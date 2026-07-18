<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use App\Notifications\ContentSubmissionStatusNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContentSubmissionWorkflowService
{
    public function submit(Model $submission, int $userId): Model
    {
        $result = DB::transaction(function () use ($submission, $userId) {
            $locked = $submission->newQuery()->lockForUpdate()->findOrFail($submission->getKey());
            if (! $locked->isEditableByResearcher()) {
                throw new ConflictHttpException('Este aporte ya no puede enviarse en su estado actual.');
            }
            $previous = $locked->review_status;
            $locked->forceFill([
                'review_status' => $locked::REVIEW_SUBMITTED,
                'submitted_at' => now(),
                'review_started_at' => null,
                'reviewed_at' => null,
                'reviewed_by' => null,
                'review_notes' => null,
                'status' => $locked::STATUS_DRAFT,
                'published_at' => null,
            ])->save();
            $locked->reviewHistory()->create(['previous_status' => $previous, 'new_status' => $locked::REVIEW_SUBMITTED, 'comments' => $previous === $locked::REVIEW_OBSERVED ? 'Reenviado por el investigador.' : 'Enviado por el investigador.', 'changed_by' => $userId]);
            return $locked->fresh('author');
        });
        try {
            $label = match (class_basename($result)) { 'Event' => 'Evento', 'Bulletin' => 'Boletín', 'ResearchPublication' => 'Investigación', default => 'Convocatoria' };
            $result->author?->notify(new ContentSubmissionStatusNotification($result, $label, 'submitted'));
        } catch (Throwable $e) {
            Log::warning('No se pudo notificar el envío editorial.', ['type' => $result::class, 'id' => $result->id, 'error' => $e->getMessage()]);
        }
        return $result;
    }
}
