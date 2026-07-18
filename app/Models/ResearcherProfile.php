<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ResearcherProfile extends Model
{
    use HasFactory;

    public const SALUTATIONS = ['Señor', 'Señora', 'Señorita', 'Doctor', 'Doctora', 'Profesor', 'Profesora'];

    public const COUNTRIES = [
        'Ecuador', 'Argentina', 'Bolivia', 'Brasil', 'Chile', 'Colombia', 'Costa Rica',
        'Cuba', 'El Salvador', 'España', 'Estados Unidos', 'Guatemala', 'Honduras',
        'México', 'Nicaragua', 'Panamá', 'Paraguay', 'Perú', 'Puerto Rico',
        'República Dominicana', 'Uruguay', 'Venezuela', 'Otro',
    ];

    public const RESEARCH_AREAS = [
        'Ciencias agrícolas', 'Ciencias de la educación', 'Ciencias de la salud',
        'Ciencias económicas y administrativas', 'Ciencias naturales y exactas',
        'Ciencias sociales y humanidades', 'Derecho y ciencias políticas',
        'Ingeniería y tecnología', 'Medio ambiente y sostenibilidad',
        'Tecnologías de la información y comunicación', 'Otra',
    ];

    protected $fillable = [
        'country',
        'salutation',
        'academic_title',
        'profession',
        'public_slug',
        'public_bio',
        'research_area',
        'research_line',
        'orcid',
        'google_scholar_url',
        'researchgate_url',
        'linkedin_url',
        'personal_website_url',
        'profile_photo_id',
        'profile_public',
        'public_email',
        'public_phone',
        'public_institution',
        'public_country',
        'public_research_area',
        'public_research_line',
        'public_cv',
        'publications_section_enabled',
        'contributions_section_enabled',
        'institution',
        'phone',
        'cv_path',
        'cv_original_name',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'profile_public' => 'boolean',
        'public_email' => 'boolean',
        'public_phone' => 'boolean',
        'public_institution' => 'boolean',
        'public_country' => 'boolean',
        'public_research_area' => 'boolean',
        'public_research_line' => 'boolean',
        'public_cv' => 'boolean',
        'publications_section_enabled' => 'boolean',
        'contributions_section_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profilePhoto(): BelongsTo { return $this->belongsTo(MediaFile::class, 'profile_photo_id'); }
    public function isPublic(): bool { return (bool) $this->profile_public; }
    public function hasApprovedMembership(): bool { return $this->user?->researcherApplication?->isApproved() ?? false; }
    public function canAppearInDirectory(): bool { return $this->isPublic() && filled($this->public_slug) && filled($this->completed_at) && (bool) $this->user?->is_active && $this->user?->hasRole('INVESTIGADOR') && $this->hasApprovedMembership(); }
    public function publicDisplayName(): string { return trim($this->user?->name ?: 'Investigador RIMIS'); }
    public function publicInstitution(): ?string { return $this->public_institution ? $this->institution : null; }
    public function publicContactData(): array { return array_filter(['email'=>$this->public_email ? $this->user?->email : null, 'phone'=>$this->public_phone ? $this->phone : null]); }
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('profile_public', true)->whereNotNull('public_slug')->whereNotNull('completed_at')
            ->whereHas('user', fn (Builder $user) => $user->where('is_active', true)
                ->whereHas('roles', fn (Builder $role) => $role->where('name', 'INVESTIGADOR'))
                ->whereHas('researcherApplication', fn (Builder $application) => $application->where('status', ResearcherApplication::STATUS_APPROVED)));
    }

    protected static function booted(): void
    {
        static::deleting(function (ResearcherProfile $profile) {
            if ($profile->cv_path) {
                Storage::disk('local')->delete($profile->cv_path);
            }
        });
    }
}
