<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'original_name',
        'file_path',
        'disk',
        'file_type',
        'mime_type',
        'size',
        'alt_text',
        'description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function publicUrl(): string
    {
        if ($this->disk === 'public') {
            return '/storage/'.ltrim($this->file_path, '/');
        }

        return Storage::disk($this->disk)->url($this->file_path);
    }
}
