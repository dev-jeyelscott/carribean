<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Contracts\View\View;

class MenuController extends Controller
{
    /**
     * Display the public restaurant menu with one visible menu image available
     * for the full-screen hero.
     */
    public function index(): View
    {
        $heroItem = MenuItem::query()
            ->visible()
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->ordered()
            ->first();

        return view('pages.menu', [
            'page' => Page::query()
                ->where('slug', 'menu')
                ->where('is_published', true)
                ->first(),
            'heroItem' => $heroItem,
        ]);
    }
}
