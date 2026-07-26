<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    /**
     * Display the public homepage with featured restaurant content.
     */
    public function index(): View
    {
        $allGalleryImages = GalleryImage::query()
            ->visible()
            ->ordered()
            ->limit(12)
            ->get();

        $heroImage = $allGalleryImages->firstWhere('category', 'dish')
            ?? $allGalleryImages->first();

        $storyImage = $allGalleryImages->firstWhere('category', 'interior')
            ?? $allGalleryImages
                ->first(
                    fn (GalleryImage $image): bool => $image->isNot($heroImage),
                )
            ?? $heroImage;

        $featuredCategories = MenuCategory::query()
            ->visible()
            ->whereHas(
                'visibleMenuItems',
                fn (Builder $query): Builder => $query
                    ->whereNotNull('image_path'),
            )
            ->with([
                'visibleMenuItems' => fn (Builder $query): Builder => $query
                    ->whereNotNull('image_path'),
            ])
            ->ordered()
            ->limit(4)
            ->get();

        $featuredMenuItems = MenuItem::query()
            ->with('menuCategory')
            ->visible()
            ->featured()
            ->ordered()
            ->limit(4)
            ->get();

        if ($featuredMenuItems->isEmpty()) {
            $featuredMenuItems = MenuItem::query()
                ->with('menuCategory')
                ->visible()
                ->ordered()
                ->limit(4)
                ->get();
        }

        return view('pages.home', [
            'page' => Page::query()
                ->where('slug', 'home')
                ->where('is_published', true)
                ->first(),

            'featuredCategories' => $featuredCategories,
            'featuredMenuItems' => $featuredMenuItems,
            'galleryImages' => $this->galleryPreviewImages(
                $allGalleryImages,
                $heroImage,
                $storyImage,
            ),
            'heroImage' => $heroImage,
            'storyImage' => $storyImage,
        ]);
    }

    /**
     * Return gallery images that do not repeat the primary hero and story
     * photographs whenever enough alternative images are available.
     *
     * @param  Collection<int, GalleryImage>  $images
     * @return Collection<int, GalleryImage>
     */
    private function galleryPreviewImages(
        Collection $images,
        ?GalleryImage $heroImage,
        ?GalleryImage $storyImage,
    ): Collection {
        $excludedIds = array_values(array_filter([
            $heroImage?->getKey(),
            $storyImage?->getKey(),
        ]));

        $previewImages = $images
            ->reject(
                fn (GalleryImage $image): bool => in_array(
                    $image->getKey(),
                    $excludedIds,
                    true,
                ),
            )
            ->take(7)
            ->values();

        if ($previewImages->count() >= 7) {
            return $previewImages;
        }

        return $images
            ->take(7)
            ->values();
    }
}
