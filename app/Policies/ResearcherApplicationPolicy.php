<?php

namespace App\Policies;

use App\Models\ResearcherApplication;
use App\Models\User;

class ResearcherApplicationPolicy
{
    public function viewAny(User $user): bool { return $user->can('applications.view'); }
    public function view(User $user, ResearcherApplication $application): bool { return $user->can('applications.view') || ($user->can('applications.view-own') && $user->is($application->user)); }
    public function update(User $user, ResearcherApplication $application): bool { return $user->can('applications.edit-own') && $user->is($application->user) && $application->isEditableByApplicant(); }
    public function submit(User $user, ResearcherApplication $application): bool { return $user->can('applications.submit') && $user->is($application->user) && $application->isEditableByApplicant(); }
    public function withdraw(User $user, ResearcherApplication $application): bool { return $user->can('applications.withdraw') && $user->is($application->user) && in_array($application->status, [ResearcherApplication::STATUS_DRAFT, ResearcherApplication::STATUS_SUBMITTED, ResearcherApplication::STATUS_OBSERVED], true); }
    public function review(User $user, ResearcherApplication $application): bool { return $user->can('applications.review') && ! $user->is($application->user); }
    public function observe(User $user, ResearcherApplication $application): bool { return $user->can('applications.observe') && ! $user->is($application->user); }
    public function approve(User $user, ResearcherApplication $application): bool { return $user->can('applications.approve') && ! $user->is($application->user); }
    public function reject(User $user, ResearcherApplication $application): bool { return $user->can('applications.reject') && ! $user->is($application->user); }
}
