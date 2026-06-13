<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    public const LOCATIONS = [
        'principal' => 'Principal',
        'footer' => 'Footer',
        'social' => 'Redes sociales',
    ];

    protected $fillable = ['name', 'location', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id')->with('children');
    }
}
