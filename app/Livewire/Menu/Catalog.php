<?php

namespace App\Livewire\Menu;

use App\Models\MenuCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

final class Catalog extends Component
{
    /**
     * Render every visible category and its complete visible item collection.
     *
     * Product configuration and cart mutations are intentionally delegated to
     * the reusable product modal so the catalogue remains presentation-focused.
     */
    public function render(): View
    {
        $categories = MenuCategory::query()
            ->visible()
            ->withCount([
                'menuItems as visible_menu_items_count' => fn (
                    Builder $query,
                ) => $query->visible(),
            ])
            ->with([
                'menuItems' => fn (Builder $query) => $query
                    ->reorder()
                    ->visible()
                    ->ordered()
                    ->withCount('optionGroups')
                    ->with('menuCategory'),
            ])
            ->ordered()
            ->get();

        return view('components.public.menu-catalog', [
            'categories' => $categories,
        ]);
    }
}
