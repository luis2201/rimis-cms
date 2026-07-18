<?php
namespace App\Policies\Concerns;
use App\Models\User;
trait AuthorizesContentSubmission
{
    public function viewOwnSubmission(User $user, object $model): bool { return $this->owns($user, $model); }
    public function createSubmission(User $user): bool { return $user->hasRole('INVESTIGADOR') && $user->can('submissions.view-own'); }
    public function updateSubmission(User $user, object $model): bool { return $this->owns($user, $model) && $model->isEditableByResearcher(); }
    public function deleteSubmission(User $user, object $model): bool { return $this->updateSubmission($user, $model) && $model->isReviewDraft(); }
    public function submitSubmission(User $user, object $model): bool { return $this->updateSubmission($user, $model); }
    public function downloadSubmissionFile(User $user, object $model): bool { return $this->owns($user, $model); }
    public function viewSubmission(User $user, object $model): bool { return ! $user->hasRole('INVESTIGADOR') && $user->can('submissions.view') && $model->isResearcherSubmission() && ! $model->isReviewDraft(); }
    public function reviewSubmission(User $user, object $model): bool { return $this->viewSubmission($user,$model) && $user->can('submissions.review'); }
    public function publishSubmission(User $user, object $model): bool { return $this->viewSubmission($user,$model) && $user->can('submissions.publish'); }
    private function owns(User $user, object $model): bool { return $user->hasRole('INVESTIGADOR') && $model->isResearcherSubmission() && (int) $model->user_id === (int) $user->id; }
}
