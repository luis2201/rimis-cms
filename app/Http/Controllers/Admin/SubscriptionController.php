<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSubscriptionRequest;
use App\Models\ResearcherProfile;
use App\Models\Subscription;
use App\Services\SubscriptionWorkflowService;
use Illuminate\Http\{RedirectResponse,Request};
use Illuminate\Support\Facades\{DB,Storage};
use Illuminate\View\View;
class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionWorkflowService $workflow){}
    public function index(Request $r):View{$this->authorizeAccess($r,'subscriptions.view');$subscriptions=Subscription::with('reviewer')->when($r->filled('status'),fn($q)=>$q->where('status',$r->status))->when($r->filled('type'),fn($q)=>$q->where('type',$r->type))->when($r->filled('search'),fn($q)=>$q->where(fn($x)=>$x->where('email','like','%'.$r->search.'%')->orWhere('first_names','like','%'.$r->search.'%')->orWhere('last_names','like','%'.$r->search.'%')->orWhere('institution_name','like','%'.$r->search.'%')))->orderByRaw("CASE status WHEN 'submitted' THEN 1 WHEN 'under_review' THEN 2 ELSE 3 END")->latest()->paginate(15)->withQueryString();return view('admin.subscriptions.index',compact('subscriptions'));}
    public function show(Request $r,Subscription $subscription):View{$this->authorizeAccess($r,'subscriptions.view');$subscription->load(['reviewer','history.changedBy','user']);return view('admin.subscriptions.show',compact('subscription'));}
    public function edit(Request $r,Subscription $subscription):View{$this->authorizeAccess($r,'subscriptions.edit');return view('admin.subscriptions.edit',['subscription'=>$subscription,'researchAreas'=>ResearcherProfile::RESEARCH_AREAS,'institutionTypes'=>Subscription::INSTITUTION_TYPES]);}
    public function update(UpdateSubscriptionRequest $r,Subscription $subscription):RedirectResponse
    {
        $data=$r->safe()->except(['personal_photo','institution_logo']);
        $fileField=$subscription->isProfessional()?'personal_photo':'institution_logo';
        $pathField=$subscription->isProfessional()?'personal_photo_path':'institution_logo_path';
        $directory=$subscription->isProfessional()?'subscriptions/professional-photos':'subscriptions/institution-logos';
        $oldPath=$subscription->{$pathField};
        $newPath=$r->hasFile($fileField)?$r->file($fileField)->store($directory,'public'):null;
        if($newPath){$data[$pathField]=$newPath;}
        try{
            DB::transaction(function()use($subscription,$data,$r){
                $subscription->update($data);
                if($subscription->user){$subscription->user->update(['name'=>$subscription->displayName(),'email'=>$subscription->email]);}
                $subscription->history()->create(['previous_status'=>$subscription->status,'new_status'=>$subscription->status,'comments'=>'Datos de la suscripción actualizados por administración.','changed_by'=>$r->user()->id]);
            });
        }catch(\Throwable $e){if($newPath){Storage::disk('public')->delete($newPath);}throw $e;}
        if($newPath&&$oldPath&&$oldPath!==$newPath){Storage::disk('public')->delete($oldPath);}
        return redirect()->route('admin.subscriptions.show',$subscription)->with('success','Datos de la suscripción actualizados.');
    }
    public function startReview(Request $r,Subscription $subscription):RedirectResponse{$this->authorizeAccess($r,'subscriptions.review');$this->workflow->startReview($subscription,$r->user());return back()->with('success','Revisión iniciada.');}
    public function approve(Request $r,Subscription $subscription):RedirectResponse{$this->authorizeAccess($r,'subscriptions.approve');$data=$r->validate(['review_notes'=>['nullable','string','max:5000']]);$this->workflow->approve($subscription,$r->user(),$data['review_notes']??null);return back()->with('success','Suscripción aprobada y credenciales enviadas.');}
    public function reject(Request $r,Subscription $subscription):RedirectResponse{$this->authorizeAccess($r,'subscriptions.reject');$data=$r->validate(['review_notes'=>['required','string','max:5000']]);$this->workflow->reject($subscription,$r->user(),$data['review_notes']);return back()->with('success','Suscripción rechazada.');}
    private function authorizeAccess(Request $r,string $permission):void{abort_unless($r->user()->can($permission)&&!$r->user()->isMember(),403);}
}
