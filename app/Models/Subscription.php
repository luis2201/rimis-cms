<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    public const TYPE_PROFESSIONAL = 'professional';
    public const TYPE_INSTITUTIONAL = 'institutional';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUS_LABELS = [
        self::STATUS_SUBMITTED => 'Enviada', self::STATUS_UNDER_REVIEW => 'En revisión',
        self::STATUS_APPROVED => 'Aprobada', self::STATUS_REJECTED => 'Rechazada',
    ];
    public const TYPE_LABELS = [self::TYPE_PROFESSIONAL => 'Profesional', self::TYPE_INSTITUTIONAL => 'Institucional'];
    public const INSTITUTION_TYPES = ['Pública', 'Privada', 'ONG', 'Otra'];

    protected $guarded = [];
    protected $casts = ['research_areas'=>'array','submitted_at'=>'datetime','review_started_at'=>'datetime','reviewed_at'=>'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function history(): HasMany { return $this->hasMany(SubscriptionHistory::class)->orderBy('created_at'); }
    public function isProfessional(): bool { return $this->type === self::TYPE_PROFESSIONAL; }
    public function isInstitutional(): bool { return $this->type === self::TYPE_INSTITUTIONAL; }
    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function displayName(): string { return $this->isProfessional() ? trim($this->first_names.' '.$this->last_names) : $this->institution_name; }
    public function publicationInstitution(): string { return $this->isInstitutional() ? $this->institution_name : ''; }
    public function primaryResearchArea(): ?string { return $this->research_areas[0] ?? null; }
}
