<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Models\SiteSetting;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function __construct(private HtmlSanitizer $sanitizer)
    {
    }

    public function publicIndex(Request $request): View
    {
        $featured = News::published()->where('is_featured', true)->with('featuredImage')->latest('published_at')->first();
        $categories = NewsCategory::where('is_active', true)->withCount(['news' => fn ($query) => $query->published()])->orderBy('name')->get();
        $recentNews = News::published()->with(['category', 'featuredImage'])->latest('published_at')->limit(6)->get();

        return view('news.index', compact('featured', 'categories', 'recentNews'));
    }

    public function publicAll(Request $request): View
    {
        return $this->listing($request, null);
    }

    public function publicCategory(Request $request, NewsCategory $category): View
    {
        abort_unless($category->is_active, 404);

        return $this->listing($request, $category);
    }

    public function publicShow(string $slug): View
    {
        $news = News::published()->with(['author', 'category', 'tags', 'featuredImage'])->where('slug', $slug)->firstOrFail();
        $settings = SiteSetting::find(1);
        $siteName = $settings?->site_name ?: 'RIMIS';
        $description = $news->seo_description ?: Str::limit($news->excerpt ?: strip_tags($news->content), 160, '');
        $seo = [
            'title' => $news->seo_title ?: Str::limit($news->title.' | '.$siteName, 60, ''),
            'description' => $description,
            'keywords' => $news->seo_keywords ?: $news->tags->pluck('name')->implode(', '),
            'canonical_url' => route('news.show', $news->slug),
            'image_url' => $news->featuredImage ? url($news->featuredImage->publicUrl()) : ($settings?->og_image ? url($settings->og_image) : null),
            'robots' => $news->seo_index && ($settings?->seo_index ?? true) ? 'index, follow' : 'noindex, nofollow',
            'twitter_card' => $settings?->twitter_card ?: 'summary_large_image',
        ];
        $related = News::published()->where('id', '!=', $news->id)->when($news->category_id, fn ($query) => $query->where('category_id', $news->category_id))->latest('published_at')->limit(3)->get();

        return view('news.show', compact('news', 'seo', 'related'));
    }

    public function index(Request $request): View
    {
        $news = News::with(['category', 'author'])
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.news.index', compact('news'));
    }

    public function create(): View
    {
        return view('admin.news.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateNews($request);
        $validated['user_id'] = $request->user()->id;
        $validated['status'] = News::STATUS_DRAFT;
        $validated['published_at'] = null;
        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);
        $news = News::create($validated);
        $news->tags()->sync($tags);

        return redirect()->route('admin.news.edit', $news)->with('success', 'Noticia creada como borrador.');
    }

    public function edit(News $news): View
    {
        $news->load('tags');

        return view('admin.news.edit', array_merge($this->formData(), compact('news')));
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $validated = $this->validateNews($request, $news);
        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);
        $news->update($validated);
        $news->tags()->sync($tags);

        return back()->with('success', 'Noticia actualizada correctamente.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'Noticia eliminada correctamente.');
    }

    public function publish(News $news): RedirectResponse
    {
        $news->publish();

        return back()->with('success', 'Noticia publicada correctamente.');
    }

    public function unpublish(News $news): RedirectResponse
    {
        $news->unpublish();

        return back()->with('success', 'Noticia despublicada correctamente.');
    }

    public function feature(News $news): RedirectResponse
    {
        $news->update(['is_featured' => ! $news->is_featured]);

        return back()->with('success', $news->is_featured ? 'Noticia destacada.' : 'Noticia retirada de destacados.');
    }

    private function validateNews(Request $request, ?News $news = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('title')),
            'is_featured' => $request->boolean('is_featured'),
            'seo_index' => $request->input('seo_index', true),
        ]);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('news')->ignore($news)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:news_categories,id'],
            'featured_image_id' => ['nullable', 'exists:media_files,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:news_tags,id'],
            'is_featured' => ['required', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:60'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'seo_index' => ['required', 'boolean'],
        ]);
        $validated['content'] = $this->sanitizer->clean($validated['content']);

        return $validated;
    }

    private function formData(): array
    {
        return [
            'categories' => NewsCategory::where('is_active', true)->orderBy('name')->get(),
            'tags' => NewsTag::orderBy('name')->get(),
            'mediaImages' => MediaFile::where('file_type', 'image')->where('status', true)->latest()->get(),
        ];
    }

    private function listing(Request $request, ?NewsCategory $category): View
    {
        $news = News::published()
            ->with(['category', 'featuredImage'])
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->when($request->filled('tag'), fn ($query) => $query->whereHas('tags', fn ($query) => $query->where('slug', $request->string('tag'))))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();
        $categories = NewsCategory::where('is_active', true)
            ->withCount(['news' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();

        return view('news.listing', compact('news', 'categories', 'category'));
    }
}
