<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidApplicationTransition;
use App\Http\Requests\SaveResearcherApplicationRequest;
use App\Http\Requests\SubmitResearcherApplicationRequest;
use App\Models\ResearcherApplication;
use App\Services\ResearcherApplicationWorkflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    public function certificate(Request $request): Response
    {
        $user = $request->user()->load(['researcherApplication', 'researcherProfile']);
        $application = $user->researcherApplication;

        abort_unless($application?->isApproved() && $user->hasRole('INVESTIGADOR'), 403);
        abort_unless($user->researcherProfile, 404);

        $profile = $user->researcherProfile;
        $issuedAt = $application->reviewed_at ?? $application->updated_at;
        $months = [
            1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
        ];
        $salutations = [
            'Señor' => 'Sr.', 'Señora' => 'Sra.', 'Señorita' => 'Srta.',
            'Doctor' => 'Dr.', 'Doctora' => 'Dra.',
            'Profesor' => 'Prof.', 'Profesora' => 'Prof.',
        ];
        $female = in_array($profile->salutation, ['Señora', 'Señorita', 'Doctora', 'Profesora'], true);

        $pdf = Pdf::loadView('applications.certificate', [
            'article' => $female ? 'la' : 'el',
            'salutation' => $salutations[$profile->salutation] ?? $profile->academic_title,
            'name' => $user->name,
            'role' => $female ? 'Investigadora' : 'Investigador',
            'memberNoun' => $female ? 'investigadora' : 'investigador',
            'registered' => $female ? 'registrada' : 'registrado',
            'interestedPhrase' => $female ? 'de la interesada' : 'del interesado',
            'researchLine' => $profile->research_line ?: $profile->research_area,
            'city' => 'Portoviejo',
            'day' => $issuedAt->day,
            'month' => $months[$issuedAt->month],
            'year' => $issuedAt->year,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('certificacion-rimis-'.Str::slug($user->name).'.pdf');
    }
}
