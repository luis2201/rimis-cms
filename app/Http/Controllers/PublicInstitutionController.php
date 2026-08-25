<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicInstitutionController extends Controller
{
    public function index(Request $request): View
    {
        $approved = fn () => Subscription::query()
            ->where('type', Subscription::TYPE_INSTITUTIONAL)
            ->where('status', Subscription::STATUS_APPROVED)
            ->whereHas('user', fn ($query) => $query->where('is_active', true));

        $institutions = $approved()
            ->when($request->filled('search'), fn ($query) => $query->where('institution_name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('country'), fn ($query) => $query->where('country', $request->string('country')->toString()))
            ->when($request->filled('institution_type'), fn ($query) => $query->where('institution_type', $request->string('institution_type')->toString()))
            ->orderBy('institution_name')
            ->paginate(12)
            ->withQueryString();

        $filters = [
            'countries' => $approved()->distinct()->orderBy('country')->pluck('country'),
            'types' => $approved()->distinct()->orderBy('institution_type')->pluck('institution_type'),
        ];

        return view('institutions.index', compact('institutions', 'filters'));
    }

    public function show(string $slug): View
    {
        $institution = Subscription::where('type', Subscription::TYPE_INSTITUTIONAL)
            ->where('status', Subscription::STATUS_APPROVED)
            ->where('public_slug', $slug)
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->firstOrFail();

        return view('institutions.show', compact('institution'));
    }
}
