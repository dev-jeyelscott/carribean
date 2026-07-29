<?php

namespace App\Livewire\Menu;

use App\Models\MenuCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Component;

final class Catalog extends Component
{
    /**
     * Render every visible category and its complete visible item collection.
     *
     * Product configuration and cart mutations are delegated to the reusable
     * product modal so this component remains presentation-focused.
     */
    public function render(): View
    {
        $categories = MenuCategory::query()
            ->visible()
            ->withCount([
                'menuItems as visible_menu_items_count' => function (
                    Builder $query,
                ): void {
                    $query->where('menu_items.is_visible', true);
                },
            ])
            ->with([
                'menuItems' => function (Relation $relation): void {
                    $relation
                        ->getQuery()
                        ->reorder()
                        ->where('menu_items.is_visible', true)
                        ->orderBy('menu_items.sort_order')
                        ->orderBy('menu_items.name')
                        ->withCount('optionGroups')
                        ->with('menuCategory');
                },
            ])
            ->ordered()
            ->get();

        return view('components.public.menu-catalog', [
            'categories' => $categories,
        ]);
    }
}
