<?php

namespace App\Models;

use App\Models\Concerns\HasContentReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Bulletin extends Model
{
    use HasContentReview;
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'user_id', 'cover_image_id', 'title', 'slug', 'description', 'pdf_path',
        'pdf_original_name', 'pdf_size', 'status', 'published_at', 'origin', 'review_status',
        'submitted_at', 'review_started_at', 'reviewed_at', 'reviewed_by', 'review_notes',
    ];

    protected $casts = ['published_at' => 'datetime', 'submitted_at' => 'datetime', 'review_started_at' => 'datetime', 'reviewed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::deleting(function (Bulletin $bulletin) {
            if ($bulletin->pdf_path && Storage::disk('local')->exists($bulletin->pdf_path)) {
                Storage::disk('local')->delete($bulletin->pdf_path);
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'cover_image_id');
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

    public function formattedSize(): string
    {
        return $this->pdf_size ? number_format($this->pdf_size / 1048576, 1).' MB' : 'PDF';
    }
}
