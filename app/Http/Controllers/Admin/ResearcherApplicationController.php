<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidApplicationTransition;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveResearcherApplicationRequest;
use App\Http\Requests\ReviewResearcherApplicationRequest;
use App\Models\ResearcherApplication;
use App\Services\ResearcherApplicationWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResearcherApplicationController extends Controller
{
    public function __construct(private ResearcherApplicationWorkflowService $workflow) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ResearcherApplication::class);
        $applications = ResearcherApplication::with(['user.researcherProfile', 'reviewer'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%'.$request->string('search').'%')->orWhere('email', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('institution'), fn ($q) => $q->whereHas('user.researcherProfile', fn ($p) => $p->where('institution', 'like', '%'.$request->string('institution').'%')))
            ->when($request->filled('research_area'), fn ($q) => $q->whereHas('user.researcherProfile', fn ($p) => $p->where('research_area', $request->string('research_area'))))
            ->when($request->filled('submitted_from'), fn ($q) => $q->whereDate('submitted_at', '>=', $request->date('submitted_from')))
            ->when($request->filled('submitted_to'), fn ($q) => $q->whereDate('submitted_at', '<=', $request->date('submitted_to')))
            ->orderByRaw("CASE status WHEN 'submitted' THEN 1 WHEN 'under_review' THEN 2 WHEN 'observed' THEN 3 ELSE 4 END")
            ->latest('updated_at')->paginate(15)->withQueryString();
        return view('admin.researcher-applications.index', compact('applications'));
    }

    public function show(ResearcherApplication $application): View
    {
        $this->authorize('view', $application);
        $application->load(['user.researcherProfile', 'reviewer', 'history.changedBy']);
        return view('admin.researcher-applications.show', compact('application'));
    }

    public function startReview(Request $request, ResearcherApplication $application): RedirectResponse { $this->authorize('review', $application); return $this->run(fn () => $this->workflow->startReview($application, $request->user()), 'Revisión iniciada.'); }
    public function observe(ReviewResearcherApplicationRequest $request, ResearcherApplication $application): RedirectResponse { $this->authorize('observe', $application); return $this->run(fn () => $this->workflow->observe($application, $request->user(), $request->validated('review_notes')), 'Postulación observada.'); }
    public function approve(ApproveResearcherApplicationRequest $request, ResearcherApplication $application): RedirectResponse { $this->authorize('approve', $application); return $this->run(fn () => $this->workflow->approve($application, $request->user(), $request->validated('review_notes')), 'Postulación aprobada.'); }
    public function reject(ReviewResearcherApplicationRequest $request, ResearcherApplication $application): RedirectResponse { $this->authorize('reject', $application); return $this->run(fn () => $this->workflow->reject($application, $request->user(), $request->validated('review_notes')), 'Postulación rechazada.'); }

    public function downloadCv(ResearcherApplication $application): StreamedResponse
    {
        $this->authorize('view', $application);
        $profile = $application->user->researcherProfile;
        abort_unless($profile?->cv_path && Storage::disk('local')->exists($profile->cv_path), 404);
        return Storage::disk('local')->download($profile->cv_path, $profile->cv_original_name);
    }

    private function run(callable $action, string $message): RedirectResponse
    {
        try { $action(); } catch (InvalidApplicationTransition $exception) { abort(409, $exception->getMessage()); }
        return back()->with('success', $message);
    }
}
