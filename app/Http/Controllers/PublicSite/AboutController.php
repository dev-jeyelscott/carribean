<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

final class AboutController extends Controller
{
    /**
     * Display the dedicated public About page.
     */
    public function __invoke(): View
    {
        $page = Page::query()
            ->published()
            ->where('slug', 'about')
            ->firstOrFail();

        /** @var Collection<int, GalleryImage> $images */
        $images = GalleryImage::query()
            ->visible()
            ->ordered()
            ->limit(16)
            ->get();

        $heroImage = $this->preferredImage(
            images: $images,
            categories: [
                'about-hero',
                'interior',
                'ambiance',
                'dish',
            ],
        );

        $storyImage = $this->preferredImage(
            images: $images,
            categories: [
                'interior',
                'ambiance',
                'guests',
            ],
            excludedIds: array_filter([
                $heroImage?->getKey(),
            ], is_int(...)),
        );

        $heritageImage = $this->preferredImage(
            images: $images,
            categories: [
                'ingredients',
                'dish',
                'ambiance',
            ],
            excludedIds: array_values(array_filter([
                $heroImage?->getKey(),
                $storyImage?->getKey(),
            ], is_int(...))),
        );

        $closingImage = $this->preferredImage(
            images: $images,
            categories: [
                'dish',
                'guests',
                'ambiance',
            ],
            excludedIds: array_values(array_filter([
                $heroImage?->getKey(),
                $storyImage?->getKey(),
                $heritageImage?->getKey(),
            ], is_int(...))),
        );

        $usedImageIds = array_values(array_filter([
            $heroImage?->getKey(),
            $storyImage?->getKey(),
            $heritageImage?->getKey(),
            $closingImage?->getKey(),
        ], is_int(...)));

        return view('pages.about', [
            'page' => $page,
            'heroImage' => $heroImage,
            'storyImage' => $storyImage,
            'heritageImage' => $heritageImage,
            'closingImage' => $closingImage,

            'valueImages' => $this->imageSet(
                images: $images,
                preferredCategories: [
                    'dish',
                    'ingredients',
                    'team',
                    'interior',
                ],
                excludedIds: [],
                count: 4,
            ),

            'experienceImages' => $this->imageSet(
                images: $images,
                preferredCategories: [
                    'guests',
                    'team',
                    'ambiance',
                    'interior',
                    'dish',
                ],
                excludedIds: $usedImageIds,
                count: 4,
            ),
        ]);
    }

    /**
     * Select one preferred image while respecting already assigned images.
     *
     * @param  Collection<int, GalleryImage>  $images
     * @param  list<string>  $categories
     * @param  list<int>  $excludedIds
     */
    private function preferredImage(
        Collection $images,
        array $categories,
        array $excludedIds = [],
    ): ?GalleryImage {
        $eligibleImages = $images->reject(
            fn (GalleryImage $image): bool => in_array(
                (int) $image->getKey(),
                $excludedIds,
                true,
            ),
        );

        return $eligibleImages->first(
            fn (GalleryImage $image): bool => in_array(
                $image->category,
                $categories,
                true,
            ),
        )
            ?? $eligibleImages->first()
            ?? $images->first();
    }

    /**
     * Build a fixed-size image collection and repeat safe fallbacks when the
     * development gallery does not yet contain enough unique photographs.
     *
     * @param  Collection<int, GalleryImage>  $images
     * @param  list<string>  $preferredCategories
     * @param  list<int>  $excludedIds
     * @return Collection<int, GalleryImage>
     */
    private function imageSet(
        Collection $images,
        array $preferredCategories,
        array $excludedIds,
        int $count,
    ): Collection {
        $eligibleImages = $images->reject(
            fn (GalleryImage $image): bool => in_array(
                (int) $image->getKey(),
                $excludedIds,
                true,
            ),
        );

        $preferredImages = $eligibleImages->filter(
            fn (GalleryImage $image): bool => in_array(
                $image->category,
                $preferredCategories,
                true,
            ),
        );

        $pool = $preferredImages
            ->concat($eligibleImages)
            ->unique(
                fn (GalleryImage $image): int => (int) $image->getKey(),
            )
            ->values();

        if ($pool->isEmpty()) {
            /** @var Collection<int, GalleryImage> $empty */
            $empty = new Collection;

            return $empty;
        }

        /** @var Collection<int, GalleryImage> $result */
        $result = new Collection;

        while ($result->count() < $count) {
            foreach ($pool as $image) {
                $result->push($image);

                if ($result->count() >= $count) {
                    break;
                }
            }
        }

        return $result->take($count)->values();
    }
}
