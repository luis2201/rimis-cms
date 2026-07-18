<?php

namespace App\Http\Controllers;

use App\Models\Bulletin;
use App\Models\CallForProposal;
use App\Models\Event;
use App\Models\ResearcherProfile;
use App\Models\ResearchPublication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicResearcherController extends Controller
{
    public function index(Request $request): View
    {
        $query = ResearcherProfile::publiclyVisible()->with([
            'user.researchPublications' => fn ($q) => $q->publiclyVisible()->select('research_publications.id','research_publications.user_id'),
            'user.researchPublicationAuthorships.publication' => fn ($q) => $q->publiclyVisible()->select('research_publications.id','research_publications.user_id'),
            'profilePhoto',
        ])
            ->when($request->filled('search'), function (Builder $q) use ($request) { $term='%'.$request->string('search').'%'; $q->where(fn(Builder $s)=>$s->where('institution','like',$term)->orWhere('research_area','like',$term)->orWhere('research_line','like',$term)->orWhere('country','like',$term)->orWhere('public_bio','like',$term)->orWhere('orcid','like',$term)->orWhereHas('user',fn(Builder $u)=>$u->where('name','like',$term))); })
            ->when($request->filled('country'),fn(Builder $q)=>$q->where('country',$request->string('country')))
            ->when($request->filled('institution'),fn(Builder $q)=>$q->where('institution',$request->string('institution')))
            ->when($request->filled('research_area'),fn(Builder $q)=>$q->where('research_area',$request->string('research_area')))
            ->when($request->filled('research_line'),fn(Builder $q)=>$q->where('research_line',$request->string('research_line')))
            ->when($request->boolean('with_publications'),fn(Builder $q)=>$q->whereHas('user',fn(Builder $u)=>$u->whereHas('researchPublications',fn(Builder $p)=>$p->publiclyVisible())->orWhereHas('researchPublicationAuthorships.publication',fn(Builder $p)=>$p->publiclyVisible())))
            ->when($request->input('order')==='recent',fn(Builder $q)=>$q->latest('completed_at'))
            ->when($request->input('order')==='name_desc',fn(Builder $q)=>$q->whereHas('user')->orderByDesc($this->userNameSubquery()))
            ->when(!in_array($request->input('order'),['recent','name_desc'],true),fn(Builder $q)=>$q->orderBy($this->userNameSubquery()));
        $profiles=$query->paginate(12)->withQueryString();
        foreach($profiles as $profile) $profile->public_publications_count=$profile->user->researchPublications->pluck('id')->merge($profile->user->researchPublicationAuthorships->pluck('publication.id'))->filter()->unique()->count();
        $filters=['countries'=>ResearcherProfile::publiclyVisible()->distinct()->orderBy('country')->pluck('country'),'institutions'=>ResearcherProfile::publiclyVisible()->distinct()->orderBy('institution')->pluck('institution'),'areas'=>ResearcherProfile::publiclyVisible()->distinct()->orderBy('research_area')->pluck('research_area'),'lines'=>ResearcherProfile::publiclyVisible()->whereNotNull('research_line')->distinct()->orderBy('research_line')->pluck('research_line')];
        return view('researchers.index',compact('profiles','filters'));
    }

    public function show(string $slug): View
    {
        $profile=ResearcherProfile::publiclyVisible()->with(['user','profilePhoto'])->where('public_slug',$slug)->firstOrFail();
        $publications=$profile->publications_section_enabled ? $this->publicationsFor($profile)->with('authors')->latest('published_at')->get() : collect();
        $contributions=$profile->contributions_section_enabled ? ['events'=>$this->contributions(Event::class,$profile),'bulletins'=>$this->contributions(Bulletin::class,$profile),'calls'=>$this->contributions(CallForProposal::class,$profile)] : [];
        return view('researchers.show',compact('profile','publications','contributions'));
    }

    public function cv(string $slug): StreamedResponse
    {
        $profile=ResearcherProfile::publiclyVisible()->where('public_slug',$slug)->firstOrFail();
        abort_unless($profile->public_cv && $profile->cv_path && Storage::disk('local')->exists($profile->cv_path),404);
        return Storage::disk('local')->download($profile->cv_path,$profile->cv_original_name);
    }

    private function publicationsFor(ResearcherProfile $profile): Builder { return ResearchPublication::publiclyVisible()->where(fn(Builder $q)=>$q->where('user_id',$profile->user_id)->orWhereHas('authors',fn(Builder $a)=>$a->where('user_id',$profile->user_id)))->distinct(); }
    private function contributions(string $model,ResearcherProfile $profile){return $model::published()->where('user_id',$profile->user_id)->where('origin',$model::ORIGIN_RESEARCHER)->where('review_status',$model::REVIEW_APPROVED)->latest('published_at')->limit(6)->get();}
    private function userNameSubquery(){return \App\Models\User::select('name')->whereColumn('users.id','researcher_profiles.user_id')->limit(1);}
}
