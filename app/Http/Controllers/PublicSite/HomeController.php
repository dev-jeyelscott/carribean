<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class HomeController extends Controller
{
    /**
     * Display the public homepage with lightweight restaurant content.
     */
    public function index(): View
    {
        $galleryImages = GalleryImage::query()
            ->visible()
            ->ordered()
            ->limit(6)
            ->get();

        $heroImage = $galleryImages->firstWhere('category', 'interior')
            ?? $galleryImages->first();

        $storyImage = $galleryImages->firstWhere('category', 'dish')
            ?? $galleryImages->skip(1)->first()
            ?? $heroImage;

        $featuredCategories = MenuCategory::query()
            ->visible()
            ->whereHas(
                'menuItems',
                fn(Builder $query): Builder => $query
                    ->where('is_visible', true),
            )
            ->ordered()
            ->limit(3)
            ->get();

        return view('pages.home', [
            'page' => Page::query()
                ->where('slug', 'home')
                ->where('is_published', true)
                ->first(),

            'featuredCategories' => $featuredCategories,

            'featuredMenuItems' => MenuItem::query()
                ->with('menuCategory')
                ->visible()
                ->ordered()
                ->limit(3)
                ->get(),

            'galleryImages' => $galleryImages,
            'heroImage' => $heroImage,
            'storyImage' => $storyImage,
        ]);
    }
}
