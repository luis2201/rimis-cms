<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidApplicationTransition;
use App\Http\Requests\SaveResearcherApplicationRequest;
use App\Http\Requests\SubmitResearcherApplicationRequest;
use App\Models\ResearcherApplication;
use App\Services\ResearcherApplicationWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResearcherApplicationController extends Controller
{
    public function __construct(private ResearcherApplicationWorkflowService $workflow) {}

    public function show(Request $request): View|RedirectResponse
    {
        $application = $request->user()->researcherApplication;
        if (! $application) { return redirect()->route('applications.create'); }
        $this->authorize('view', $application);
        $application->load(['history.changedBy', 'reviewer']);
        return view('applications.show', compact('application'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->researcherApplication) { return redirect()->route('applications.show'); }
        return view('applications.create');
    }

    public function store(SaveResearcherApplicationRequest $request): RedirectResponse
    {
        abort_if($request->user()->researcherApplication()->exists(), 409, 'Ya existe una postulación para este usuario.');
        $application = $request->user()->researcherApplication()->create($request->validated() + ['status' => ResearcherApplication::STATUS_DRAFT]);
        $application->history()->create(['new_status' => ResearcherApplication::STATUS_DRAFT, 'comments' => 'Postulación creada.', 'changed_by' => $request->user()->id]);
        return redirect()->route('applications.show')->with('success', 'Postulación guardada como borrador.');
    }

    public function edit(Request $request): View
    {
        $application = $request->user()->researcherApplication()->firstOrFail();
        $this->authorize('update', $application);
        return view('applications.edit', compact('application'));
    }

    public function update(SaveResearcherApplicationRequest $request): RedirectResponse
    {
        $application = $request->user()->researcherApplication()->firstOrFail();
        $this->authorize('update', $application);
        $application->update($request->validated());
        return redirect()->route('applications.show')->with('success', 'Postulación actualizada.');
    }

    public function submit(SubmitResearcherApplicationRequest $request): RedirectResponse
    {
        $application = $request->user()->researcherApplication()->firstOrFail();
        $this->authorize('submit', $application);
        try { $this->workflow->submit($application, $request->user()); }
        catch (InvalidApplicationTransition $exception) { return redirect()->route('profile.edit')->with('warning', $exception->getMessage()); }
        return redirect()->route('applications.show')->with('success', 'Postulación enviada para revisión.');
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $application = $request->user()->researcherApplication()->firstOrFail();
        $this->authorize('withdraw', $application);
        $this->workflow->withdraw($application, $request->user());
        return redirect()->route('applications.show')->with('success', 'Postulación retirada.');
    }
}
