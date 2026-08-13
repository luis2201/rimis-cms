<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreResearchPublicationDraftRequest;
use App\Http\Requests\SubmitResearchPublicationRequest;
use App\Http\Requests\UpdateResearchPublicationDraftRequest;
use App\Models\ResearchPublication;
use App\Services\ContentSubmissionWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
class ResearchPublicationController extends Controller
{
 public function index(Request $r):View{$this->authorize('viewAny',ResearchPublication::class);$publications=ResearchPublication::ownedBy($r->user())->when($r->filled('review_status'),fn($q)=>$q->where('review_status',$r->string('review_status')))->when($r->filled('status'),fn($q)=>$q->where('status',$r->string('status')))->latest()->paginate(15)->withQueryString();$counts=ResearchPublication::ownedBy($r->user())->selectRaw('review_status,status,count(*) total')->groupBy('review_status','status')->get();return view('researcher.publications.index',compact('publications','counts'));}
 public function create():View{$this->authorize('create',ResearchPublication::class);return view('researcher.publications.form',['publication'=>new ResearchPublication]);}
 public function store(StoreResearchPublicationDraftRequest $r):RedirectResponse{return DB::transaction(function()use($r){$data=$this->data($r);$data+=['user_id'=>$r->user()->id,'slug'=>$this->uniqueSlug($data['title']),'origin'=>'researcher','review_status'=>'draft','status'=>'draft'];$p=ResearchPublication::create($data);$this->savePdf($r,$p);$this->saveAuthors($p,$r->input('authors'),$r);$p->reviewHistory()->create(['new_status'=>'draft','comments'=>'Publicación creada.','changed_by'=>$r->user()->id]);return redirect()->route('researcher.publications.edit',$p)->with('success','Publicación guardada como borrador.');});}
 public function show(ResearchPublication $publication):View{$this->authorize('view',$publication);return view('researcher.publications.show',['publication'=>$publication->load(['authors','reviewer','reviewHistory.changedBy'])]);}
 public function edit(ResearchPublication $publication):View{$this->authorize('update',$publication);return view('researcher.publications.form',['publication'=>$publication->load('authors')]);}
 public function update(UpdateResearchPublicationDraftRequest $r,ResearchPublication $publication):RedirectResponse{DB::transaction(function()use($r,$publication){$publication->update($this->data($r));$this->savePdf($r,$publication);$this->saveAuthors($publication,$r->input('authors'),$r);});return back()->with('success','Borrador actualizado.');}
 public function destroy(ResearchPublication $publication):RedirectResponse{$this->authorize('delete',$publication);$publication->delete();return redirect()->route('researcher.publications.index')->with('success','Borrador eliminado.');}
 public function submit(SubmitResearchPublicationRequest $r,ResearchPublication $publication,ContentSubmissionWorkflowService $workflow):RedirectResponse{$workflow->submit($publication,$r->user()->id);return redirect()->route('researcher.publications.show',$publication)->with('success','Publicación enviada a revisión.');}
 public function pdf(Request $r,ResearchPublication $publication):StreamedResponse{$this->authorize('downloadPrivatePdf',$publication);abort_unless($publication->pdf_path&&Storage::disk('local')->exists($publication->pdf_path),404);return Storage::disk('local')->download($publication->pdf_path,$publication->pdf_original_name);}
 private function data(Request $r):array{$d=$r->safe()->except(['pdf','authors','keywords_text']);$d['abstract']=isset($d['abstract'])?strip_tags($d['abstract']):null;$d['year']=($d['publication_date']??null)?date('Y',strtotime($d['publication_date'])):($r->integer('year')?:null);$d['keywords']=collect(explode(',',$r->input('keywords_text','')))->map(fn($x)=>mb_strtolower(trim($x)))->filter()->unique()->take(10)->values()->all();$d['pdf_distribution_authorized']=$r->boolean('pdf_distribution_authorized');unset($d['pdf_public']);return $d;}
 private function saveAuthors(ResearchPublication $p,?array $authors,Request $r):void{$authors=collect($authors?:[['author_name'=>$r->user()->name,'institution'=>$r->user()->subscription?->publicationInstitution(),'is_corresponding'=>true]])->filter(fn($a)=>filled($a['author_name']??null))->values();if($authors->isEmpty())return;$p->authors()->delete();$correspondingUsed=false;foreach($authors as $i=>$a){$isCorresponding=!$correspondingUsed&&!empty($a['is_corresponding']);$correspondingUsed=$correspondingUsed||$isCorresponding;$p->authors()->create(['user_id'=>$i===0?$r->user()->id:null,'author_name'=>$a['author_name'],'institution'=>$a['institution']??null,'orcid'=>$a['orcid']??null,'email'=>$a['email']??null,'author_order'=>$i+1,'is_corresponding'=>$isCorresponding]);}}
 private function savePdf(Request $r,ResearchPublication $p):void{if(!$r->hasFile('pdf'))return;if($p->pdf_path)Storage::disk('local')->delete($p->pdf_path);$f=$r->file('pdf');$p->update(['pdf_path'=>$f->store("research-publications/{$p->user_id}/{$p->id}",'local'),'pdf_original_name'=>$f->getClientOriginalName(),'pdf_mime_type'=>$f->getMimeType(),'pdf_size'=>$f->getSize()]);}
 private function uniqueSlug(string $title):string{$base=Str::slug($title)?:'publicacion';$slug=$base;$i=2;while(ResearchPublication::where('slug',$slug)->exists())$slug=$base.'-'.$i++;return $slug;}
}
