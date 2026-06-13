<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageBlock;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageBlockController extends Controller
{
    public function __construct(private HtmlSanitizer $sanitizer)
    {
    }

    public function store(Request $request, Page $page): RedirectResponse
    {
        $validated = $this->validateBlock($request);
        $validated['sort_order'] = $page->blocks()->max('sort_order') + 1;
        $page->blocks()->create($validated);

        return back()->with('success', 'Bloque agregado correctamente.');
    }

    public function update(Request $request, Page $page, PageBlock $pageBlock): RedirectResponse
    {
        $this->ensureBelongsToPage($page, $pageBlock);
        $pageBlock->update($this->validateBlock($request));

        return back()->with('success', 'Bloque actualizado correctamente.');
    }

    public function destroy(Page $page, PageBlock $pageBlock): RedirectResponse
    {
        $this->ensureBelongsToPage($page, $pageBlock);
        $pageBlock->delete();

        return back()->with('success', 'Bloque eliminado correctamente.');
    }

    public function move(Page $page, PageBlock $pageBlock, string $direction): RedirectResponse
    {
        $this->ensureBelongsToPage($page, $pageBlock);
        abort_unless(in_array($direction, ['up', 'down'], true), 404);

        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';
        $sibling = $page->blocks()
            ->where('sort_order', $operator, $pageBlock->sort_order)
            ->orderBy('sort_order', $order)
            ->first();

        if ($sibling) {
            [$blockOrder, $siblingOrder] = [$pageBlock->sort_order, $sibling->sort_order];
            $pageBlock->update(['sort_order' => $siblingOrder]);
            $sibling->update(['sort_order' => $blockOrder]);
        }

        return back()->with('success', 'Orden de bloques actualizado.');
    }

    private function validateBlock(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(PageBlock::TYPES))],
            'name' => ['nullable', 'string', 'max:120'],
            'is_active' => ['required', 'boolean'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'image_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'image_position' => ['nullable', Rule::in(['left', 'right'])],
            'image_ids' => ['nullable', 'array'],
            'image_ids.*' => ['integer', 'exists:media_files,id'],
            'url' => ['nullable', 'string', 'max:2048'],
            'items' => ['nullable', 'string'],
            'source' => ['nullable', Rule::in(['published_pages', 'media_images', 'recent_news', 'featured_news', 'news_category'])],
            'category_id' => ['nullable', 'integer', 'exists:news_categories,id'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:24'],
        ]);

        $type = $validated['type'];
        $content = $validated['content'] ?? '';
        $data = array_filter([
            'title' => $validated['title'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'content' => in_array($type, ['text', 'text_image', 'html'], true) ? $this->sanitizer->clean($content) : $content,
            'image_id' => isset($validated['image_id']) ? (int) $validated['image_id'] : null,
            'image_position' => $validated['image_position'] ?? 'right',
            'image_ids' => array_map('intval', $validated['image_ids'] ?? []),
            'url' => $validated['url'] ?? null,
            'items' => $this->parseItems($validated['items'] ?? '', $type),
            'source' => $validated['source'] ?? 'published_pages',
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'limit' => (int) ($validated['limit'] ?? 6),
        ], fn ($value) => $value !== null && $value !== '' && $value !== []);

        return [
            'type' => $type,
            'name' => $validated['name'] ?? null,
            'is_active' => (bool) $validated['is_active'],
            'data' => $data,
        ];
    }

    private function parseItems(string $items, string $type): array
    {
        return collect(preg_split('/\r\n|\r|\n/', trim($items)))
            ->filter()
            ->map(fn (string $line) => array_map('trim', explode('|', $line, 3)))
            ->map(function (array $item) use ($type) {
                $urlIndex = $type === 'buttons' ? 1 : ($type === 'cards' ? 2 : null);
                if ($urlIndex !== null && isset($item[$urlIndex]) && ! $this->isSafeUrl($item[$urlIndex])) {
                    $item[$urlIndex] = '#';
                }

                return $item;
            })
            ->values()
            ->all();
    }

    private function isSafeUrl(string $url): bool
    {
        return str_starts_with($url, '/') || str_starts_with($url, '#') || filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    private function ensureBelongsToPage(Page $page, PageBlock $pageBlock): void
    {
        abort_unless((int) $pageBlock->page_id === (int) $page->id, 404);
    }
}
