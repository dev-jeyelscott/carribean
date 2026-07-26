<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Contracts\View\View;

final class MenuItemController extends Controller
{
    /**
     * Display one visible menu item with its currently available options.
     */
    public function __invoke(MenuItem $menuItem): View
    {
        abort_unless($menuItem->is_visible, 404);

        abort_unless(
            $menuItem->menuCategory()
                ->where('is_visible', true)
                ->exists(),
            404,
        );

        $menuItem->load([
            'menuCategory',
            'optionGroups' => fn ($query) => $query
                ->ordered()
                ->with([
                    'options' => fn ($optionQuery) => $optionQuery
                        ->available()
                        ->ordered(),
                ]),
        ]);

        return view('pages.menu-item', [
            'menuItem' => $menuItem,
        ]);
    }
}
