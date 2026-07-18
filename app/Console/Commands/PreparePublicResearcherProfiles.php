<?php

namespace App\Console\Commands;

use App\Models\ResearcherApplication;
use App\Models\ResearcherProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PreparePublicResearcherProfiles extends Command
{
    protected $signature = 'rimis:prepare-public-researcher-profiles';
    protected $description = 'Prepara de forma segura los perfiles públicos de investigadores aprobados';

    public function handle(): int
    {
        $updated = 0; $skipped = 0;
        User::role('INVESTIGADOR')->with(['researcherProfile','researcherApplication'])->chunkById(100, function ($users) use (&$updated, &$skipped) {
            foreach ($users as $user) {
                $profile = $user->researcherProfile;
                if (! $profile || ! $user->is_active || ! $profile->completed_at || $user->researcherApplication?->status !== ResearcherApplication::STATUS_APPROVED) { $skipped++; continue; }
                $changes = [];
                if (blank($profile->public_slug)) {
                    $changes['public_slug'] = $this->uniqueSlug($user->name, $profile);
                    $changes['profile_public'] = true;
                }
                if ($changes) { $profile->update($changes); $updated++; } else $skipped++;
            }
        });
        $this->info("Perfiles actualizados: {$updated}. Omitidos: {$skipped}.");
        return self::SUCCESS;
    }

    private function uniqueSlug(string $name, ResearcherProfile $profile): string
    {
        $base = Str::slug($name) ?: 'investigador'; $slug = $base; $suffix = 2;
        while (ResearcherProfile::where('public_slug', $slug)->whereKeyNot($profile->id)->exists()) $slug = $base.'-'.$suffix++;
        return $slug;
    }
}
