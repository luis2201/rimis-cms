<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'site_description',
        'site_slogan',
        'logo',
        'logo_white',
        'favicon',
        'primary_color',
        'secondary_color',
        'accent_color',
        'email',
        'phone',
        'mobile',
        'address',
        'facebook',
        'instagram',
        'youtube',
        'linkedin',
        'x_twitter',
        'tiktok',
        'footer_text',
        'copyright_text',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'seo_index',
        'twitter_card',
        'header_scripts',
        'footer_scripts',
        'maintenance_mode',
        'status',
    ];

    protected $casts = [
        'seo_index' => 'boolean',
        'maintenance_mode' => 'boolean',
        'status' => 'boolean',
    ];
}
