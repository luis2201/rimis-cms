<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class News extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $table = 'news';

    protected $fillable = [
        'user_id', 'category_id', 'featured_image_id', 'title', 'slug', 'excerpt', 'content',
        'status', 'is_featured', 'published_at', 'seo_title', 'seo_description', 'seo_keywords', 'seo_index',
    ];

    protected $casts = ['is_featured' => 'boolean', 'seo_index' => 'boolean', 'published_at' => 'datetime'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'featured_image_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(NewsTag::class, 'news_news_tag');
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
}
