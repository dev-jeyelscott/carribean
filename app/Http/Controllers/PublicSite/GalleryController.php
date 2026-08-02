<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Define the number of visible gallery images rendered per request.
     *
     * Twelve items provide enough variety for the collage while keeping the
     * initial response and responsive-image requests reasonably small.
     */
    private const IMAGES_PER_PAGE = 12;

    /**
     * Display the public Gallery page or return the next collage fragment.
     *
     * Normal requests render the complete server-side page. JSON requests are
     * used only as a progressive enhancement for the Load More interaction.
     */
    public function index(Request $request): View|JsonResponse
    {
        $galleryImages = GalleryImage::query()
            ->visible()
            ->ordered()
            ->simplePaginate(self::IMAGES_PER_PAGE)
            ->withQueryString()
            ->fragment('gallery-grid');

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view(
                    'partials.public.gallery-items',
                    [
                        'galleryImages' => $galleryImages,
                    ],
                )->render(),
                'next_page_url' => $galleryImages->nextPageUrl(),
            ]);
        }

        $page = Page::query()
            ->where('slug', 'gallery')
            ->where('is_published', true)
            ->first();

        return view('pages.gallery', [
            'page' => $page,
            'galleryImages' => $galleryImages,
            'coverImage' => GalleryImage::query()
                ->visible()
                ->ordered()
                ->first(),
            'totalImageCount' => GalleryImage::query()
                ->visible()
                ->count(),
        ]);
    }
}
