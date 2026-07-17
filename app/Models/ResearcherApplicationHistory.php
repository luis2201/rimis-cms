<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearcherApplicationHistory extends Model
{
    protected $table = 'researcher_application_history';

    protected $fillable = [
        'researcher_application_id', 'previous_status', 'new_status', 'comments', 'changed_by',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ResearcherApplication::class, 'researcher_application_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
