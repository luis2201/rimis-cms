<?php

namespace App\Http\Controllers;

use App\Models\ResearcherProfile;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicResearcherController extends Controller
{
    public function index(Request $request): View
    {
        $approved = fn () => Subscription::query()
            ->where('type', Subscription::TYPE_PROFESSIONAL)
            ->where('status', Subscription::STATUS_APPROVED)
            ->whereHas('user', fn ($query) => $query->where('is_active', true));

        $profiles = $approved()
            ->when($request->filled('search'), fn ($query) => $query->where(function ($nested) use ($request) {
                $search = '%'.$request->string('search').'%';
                $nested->where('first_names', 'like', $search)
                    ->orWhere('last_names', 'like', $search)
                    ->orWhere('undergraduate_title', 'like', $search);
            }))
            ->when($request->filled('institution'), fn ($query) => $query->where('affiliated_institution', $request->string('institution')->toString()))
            ->when($request->filled('country'), fn ($query) => $query->where('country', $request->string('country')->toString()))
            ->when($request->filled('research_area'), fn ($query) => $query->whereJsonContains('research_areas', $request->string('research_area')->toString()))
            ->orderBy('last_names')
            ->paginate(12)
            ->withQueryString();

        $filters = [
            'institutions' => $approved()->whereNotNull('affiliated_institution')->where('affiliated_institution', '!=', '')->distinct()->orderBy('affiliated_institution')->pluck('affiliated_institution'),
            'countries' => $approved()->distinct()->orderBy('country')->pluck('country'),
            'areas' => collect(ResearcherProfile::RESEARCH_AREAS),
        ];

        return view('researchers.index', compact('profiles', 'filters'));
    }

    public function show(string $slug): View
    {
        $profile = Subscription::where('type', Subscription::TYPE_PROFESSIONAL)
            ->where('status', Subscription::STATUS_APPROVED)
            ->where('public_slug', $slug)
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->with('user.researchPublications')
            ->firstOrFail();

        return view('researchers.show', compact('profile'));
    }
}
