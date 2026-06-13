<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const MODALITY_IN_PERSON = 'in_person';

    public const MODALITY_VIRTUAL = 'virtual';

    public const MODALITY_HYBRID = 'hybrid';

    protected $fillable = [
        'user_id', 'featured_image_id', 'title', 'slug', 'summary', 'description',
        'starts_at', 'ends_at', 'modality', 'location', 'organizer',
        'responsible_name', 'contact_email', 'contact_phone', 'website_url',
        'status', 'published_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'featured_image_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)->whereNotNull('published_at');
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
