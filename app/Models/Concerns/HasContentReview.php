<?php

namespace App\Models\Concerns;

use App\Models\ContentReviewHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasContentReview
{
    public const ORIGIN_STAFF = 'staff';
    public const ORIGIN_RESEARCHER = 'researcher';
    public const REVIEW_NOT_REQUIRED = 'not_required';
    public const REVIEW_DRAFT = 'draft';
    public const REVIEW_SUBMITTED = 'submitted';
    public const REVIEW_UNDER_REVIEW = 'under_review';
    public const REVIEW_OBSERVED = 'observed';
    public const REVIEW_APPROVED = 'approved';
    public const REVIEW_REJECTED = 'rejected';

    public const REVIEW_STATUS_LABELS = [
        self::REVIEW_NOT_REQUIRED => 'No requiere revisión', self::REVIEW_DRAFT => 'Borrador',
        self::REVIEW_SUBMITTED => 'Enviado', self::REVIEW_UNDER_REVIEW => 'En revisión',
        self::REVIEW_OBSERVED => 'Observado', self::REVIEW_APPROVED => 'Aprobado',
        self::REVIEW_REJECTED => 'Rechazado',
    ];

    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function reviewHistory(): MorphMany { return $this->morphMany(ContentReviewHistory::class, 'reviewable')->latest(); }
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)->whereNotNull('published_at')
            ->where(fn (Builder $q) => $q->where('origin', self::ORIGIN_STAFF)
                ->orWhere(fn (Builder $r) => $r->where('origin', self::ORIGIN_RESEARCHER)->where('review_status', self::REVIEW_APPROVED)));
    }
    public function isStaffContent(): bool { return $this->origin === self::ORIGIN_STAFF; }
    public function isResearcherSubmission(): bool { return $this->origin === self::ORIGIN_RESEARCHER; }
    public function isReviewDraft(): bool { return $this->review_status === self::REVIEW_DRAFT; }
    public function isSubmittedForReview(): bool { return $this->review_status === self::REVIEW_SUBMITTED; }
    public function isUnderReview(): bool { return $this->review_status === self::REVIEW_UNDER_REVIEW; }
    public function isObserved(): bool { return $this->review_status === self::REVIEW_OBSERVED; }
    public function isApprovedForPublication(): bool { return $this->review_status === self::REVIEW_APPROVED; }
    public function isRejected(): bool { return $this->review_status === self::REVIEW_REJECTED; }
    public function isEditableByResearcher(): bool { return $this->status === self::STATUS_DRAFT && ($this->isReviewDraft() || $this->isObserved()); }
    public function reviewStatusLabel(): string { return self::REVIEW_STATUS_LABELS[$this->review_status] ?? $this->review_status; }
}
