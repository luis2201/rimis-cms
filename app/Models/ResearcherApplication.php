<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResearcherApplication extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_OBSERVED = 'observed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Borrador',
        self::STATUS_SUBMITTED => 'Enviada',
        self::STATUS_UNDER_REVIEW => 'En revisión',
        self::STATUS_OBSERVED => 'Observada',
        self::STATUS_APPROVED => 'Aprobada',
        self::STATUS_REJECTED => 'Rechazada',
        self::STATUS_WITHDRAWN => 'Retirada',
    ];

    protected $fillable = [
        'user_id', 'status', 'motivation', 'experience_summary', 'expected_contribution',
        'profile_snapshot', 'submitted_at', 'review_started_at', 'reviewed_at',
        'reviewed_by', 'review_notes',
    ];

    protected $casts = [
        'profile_snapshot' => 'array',
        'submitted_at' => 'datetime',
        'review_started_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public static function statusLabels(): array
    {
        return self::STATUS_LABELS;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(ResearcherApplicationHistory::class)->orderBy('created_at');
    }

    public function isDraft(): bool { return $this->status === self::STATUS_DRAFT; }
    public function isSubmitted(): bool { return $this->status === self::STATUS_SUBMITTED; }
    public function isUnderReview(): bool { return $this->status === self::STATUS_UNDER_REVIEW; }
    public function isObserved(): bool { return $this->status === self::STATUS_OBSERVED; }
    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function isRejected(): bool { return $this->status === self::STATUS_REJECTED; }
    public function isWithdrawn(): bool { return $this->status === self::STATUS_WITHDRAWN; }

    public function isEditableByApplicant(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_OBSERVED], true);
    }
}
