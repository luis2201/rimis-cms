<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Page;
use App\Support\HtmlSanitizer;
use App\Support\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private HtmlSanitizer $sanitizer, private SeoService $seo)
    {
    }

    public function publicShow(string $slug): View
    {
        $page = Page::published()->with(['blocks' => fn ($query) => $query->where('is_active', true), 'seoImage'])->where('slug', $slug)->firstOrFail();
        $media = $this->blockMedia($page)->keyBy('id');
        $seo = $this->seo->forPage($page);
        $dynamicLists = [
            'published_pages' => Page::published()->where('id', '!=', $page->id)->latest('published_at')->limit(24)->get(),
            'media_images' => MediaFile::where('file_type', 'image')->where('status', true)->latest()->limit(24)->get(),
            'recent_news' => News::published()->with(['category', 'featuredImage'])->latest('published_at')->limit(24)->get(),
            'featured_news' => News::published()->where('is_featured', true)->with(['category', 'featuredImage'])->latest('published_at')->limit(24)->get(),
        ];
        $page->blocks
            ->where('type', 'dynamic_list')
            ->pluck('data.category_id')
            ->filter()
            ->unique()
            ->each(function ($categoryId) use (&$dynamicLists) {
                $dynamicLists['news_category_'.$categoryId] = News::published()
                    ->where('category_id', $categoryId)
                    ->with(['category', 'featuredImage'])
                    ->latest('published_at')
                    ->limit(24)
                    ->get();
            });

        return view('pages.show', compact('page', 'media', 'dynamicLists', 'seo'));
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $pages = Page::query()
            ->with('author')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(fn ($query) => $query->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
            })
            ->when(in_array($status, [Page::STATUS_DRAFT, Page::STATUS_PUBLISHED], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        $mediaImages = MediaFile::where('file_type', 'image')->where('status', true)->latest()->get();

        return view('admin.pages.create', compact('mediaImages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePage($request);
        $validated['user_id'] = $request->user()->id;
        $validated['status'] = Page::STATUS_DRAFT;
        $validated['published_at'] = null;

        $page = Page::create($validated);

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Página creada como borrador.');
    }

    public function edit(Page $page): View
    {
        $page->load('blocks');
        $mediaImages = MediaFile::where('file_type', 'image')->where('status', true)->latest()->get();
        $newsCategories = NewsCategory::where('is_active', true)->orderBy('name')->get();
        $seoSuggestions = $this->seo->suggestions($page);

        return view('admin.pages.edit', compact('page', 'mediaImages', 'newsCategories', 'seoSuggestions'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page->update($this->validatePage($request, $page));

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Página actualizada correctamente.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Página eliminada correctamente.');
    }

    public function publish(Page $page): RedirectResponse
    {
        $page->publish();

        return back()->with('success', 'Página publicada correctamente.');
    }

    public function unpublish(Page $page): RedirectResponse
    {
        $page->unpublish();

        return back()->with('success', 'Página despublicada correctamente.');
    }

    private function validatePage(Request $request, ?Page $page = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('title')),
            'seo_index' => $request->input('seo_index', true),
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('pages')->ignore($page)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'show_title' => ['required', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:60'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'seo_canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo_image_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'seo_index' => ['required', 'boolean'],
        ]);

        $validated['content'] = $this->sanitizer->clean($validated['content'] ?? '');

        return $validated;
    }

    private function blockMedia(Page $page)
    {
        $ids = $page->blocks->flatMap(function ($block) {
            return array_merge(
                isset($block->data['image_id']) ? [$block->data['image_id']] : [],
                $block->data['image_ids'] ?? []
            );
        })->filter()->unique();

        return MediaFile::whereIn('id', $ids)->get();
    }
}
