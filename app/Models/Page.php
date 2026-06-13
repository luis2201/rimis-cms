<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = ['user_id', 'title', 'slug', 'excerpt', 'content', 'show_title', 'seo_title', 'seo_description', 'seo_keywords', 'seo_canonical_url', 'seo_image_id', 'seo_index', 'status', 'published_at'];

    protected $casts = ['show_title' => 'boolean', 'seo_index' => 'boolean', 'published_at' => 'datetime'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('sort_order');
    }

    public function seoImage(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'seo_image_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)->whereNotNull('published_at');
    }

    public function publish(): void
    {
        $this->update(['status' => self::STATUS_PUBLISHED, 'published_at' => now()]);
    }

    public function unpublish(): void
    {
        $this->update(['status' => self::STATUS_DRAFT, 'published_at' => null]);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->published_at !== null;
    }
}
