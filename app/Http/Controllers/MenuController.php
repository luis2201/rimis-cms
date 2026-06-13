<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        return view('admin.menus.index', ['menus' => Menu::withCount('items')->latest()->get()]);
    }

    public function create(): View
    {
        return view('admin.menus.create', ['locations' => Menu::LOCATIONS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $menu = Menu::create($this->validateMenu($request));

        return redirect()->route('admin.menus.show', $menu)->with('success', 'Menú creado correctamente.');
    }

    public function show(Menu $menu): View
    {
        return view('admin.menus.show', [
            'menu' => $menu->load(['rootItems', 'items']),
            'parentItems' => $menu->items()->get(),
            'publishedPages' => Page::published()->orderBy('title')->get(['title', 'slug']),
        ]);
    }

    public function edit(Menu $menu): View
    {
        return view('admin.menus.edit', ['menu' => $menu, 'locations' => Menu::LOCATIONS]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $menu->update($this->validateMenu($request, $menu));

        return redirect()->route('admin.menus.index')->with('success', 'Menú actualizado correctamente.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menú eliminado correctamente.');
    }

    private function validateMenu(Request $request, ?Menu $menu = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location' => ['required', Rule::in(array_keys(Menu::LOCATIONS)), Rule::unique('menus')->ignore($menu)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
