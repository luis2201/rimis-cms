<?php

namespace App\Services;

use App\Exceptions\InvalidApplicationTransition;
use App\Models\ResearcherApplication;
use App\Models\User;
use App\Notifications\ResearcherApplicationStatusNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ResearcherApplicationWorkflowService
{
    public function submit(ResearcherApplication $application, User $actor): ResearcherApplication
    {
        $actor->load('researcherProfile');
        $profile = $actor->researcherProfile;
        if (! $actor->hasVerifiedEmail() || ! $this->profileIsComplete($actor)) {
            throw new InvalidApplicationTransition('Completa y verifica tu perfil profesional, incluido el currículum PDF, antes de enviar la postulación.');
        }

        $snapshot = [
            'name' => $actor->name, 'email' => $actor->email, 'country' => $profile->country,
            'salutation' => $profile->salutation, 'academic_title' => $profile->academic_title,
            'profession' => $profile->profession, 'research_area' => $profile->research_area,
            'institution' => $profile->institution, 'phone' => $profile->phone,
            'cv_original_name' => $profile->cv_original_name,
            'profile_completed_at' => $profile->completed_at?->toIso8601String(),
        ];

        return $this->transition($application, $actor, [ResearcherApplication::STATUS_DRAFT, ResearcherApplication::STATUS_OBSERVED], ResearcherApplication::STATUS_SUBMITTED, [
            'submitted_at' => now(), 'review_started_at' => null, 'reviewed_at' => null,
            'reviewed_by' => null, 'review_notes' => null, 'profile_snapshot' => $snapshot,
        ], $application->isObserved() ? 'Postulación corregida y reenviada.' : 'Postulación enviada.', 'submitted');
    }

    public function startReview(ResearcherApplication $application, User $actor): ResearcherApplication
    {
        $this->ensureNotOwn($application, $actor);
        return $this->transition($application, $actor, [ResearcherApplication::STATUS_SUBMITTED], ResearcherApplication::STATUS_UNDER_REVIEW, ['review_started_at' => now(), 'reviewed_by' => $actor->id], 'Revisión iniciada.', 'under_review');
    }

    public function observe(ResearcherApplication $application, User $actor, string $notes): ResearcherApplication
    {
        $this->ensureNotOwn($application, $actor);
        return $this->transition($application, $actor, [ResearcherApplication::STATUS_UNDER_REVIEW], ResearcherApplication::STATUS_OBSERVED, ['reviewed_at' => now(), 'reviewed_by' => $actor->id, 'review_notes' => $notes], $notes, 'observed');
    }

    public function approve(ResearcherApplication $application, User $actor, ?string $notes = null): ResearcherApplication
    {
        $this->ensureNotOwn($application, $actor);
        $application->refresh();
        if ($application->isApproved()) {
            $application->user->removeRole('USUARIO');
            $application->user->assignRole('INVESTIGADOR');
            return $application->load(['user', 'reviewer', 'history']);
        }
        return $this->transition($application, $actor, [ResearcherApplication::STATUS_UNDER_REVIEW], ResearcherApplication::STATUS_APPROVED, ['reviewed_at' => now(), 'reviewed_by' => $actor->id, 'review_notes' => $notes], $notes ?: 'Postulación aprobada.', 'approved', function (ResearcherApplication $locked) {
            $locked->user->removeRole('USUARIO');
            $locked->user->assignRole('INVESTIGADOR');
        });
    }

    public function reject(ResearcherApplication $application, User $actor, string $notes): ResearcherApplication
    {
        $this->ensureNotOwn($application, $actor);
        return $this->transition($application, $actor, [ResearcherApplication::STATUS_UNDER_REVIEW], ResearcherApplication::STATUS_REJECTED, ['reviewed_at' => now(), 'reviewed_by' => $actor->id, 'review_notes' => $notes], $notes, 'rejected');
    }

    public function withdraw(ResearcherApplication $application, User $actor): ResearcherApplication
    {
        return $this->transition($application, $actor, [ResearcherApplication::STATUS_DRAFT, ResearcherApplication::STATUS_SUBMITTED, ResearcherApplication::STATUS_OBSERVED], ResearcherApplication::STATUS_WITHDRAWN, [], 'Postulación retirada por el postulante.', 'withdrawn');
    }

    private function transition(ResearcherApplication $application, User $actor, array $from, string $to, array $changes, string $comments, string $notification, ?callable $after = null): ResearcherApplication
    {
        $result = DB::transaction(function () use ($application, $actor, $from, $to, $changes, $comments, $after) {
            $locked = ResearcherApplication::with('user')->lockForUpdate()->findOrFail($application->id);
            if (! in_array($locked->status, $from, true)) {
                throw new InvalidApplicationTransition('La transición solicitada no está permitida desde el estado actual.');
            }
            $previous = $locked->status;
            $locked->update($changes + ['status' => $to]);
            $locked->history()->create(['previous_status' => $previous, 'new_status' => $to, 'comments' => $comments, 'changed_by' => $actor->id]);
            if ($after) { $after($locked); }
            return $locked->fresh(['user', 'reviewer', 'history']);
        });

        try { $result->user->notify(new ResearcherApplicationStatusNotification($result, $notification)); }
        catch (Throwable $exception) { Log::warning('No se pudo enviar la notificación de postulación.', ['application_id' => $result->id, 'error' => $exception->getMessage()]); }
        return $result;
    }

    private function ensureNotOwn(ResearcherApplication $application, User $actor): void
    {
        if ((int) $application->user_id === (int) $actor->id) { throw new InvalidApplicationTransition('No puedes revisar tu propia postulación.'); }
    }

    private function profileIsComplete(User $user): bool
    {
        $profile = $user->researcherProfile;
        $fields = ['country', 'salutation', 'academic_title', 'profession', 'research_area', 'institution', 'phone', 'cv_path', 'completed_at'];
        return $profile && collect($fields)->every(fn ($field) => filled($profile->{$field})) && Storage::disk('local')->exists($profile->cv_path);
    }
}
