<?php
namespace App\Http\Controllers\Admin;
use App\Exceptions\InvalidApplicationTransition;
use App\Http\Controllers\Controller;
use App\Models\Bulletin;
use App\Models\CallForProposal;
use App\Models\Event;
use App\Models\ResearchPublication;
use App\Services\ContentReviewWorkflowService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
class SubmissionReviewController extends Controller
{
    private const TYPES=['event'=>Event::class,'bulletin'=>Bulletin::class,'call'=>CallForProposal::class,'research_publication'=>ResearchPublication::class];
    public function __construct(private ContentReviewWorkflowService $workflow) {}
    public function index(Request $request): View
    {
        $this->authorizeEditor($request,'submissions.view');
        $types=$request->filled('type') ? [$request->string('type')->toString() => self::TYPES[$request->string('type')->toString()] ?? abort(404)] : self::TYPES;
        $items=collect();
        foreach($types as $type=>$class){
            $rows=$class::with(['author.subscription','reviewer'])->where('origin',$class::ORIGIN_RESEARCHER)
                ->where('review_status','!=',$class::REVIEW_DRAFT)
                ->when($request->filled('review_status'),fn($q)=>$q->where('review_status',$request->string('review_status')))
                ->when($request->filled('status'),fn($q)=>$q->where('status',$request->string('status')))
                ->when($request->filled('title'),fn($q)=>$q->where('title','like','%'.$request->string('title').'%'))
                ->when($request->filled('researcher'),fn($q)=>$q->whereHas('author',fn($u)=>$u->where('name','like','%'.$request->string('researcher').'%')))
                ->when($request->filled('institution'),fn($q)=>$q->whereHas('author.subscription',fn($p)=>$p->where('institution_name','like','%'.$request->string('institution').'%')))
                ->when($class===ResearchPublication::class && $request->filled('publication_type'),fn($q)=>$q->where('publication_type',$request->string('publication_type')))
                ->when($class===ResearchPublication::class && $request->filled('year'),fn($q)=>$q->where('year',$request->integer('year')))
                ->when($class===ResearchPublication::class && $request->filled('research_area'),fn($q)=>$q->where('research_area',$request->string('research_area')))
                ->when($request->filled('submitted_from'),fn($q)=>$q->whereDate('submitted_at','>=',$request->date('submitted_from')))->get();
            $items=$items->concat($rows->map(fn($m)=>$this->item($m,$type)));
        }
        $priority=['submitted'=>1,'under_review'=>2,'observed'=>3,'approved'=>4,'rejected'=>5,'draft'=>6];
        $items=$items->sortBy(fn($i)=>sprintf('%02d-%s',$priority[$i['model']->review_status]??9,9999999999-$i['model']->updated_at->timestamp))->values();
        $page=LengthAwarePaginator::resolveCurrentPage(); $perPage=15;
        $submissions=new LengthAwarePaginator($items->forPage($page,$perPage),$items->count(),$perPage,$page,['path'=>$request->url(),'query'=>$request->query()]);
        return view('admin.submissions.index',compact('submissions'));
    }
    public function show(Request $r,string $type,int $id): View { $this->authorizeEditor($r,'submissions.view'); $submission=$this->resolve($type,$id); $submission->load(['author.subscription','reviewer','reviewHistory.changedBy']); if($submission instanceof ResearchPublication)$submission->load('authors'); return view('admin.submissions.show',compact('submission','type')); }
    public function startReview(Request $r,string $type,int $id): RedirectResponse { return $this->act($r,$type,$id,'submissions.review',fn($m)=>$this->workflow->startReview($m,$r->user())); }
    public function observe(Request $r,string $type,int $id): RedirectResponse { $notes=$r->validate(['review_notes'=>['required','string','min:10','max:5000']])['review_notes']; return $this->act($r,$type,$id,'submissions.observe',fn($m)=>$this->workflow->observe($m,$r->user(),$notes)); }
    public function approve(Request $r,string $type,int $id): RedirectResponse { $notes=$r->validate(['review_notes'=>['nullable','string','max:5000']])['review_notes']??null; return $this->act($r,$type,$id,'submissions.approve',fn($m)=>$this->workflow->approve($m,$r->user(),$notes)); }
    public function reject(Request $r,string $type,int $id): RedirectResponse { $notes=$r->validate(['review_notes'=>['required','string','min:10','max:5000']])['review_notes']; return $this->act($r,$type,$id,'submissions.reject',fn($m)=>$this->workflow->reject($m,$r->user(),$notes)); }
    public function publish(Request $r,string $type,int $id): RedirectResponse { return $this->act($r,$type,$id,'submissions.publish',fn($m)=>$this->workflow->publish($m,$r->user())); }
    public function unpublish(Request $r,string $type,int $id): RedirectResponse { return $this->act($r,$type,$id,'submissions.publish',fn($m)=>$this->workflow->unpublish($m,$r->user())); }
    public function download(Request $r,string $type,int $id): StreamedResponse
    {
        $this->authorizeEditor($r,'submissions.view'); $m=$this->resolve($type,$id);
        [$path,$name]=match($type){'event'=>[$m->attachment_path,$m->attachment_original_name],'bulletin','research_publication'=>[$m->pdf_path,$m->pdf_original_name],default=>[$m->bases_pdf_path,$m->bases_pdf_original_name]};
        abort_unless($path && Storage::disk('local')->exists($path),404); return Storage::disk('local')->download($path,$name);
    }
    private function act(Request $r,string $type,int $id,string $permission,callable $action): RedirectResponse { $this->authorizeEditor($r,$permission); try{$action($this->resolve($type,$id));}catch(InvalidApplicationTransition $e){abort(409,$e->getMessage());} return back()->with('success','Acción editorial realizada correctamente.'); }
    private function resolve(string $type,int $id): Model { $class=self::TYPES[$type]??abort(404); return $class::where('origin',$class::ORIGIN_RESEARCHER)->where('review_status','!=',$class::REVIEW_DRAFT)->findOrFail($id); }
    private function authorizeEditor(Request $r,string $permission): void { abort_unless($r->user()->can($permission) && !$r->user()->isMember(),403); }
    private function item(Model $m,string $type): array { return ['type'=>$type,'label'=>['event'=>'Evento','bulletin'=>'Boletín','call'=>'Convocatoria','research_publication'=>'Investigación'][$type],'model'=>$m]; }
}
