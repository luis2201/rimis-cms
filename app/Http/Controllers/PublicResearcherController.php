<?php
namespace App\Http\Controllers;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;
class PublicResearcherController extends Controller
{
    public function index(Request $r):View
    {
        $query=Subscription::where('type',Subscription::TYPE_PROFESSIONAL)->where('status',Subscription::STATUS_APPROVED)->whereHas('user',fn($q)=>$q->where('is_active',true));
        $profiles=$query->when($r->filled('search'),fn($q)=>$q->where(fn($x)=>$x->where('first_names','like','%'.$r->search.'%')->orWhere('last_names','like','%'.$r->search.'%')->orWhere('undergraduate_title','like','%'.$r->search.'%')))->when($r->filled('country'),fn($q)=>$q->where('country',$r->country))->when($r->filled('research_area'),fn($q)=>$q->whereJsonContains('research_areas',$r->research_area))->orderBy('last_names')->paginate(12)->withQueryString();
        $filters=['countries'=>Subscription::where('type','professional')->where('status','approved')->distinct()->orderBy('country')->pluck('country'),'areas'=>collect(\App\Models\ResearcherProfile::RESEARCH_AREAS)];
        return view('researchers.index',compact('profiles','filters'));
    }
    public function show(string $slug):View{$profile=Subscription::where('type','professional')->where('status','approved')->where('public_slug',$slug)->whereHas('user',fn($q)=>$q->where('is_active',true))->with('user.researchPublications')->firstOrFail();return view('researchers.show',compact('profile'));}
}
