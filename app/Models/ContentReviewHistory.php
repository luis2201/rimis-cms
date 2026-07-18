<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentReviewHistory extends Model
{
    protected $table = 'content_review_history';
    protected $fillable = ['previous_status', 'new_status', 'comments', 'changed_by'];
    public function reviewable(): MorphTo { return $this->morphTo(); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
