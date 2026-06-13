<?php

namespace App\Http\Controllers;

use App\Models\NewsCategory;
use App\Models\NewsTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NewsTaxonomyController extends Controller
{
    public function index(): View
    {
        return view('admin.news.taxonomies', [
            'categories' => NewsCategory::withCount('news')->orderBy('name')->get(),
            'tags' => NewsTag::withCount('news')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);
        NewsCategory::create($validated);

        return back()->with('success', 'Categoría creada correctamente.');
    }

    public function updateCategory(Request $request, NewsCategory $category): RedirectResponse
    {
        $category->update($this->validateCategory($request, $category));

        return back()->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroyCategory(NewsCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Categoría eliminada correctamente.');
    }

    public function storeTag(Request $request): RedirectResponse
    {
        $validated = $this->validateTag($request);
        NewsTag::create($validated);

        return back()->with('success', 'Etiqueta creada correctamente.');
    }

    public function destroyTag(NewsTag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('success', 'Etiqueta eliminada correctamente.');
    }

    private function validateCategory(Request $request, ?NewsCategory $category = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name'))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('news_categories')->ignore($category)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function validateTag(Request $request): array
    {
        $request->merge(['slug' => Str::slug($request->input('name'))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:news_tags,name'],
            'slug' => ['required', 'string', 'max:120', 'unique:news_tags,slug'],
        ]);
    }
}
