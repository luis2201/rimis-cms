<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MenuItemController extends Controller
{
    public function store(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $this->validateItem($request, $menu);
        $validated['sort_order'] = $menu->items()->where('parent_id', $validated['parent_id'] ?? null)->max('sort_order') + 1;
        $menu->items()->create($validated);

        return back()->with('success', 'Ítem agregado correctamente.');
    }

    public function update(Request $request, Menu $menu, MenuItem $menuItem): RedirectResponse
    {
        $this->ensureBelongsToMenu($menu, $menuItem);
        $validated = $this->validateItem($request, $menu, $menuItem);

        if ((int) ($validated['parent_id'] ?? 0) !== (int) ($menuItem->parent_id ?? 0)) {
            $validated['sort_order'] = $menu->items()->where('parent_id', $validated['parent_id'] ?? null)->max('sort_order') + 1;
        }

        $menuItem->update($validated);

        return back()->with('success', 'Ítem actualizado correctamente.');
    }

    public function destroy(Menu $menu, MenuItem $menuItem): RedirectResponse
    {
        $this->ensureBelongsToMenu($menu, $menuItem);
        $menuItem->delete();

        return back()->with('success', 'Ítem eliminado correctamente.');
    }

    public function move(Menu $menu, MenuItem $menuItem, string $direction): RedirectResponse
    {
        $this->ensureBelongsToMenu($menu, $menuItem);
        abort_unless(in_array($direction, ['up', 'down'], true), 404);

        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';
        $sibling = $menu->items()
            ->where('parent_id', $menuItem->parent_id)
            ->where('sort_order', $operator, $menuItem->sort_order)
            ->orderBy('sort_order', $order)
            ->first();

        if ($sibling) {
            [$itemOrder, $siblingOrder] = [$menuItem->sort_order, $sibling->sort_order];
            $menuItem->update(['sort_order' => $siblingOrder]);
            $sibling->update(['sort_order' => $itemOrder]);
        }

        return back()->with('success', 'Orden actualizado.');
    }

    private function validateItem(Request $request, Menu $menu, ?MenuItem $item = null): array
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:2048'],
            'icon' => ['nullable', 'string', 'max:100'],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'parent_id' => ['nullable', Rule::exists('menu_items', 'id')->where('menu_id', $menu->id), Rule::notIn([$item?->id])],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($item && $this->wouldCreateCycle($item, $validated['parent_id'] ?? null)) {
            throw ValidationException::withMessages([
                'parent_id' => 'Un ítem no puede depender de uno de sus propios submenús.',
            ]);
        }

        return $validated;
    }

    private function ensureBelongsToMenu(Menu $menu, MenuItem $item): void
    {
        abort_unless((int) $item->menu_id === (int) $menu->id, 404);
    }

    private function wouldCreateCycle(MenuItem $item, ?int $parentId): bool
    {
        while ($parentId) {
            if ((int) $parentId === (int) $item->id) {
                return true;
            }

            $parentId = MenuItem::find($parentId)?->parent_id;
        }

        return false;
    }
}
