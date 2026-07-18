<?php

namespace App\Models;

use App\Models\Concerns\HasContentReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasContentReview;
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const MODALITY_IN_PERSON = 'in_person';

    public const MODALITY_VIRTUAL = 'virtual';

    public const MODALITY_HYBRID = 'hybrid';

    protected $fillable = [
        'user_id', 'featured_image_id', 'title', 'slug', 'summary', 'description',
        'starts_at', 'ends_at', 'modality', 'location', 'organizer',
        'responsible_name', 'contact_email', 'contact_phone', 'website_url',
        'status', 'published_at', 'origin', 'review_status', 'submitted_at',
        'review_started_at', 'reviewed_at', 'reviewed_by', 'review_notes',
        'attachment_path', 'attachment_original_name', 'attachment_mime_type', 'attachment_size',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'published_at' => 'datetime',
        'submitted_at' => 'datetime', 'review_started_at' => 'datetime', 'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Event $event) {
            if ($event->attachment_path) Storage::disk('local')->delete($event->attachment_path);
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

    public function modalityLabel(): string
    {
        return match ($this->modality) {
            self::MODALITY_VIRTUAL => 'Virtual',
            self::MODALITY_HYBRID => 'Híbrido',
            default => 'Presencial',
        };
    }
}
