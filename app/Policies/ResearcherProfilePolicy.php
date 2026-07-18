<?php

namespace App\Policies;

use App\Models\ResearcherProfile;
use App\Models\User;

class ResearcherProfilePolicy
{
    public function viewAny(User $user): bool { return $user->can('researchers.view') && ! $user->hasRole('INVESTIGADOR'); }
    public function view(User $user, ResearcherProfile $profile): bool { return $user->is($profile->user) || $this->viewAny($user); }
    public function updateOwn(User $user, ResearcherProfile $profile): bool { return $user->is($profile->user) && $user->can('profile.edit'); }
    public function manageOwnPrivacy(User $user, ResearcherProfile $profile): bool { return $this->updateOwn($user, $profile) && $user->can('researcher-profile.manage-privacy'); }
    public function update(User $user, ResearcherProfile $profile): bool { return $this->viewAny($user) && $user->can('researchers.edit'); }
    public function manageVisibility(User $user, ResearcherProfile $profile): bool { return $this->viewAny($user) && $user->can('researchers.manage-visibility'); }
}
