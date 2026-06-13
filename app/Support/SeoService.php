<?php

namespace App\Support;

use App\Models\MediaFile;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SeoService
{
    public function suggestions(Page $page): array
    {
        $settings = $this->settings();
        $plainText = $this->plainText($page);
        $siteName = $settings?->site_name ?: 'RIMIS';
        $title = Str::limit(trim($page->title.' | '.$siteName), 60, '');
        $description = Str::limit(trim($page->excerpt ?: $plainText ?: $settings?->site_description), 160, '');

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $this->keywords($page->title.' '.$plainText),
            'canonical_url' => route('pages.show', $page->slug),
            'image_url' => $this->suggestedImage($page)?->publicUrl(),
        ];
    }

    public function forPage(Page $page): array
    {
        $settings = $this->settings();
        $suggestions = $this->suggestions($page);
        $imageUrl = $page->seoImage?->publicUrl() ?: $suggestions['image_url'] ?: $settings?->og_image;

        return [
            'title' => $page->seo_title ?: $suggestions['title'] ?: $settings?->meta_title,
            'description' => $page->seo_description ?: $suggestions['description'] ?: $settings?->meta_description,
            'keywords' => $page->seo_keywords ?: $suggestions['keywords'] ?: $settings?->meta_keywords,
            'canonical_url' => $page->seo_canonical_url ?: $suggestions['canonical_url'],
            'image_url' => $imageUrl ? url($imageUrl) : null,
            'robots' => $page->seo_index && ($settings?->seo_index ?? true) ? 'index, follow' : 'noindex, nofollow',
            'twitter_card' => $settings?->twitter_card ?: 'summary_large_image',
        ];
    }

    public function global(): array
    {
        $settings = $this->settings();
        $suggestions = $this->globalSuggestions($settings);

        return [
            'title' => $settings?->meta_title ?: $suggestions['title'],
            'description' => $settings?->meta_description ?: $suggestions['description'],
            'keywords' => $settings?->meta_keywords ?: $suggestions['keywords'],
            'canonical_url' => url('/'),
            'image_url' => $settings?->og_image ? url($settings->og_image) : null,
            'robots' => ($settings?->seo_index ?? true) ? 'index, follow' : 'noindex, nofollow',
            'twitter_card' => $settings?->twitter_card ?: 'summary_large_image',
        ];
    }

    public function globalSuggestions(?SiteSetting $settings = null): array
    {
        $settings ??= $this->settings();
        $siteName = $settings?->site_name ?: 'RIMIS';
        $description = Str::squish(implode(' ', array_filter([
            $settings?->site_description,
            $settings?->site_slogan,
        ])));

        return [
            'title' => Str::limit($siteName.($settings?->site_slogan ? ' | '.$settings->site_slogan : ''), 60, ''),
            'description' => Str::limit($description ?: 'Red de Investigación Multidisciplinaria', 160, ''),
            'keywords' => $this->keywords($siteName.' '.$description.' investigación ciencia innovación'),
            'canonical_url' => url('/'),
        ];
    }

    private function plainText(Page $page): string
    {
        $blockText = $page->blocks->flatMap(fn ($block) => [
            $block->data['title'] ?? '',
            $block->data['subtitle'] ?? '',
            $block->data['content'] ?? '',
        ])->implode(' ');

        return Str::squish(strip_tags($page->content.' '.$blockText));
    }

    private function keywords(string $text): string
    {
        $stopWords = ['para', 'como', 'este', 'esta', 'estos', 'estas', 'desde', 'entre', 'sobre', 'también', 'tiene', 'tienen', 'todo', 'todos', 'todas', 'una', 'unos', 'unas', 'que', 'del', 'las', 'los', 'con', 'por', 'sus', 'más', 'rimis'];
        $words = preg_split('/[^\pL\pN]+/u', Str::lower(strip_tags($text)));
        $frequencies = array_count_values(array_filter($words, fn ($word) => mb_strlen($word) >= 4 && ! in_array($word, $stopWords, true)));
        arsort($frequencies);

        return implode(', ', array_slice(array_keys($frequencies), 0, 10));
    }

    private function suggestedImage(Page $page): ?MediaFile
    {
        $id = $page->blocks->first(fn ($block) => ! empty($block->data['image_id']))?->data['image_id']
            ?? $page->blocks->first(fn ($block) => ! empty($block->data['image_ids']))?->data['image_ids'][0]
            ?? null;

        return $id ? MediaFile::find($id) : null;
    }

    private function settings(): ?SiteSetting
    {
        return Schema::hasTable('site_settings') ? SiteSetting::find(1) : null;
    }
}
