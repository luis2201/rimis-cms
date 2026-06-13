<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBlock extends Model
{
    public const TYPES = [
        'hero' => ['label' => 'Hero', 'icon' => 'fa-solid fa-panorama'],
        'text' => ['label' => 'Texto', 'icon' => 'fa-solid fa-align-left'],
        'image' => ['label' => 'Imagen', 'icon' => 'fa-regular fa-image'],
        'text_image' => ['label' => 'Texto + Imagen', 'icon' => 'fa-solid fa-table-columns'],
        'gallery' => ['label' => 'Galería', 'icon' => 'fa-regular fa-images'],
        'cards' => ['label' => 'Cards', 'icon' => 'fa-solid fa-grip'],
        'video' => ['label' => 'Video', 'icon' => 'fa-solid fa-circle-play'],
        'buttons' => ['label' => 'Botones', 'icon' => 'fa-solid fa-arrow-pointer'],
        'faq' => ['label' => 'FAQ', 'icon' => 'fa-regular fa-circle-question'],
        'html' => ['label' => 'HTML personalizado', 'icon' => 'fa-solid fa-code'],
        'dynamic_list' => ['label' => 'Listado dinámico', 'icon' => 'fa-solid fa-list'],
    ];

    protected $fillable = ['page_id', 'type', 'name', 'data', 'sort_order', 'is_active'];

    protected $casts = ['data' => 'array', 'is_active' => 'boolean'];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function typeIcon(): string
    {
        return self::TYPES[$this->type]['icon'] ?? 'fa-solid fa-cube';
    }

    public function videoEmbedUrl(): ?string
    {
        $url = $this->data['url'] ?? '';
        $host = parse_url($url, PHP_URL_HOST);
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true) && $path) {
            return 'https://www.youtube.com/embed/'.rawurlencode($path);
        }

        if (in_array($host, ['youtube.com', 'www.youtube.com'], true)) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            if (! empty($query['v'])) {
                return 'https://www.youtube.com/embed/'.rawurlencode($query['v']);
            }
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com'], true) && ctype_digit($path)) {
            return 'https://player.vimeo.com/video/'.$path;
        }

        return null;
    }
}
