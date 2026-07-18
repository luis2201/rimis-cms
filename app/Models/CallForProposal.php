<?php

namespace App\Models;

use App\Models\Concerns\HasContentReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CallForProposal extends Model
{
    use HasContentReview;
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $table = 'calls';

    protected $fillable = [
        'user_id', 'featured_image_id', 'title', 'slug', 'summary', 'description',
        'opens_at', 'closes_at', 'bases_pdf_path', 'bases_pdf_original_name',
        'bases_pdf_size', 'registration_enabled', 'registration_url', 'status', 'published_at',
        'origin', 'review_status', 'submitted_at', 'review_started_at', 'reviewed_at', 'reviewed_by', 'review_notes',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'registration_enabled' => 'boolean',
        'published_at' => 'datetime',
        'submitted_at' => 'datetime', 'review_started_at' => 'datetime', 'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (CallForProposal $call) {
            if ($call->bases_pdf_path && Storage::disk('local')->exists($call->bases_pdf_path)) {
                Storage::disk('local')->delete($call->bases_pdf_path);
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'featured_image_id');
    }

    public function publish(): void
    {
        $this->update(['status' => self::STATUS_PUBLISHED, 'published_at' => $this->published_at ?: now()]);
    }

    public function unpublish(): void
    {
        $this->update(['status' => self::STATUS_DRAFT, 'published_at' => null]);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->published_at !== null;
    }

    public function operationalStatus(): string
    {
        if (now()->lt($this->opens_at)) {
            return 'upcoming';
        }

        return now()->lte($this->closes_at) ? 'open' : 'closed';
    }

    public function operationalStatusLabel(): string
    {
        return match ($this->operationalStatus()) {
            'upcoming' => 'Próxima',
            'open' => 'Abierta',
            default => 'Cerrada',
        };
    }

    public function operationalStatusColor(): string
    {
        return match ($this->operationalStatus()) {
            'upcoming' => 'info',
            'open' => 'success',
            default => 'secondary',
        };
    }

    public function operationalStatusPublicClasses(): string
    {
        return match ($this->operationalStatus()) {
            'upcoming' => 'bg-sky-100 text-sky-700',
            'open' => 'bg-emerald-100 text-emerald-700',
            default => 'bg-slate-100 text-slate-600',
        };
    }

    public function acceptsRegistrations(): bool
    {
        return $this->registration_enabled
            && filled($this->registration_url)
            && $this->operationalStatus() === 'open';
    }

    public function formattedBasesSize(): string
    {
        return $this->bases_pdf_size ? number_format($this->bases_pdf_size / 1048576, 1).' MB' : 'PDF';
    }
}
