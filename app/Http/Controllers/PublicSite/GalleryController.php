<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    private const IMAGES_PER_PAGE = 12;

    /**
     * Display the public gallery with server-rendered category filtering.
     */
    public function index(Request $request): View
    {
        $page = Page::query()
            ->where('slug', 'gallery')
            ->where('is_published', true)
            ->first();

        $categoryCounts = GalleryImage::query()
            ->visible()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->selectRaw('category, COUNT(*) AS image_count')
            ->groupBy('category')
            ->orderBy('category')
            ->pluck('image_count', 'category')
            ->map(
                fn (mixed $count): int => (int) $count,
            );

        $requestedCategory = $request
            ->string('category')
            ->trim()
            ->toString();

        $selectedCategory = $requestedCategory !== ''
            && $categoryCounts->has($requestedCategory)
                ? $requestedCategory
                : null;

        $heroImage = GalleryImage::query()
            ->visible()
            ->ordered()
            ->first();

        $highlightImagesQuery = GalleryImage::query()
            ->visible()
            ->ordered();

        if ($heroImage !== null) {
            $highlightImagesQuery->where(
                'id',
                '!=',
                $heroImage->getKey(),
            );
        }

        $highlightImages = $highlightImagesQuery
            ->limit(3)
            ->get();

        $galleryImagesQuery = GalleryImage::query()
            ->visible()
            ->ordered();

        if ($selectedCategory !== null) {
            $galleryImagesQuery->where(
                'category',
                $selectedCategory,
            );
        }

        $galleryImages = $galleryImagesQuery
            ->simplePaginate(self::IMAGES_PER_PAGE)
            ->withQueryString()
            ->fragment('gallery-collection');

        return view('pages.gallery', [
            'page' => $page,
            'heroImage' => $heroImage,
            'highlightImages' => $highlightImages,
            'galleryImages' => $galleryImages,
            'categoryCounts' => $categoryCounts,
            'selectedCategory' => $selectedCategory,
            'totalImageCount' => GalleryImage::query()
                ->visible()
                ->count(),
        ]);
    }
}
