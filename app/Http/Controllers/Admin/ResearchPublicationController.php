<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUpdateResearchPublicationRequest;
use App\Models\MediaFile;
use App\Models\ResearchPublication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
class ResearchPublicationController extends Controller
{
 public function edit(ResearchPublication $publication):View{$this->authorize('updateEditorial',$publication);return view('admin.research-publications.edit',['publication'=>$publication->load('authors'),'mediaImages'=>MediaFile::where('file_type','image')->where('status',true)->get()]);}
 public function update(AdminUpdateResearchPublicationRequest $r,ResearchPublication $publication):RedirectResponse{$d=$r->safe()->except(['pdf','authors','keywords_text']);$d['keywords']=collect(explode(',',$r->input('keywords_text','')))->map(fn($x)=>mb_strtolower(trim($x)))->filter()->unique()->take(10)->values()->all();$d['pdf_public']=$r->boolean('pdf_public')&&$publication->pdf_distribution_authorized&&$publication->isApproved()&&$publication->hasPdf();if($r->hasFile('pdf')){if($publication->pdf_path)Storage::disk('local')->delete($publication->pdf_path);$f=$r->file('pdf');$d+=['pdf_path'=>$f->store("research-publications/{$publication->user_id}/{$publication->id}",'local'),'pdf_original_name'=>$f->getClientOriginalName(),'pdf_mime_type'=>$f->getMimeType(),'pdf_size'=>$f->getSize()];}$publication->update($d);$publication->reviewHistory()->create(['previous_status'=>'editorial:content','new_status'=>'editorial:content','comments'=>'Edición administrativa de la publicación.','changed_by'=>$r->user()->id]);return back()->with('success','Publicación actualizada editorialmente.');}
}
