<?php

namespace App\Models;

use App\Notifications\VerifyResearcherEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
        'is_active',
        'deactivated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
        'must_change_password' => 'boolean',
    ];

    public function researcherProfile(): HasOne
    {
        return $this->hasOne(ResearcherProfile::class);
    }

    public function researcherApplication(): HasOne
    {
        return $this->hasOne(ResearcherApplication::class);
    }

    public function reviewedResearcherApplications(): HasMany
    {
        return $this->hasMany(ResearcherApplication::class, 'reviewed_by');
    }

    public function researcherApplicationHistoryChanges(): HasMany
    {
        return $this->hasMany(ResearcherApplicationHistory::class, 'changed_by');
    }

    public function subscription(): HasOne { return $this->hasOne(Subscription::class); }
    public function isMember(): bool { return $this->hasAnyRole(['INVESTIGADOR', 'INSTITUCIONAL']); }
    public function membershipProfile(): ?Subscription { return $this->subscription; }
    public function researchPublications(): HasMany { return $this->hasMany(ResearchPublication::class); }
    public function researchPublicationAuthorships(): HasMany { return $this->hasMany(ResearchPublicationAuthor::class); }
    public function reviewedResearchPublications(): HasMany { return $this->hasMany(ResearchPublication::class, 'reviewed_by'); }

    public function hasCompleteResearcherProfile(): bool
    {
        return $this->researcherProfile()->whereNotNull('completed_at')->exists();
    }

    public function deactivate(): void
    {
        $this->forceFill([
            'is_active' => false,
            'deactivated_at' => now(),
            'remember_token' => null,
        ])->save();
    }

    public function activate(): void
    {
        $this->forceFill([
            'is_active' => true,
            'deactivated_at' => null,
        ])->save();
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyResearcherEmail());
    }
}
