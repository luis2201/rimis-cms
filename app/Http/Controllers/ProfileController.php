<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\ResearcherProfileUpdateRequest;
use App\Models\ResearcherProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('researcherProfile'),
            'salutations' => ResearcherProfile::SALUTATIONS,
            'countries' => ResearcherProfile::COUNTRIES,
            'researchAreas' => ResearcherProfile::RESEARCH_AREAS,
        ]);
    }

    public function updateResearcher(ResearcherProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $profile = $request->user()->researcherProfile;

        if ($request->hasFile('cv')) {
            if ($profile?->cv_path) {
                Storage::disk('local')->delete($profile->cv_path);
            }

            $validated['cv_original_name'] = $request->file('cv')->getClientOriginalName();
            $validated['cv_path'] = $request->file('cv')->store('curricula', 'local');
        }

        unset($validated['cv']);
        $validated['completed_at'] = now();

        $request->user()->researcherProfile()->updateOrCreate([], $validated);

        return Redirect::route('profile.edit')->with('success', 'Información profesional actualizada correctamente.');
    }

    public function downloadCv(Request $request, User $user): StreamedResponse
    {
        abort_unless($request->user()->is($user) || $request->user()->can('users.view'), 403);

        $profile = $user->researcherProfile;
        abort_unless($profile?->cv_path && Storage::disk('local')->exists($profile->cv_path), 404);

        return Storage::disk('local')->download($profile->cv_path, $profile->cv_original_name);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());
        $emailChanged = $request->user()->isDirty('email');

        if ($emailChanged) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($emailChanged && $request->user()->hasAnyRole(['USUARIO', 'INVESTIGADOR'])) {
            $request->user()->sendEmailVerificationNotification();

            return Redirect::route('verification.notice')->with('status', 'verification-link-sent');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        abort_if($user->hasRole('ADMINISTRADOR'), 403);

        $user->deactivate();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Tu cuenta fue desactivada correctamente.');
    }
}
