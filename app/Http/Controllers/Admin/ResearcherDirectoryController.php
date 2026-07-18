<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ResearcherProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
class ResearcherDirectoryController extends Controller
{
 public function index(Request $r):View{$this->authorize('viewAny',ResearcherProfile::class);$profiles=ResearcherProfile::with(['user.researcherApplication'])->whereHas('user',fn($u)=>$u->role('INVESTIGADOR'))->when($r->filled('search'),fn($q)=>$q->whereHas('user',fn($u)=>$u->where('name','like','%'.$r->string('search').'%')->orWhere('email','like','%'.$r->string('search').'%')))->when($r->filled('profile_public'),fn($q)=>$q->where('profile_public',$r->boolean('profile_public')))->latest()->paginate(20)->withQueryString();return view('admin.researchers.index',compact('profiles'));}
 public function edit(ResearcherProfile $profile):View{$this->authorize('update',$profile);return view('admin.researchers.edit',compact('profile'));}
 public function update(Request $r,ResearcherProfile $profile):RedirectResponse{$this->authorize('update',$profile);$d=$r->validate(['public_bio'=>['nullable','string','max:3000'],'research_line'=>['nullable','string','max:255'],'orcid'=>['nullable','regex:/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/'],'public_email'=>['nullable','boolean'],'public_phone'=>['nullable','boolean'],'public_institution'=>['nullable','boolean'],'public_country'=>['nullable','boolean'],'public_research_area'=>['nullable','boolean'],'public_research_line'=>['nullable','boolean'],'public_cv'=>['nullable','boolean'],'publications_section_enabled'=>['nullable','boolean'],'contributions_section_enabled'=>['nullable','boolean']]);$d['public_bio']=strip_tags($d['public_bio']??'');foreach(['public_email','public_phone','public_institution','public_country','public_research_area','public_research_line','public_cv','publications_section_enabled','contributions_section_enabled'] as $f)$d[$f]=$r->boolean($f);$profile->update($d);Log::info('Datos públicos de investigador editados',['profile_id'=>$profile->id,'actor_id'=>$r->user()->id]);return back()->with('success','Perfil público actualizado.');}
 public function visibility(Request $r,ResearcherProfile $profile):RedirectResponse{$this->authorize('manageVisibility',$profile);$visible=$r->boolean('visible');if($visible)abort_unless($profile->fresh('user.researcherApplication')->canAppearInDirectory()||(!$profile->profile_public&&$profile->user->is_active&&$profile->user->hasRole('INVESTIGADOR')&&$profile->completed_at&&$profile->hasApprovedMembership()),422);$profile->update(['profile_public'=>$visible]);Log::info($visible?'Perfil público habilitado':'Perfil público ocultado',['profile_id'=>$profile->id,'actor_id'=>$r->user()->id,'reason'=>$r->input('reason')]);return back()->with('success',$visible?'Perfil habilitado.':'Perfil ocultado.');}
}
