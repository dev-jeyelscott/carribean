<?php

use App\Models\GalleryImage;
use App\Models\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

/**
 * Store one valid Gallery image for HTTP rendering tests.
 */
function storeGalleryPageTestImage(string $filename): string
{
    $path = UploadedFile::fake()
        ->image($filename, 2400, 1600)
        ->storeAs(
            'gallery',
            $filename,
            'public',
        );

    expect($path)->toBeString();

    return $path;
}

test('gallery page presents visible images inside the one-section collage', function (): void {
    GalleryImage::query()->create([
        'title' => 'Coastal Dining Room',
        'alt_text' => 'Warm island-inspired dining room',
        'image_path' => storeGalleryPageTestImage(
            'coastal-dining-room.jpg',
        ),
        'category' => 'ambiance',
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    GalleryImage::query()->create([
        'title' => 'Island Jerk Chicken',
        'alt_text' => 'Island jerk chicken with rice and peas',
        'image_path' => storeGalleryPageTestImage(
            'island-jerk-chicken.jpg',
        ),
        'category' => 'dish',
        'sort_order' => 2,
        'is_visible' => true,
    ]);

    $response = $this->get(route('gallery'));

    $response
        ->assertOk()
        ->assertSee('data-gallery-page', false)
        ->assertSee(
            'data-gallery-motion-state="idle"',
            false,
        )
        ->assertSee('data-gallery-section', false)
        ->assertSee('id="gallery-collection"', false)
        ->assertSee('data-gallery-grid', false)
        ->assertSee('data-gallery-item', false)
        ->assertSee('data-gallery-dialog', false)
        ->assertSee('data-gallery-previous', false)
        ->assertSee('data-gallery-next', false)
        ->assertSeeText('Coastal moments')
        ->assertSeeText('Gallery')
        ->assertSeeText('Coastal Dining Room')
        ->assertSeeText('Island Jerk Chicken')
        ->assertDontSee('data-gallery-hero', false)
        ->assertDontSee('data-gallery-panel', false)
        ->assertDontSee('id="gallery-signature"', false)
        ->assertDontSee('id="gallery-invitation"', false)
        ->assertDontSeeText('An Island, Framed.')
        ->assertDontSeeText(
            'A visual rhythm of flavor, place, and welcome.',
        )
        ->assertDontSeeText('Le Jardin')
        ->assertDontSeeText('Private Celebrations')
        ->assertDontSeeText('private-event');

    /*
     * Count the Gallery section marker rather than every footer section
     * rendered by the shared public layout.
     */
    expect(
        substr_count(
            $response->getContent(),
            'id="gallery-collection"',
        ),
    )->toBe(1);
});

test('gallery page renders twelve initial collage images with progressive pagination', function (): void {
    foreach (range(1, 13) as $index) {
        GalleryImage::withoutEvents(
            fn (): GalleryImage => GalleryImage::query()->create([
                'title' => sprintf(
                    'Coast and Cay Moment %02d',
                    $index,
                ),
                'alt_text' => "Coast and Cay moment {$index}",
                'image_path' => "gallery/coast-and-cay-{$index}.jpg",
                'category' => $index % 2 === 0
                    ? 'dish'
                    : 'ambiance',
                'sort_order' => $index,
                'is_visible' => true,
            ]),
        );
    }

    $expectedFirstPageTitles = collect(range(1, 12))
        ->map(
            fn (int $index): string => sprintf(
                'Coast and Cay Moment %02d',
                $index,
            ),
        )
        ->all();

    $this->get(route('gallery'))
        ->assertOk()
        ->assertViewHas(
            'galleryImages',
            function (
                Paginator $galleryImages,
            ) use ($expectedFirstPageTitles): bool {
                return $galleryImages->perPage() === 12
                    && $galleryImages->currentPage() === 1
                    && $galleryImages
                        ->getCollection()
                        ->pluck('title')
                        ->all() === $expectedFirstPageTitles;
            },
        )
        ->assertSeeText('Coast and Cay Moment 01')
        ->assertSeeText('Coast and Cay Moment 12')
        ->assertDontSeeText('Coast and Cay Moment 13')
        ->assertSee('data-gallery-load-more', false)
        ->assertSeeText('Load more moments');

    $this->get(
        route(
            'gallery',
            [
                'page' => 2,
            ],
        ),
    )
        ->assertOk()
        ->assertSeeText('Coast and Cay Moment 13')
        ->assertDontSeeText('Coast and Cay Moment 12');
});

test('gallery page renders escaped managed collage copy', function (): void {
    Page::query()->create([
        'slug' => 'gallery',
        'title' => 'Gallery',
        'excerpt' => 'Fallback Gallery excerpt.',
        'content' => 'Legacy Gallery body content.',
        'sections' => [
            'gallery' => [
                'eyebrow' => 'Island memories',
                'heading' => 'Gathered Moments',
                'description' => 'Managed <script>alert(1)</script> Gallery copy.',
            ],
        ],
        'is_published' => true,
    ]);

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSeeText('Island memories')
        ->assertSeeText('Gathered Moments')
        ->assertSee(
            'Managed &lt;script&gt;alert(1)&lt;/script&gt; Gallery copy.',
            false,
        )
        ->assertDontSee(
            '<script>alert(1)</script>',
            false,
        )
        ->assertDontSeeText(
            'Legacy Gallery body content.',
        );
});

test('gallery page uses the one-section empty state', function (): void {
    $response = $this->get(route('gallery'));

    $response
        ->assertOk()
        ->assertSee('data-gallery-page', false)
        ->assertSee('data-gallery-section', false)
        ->assertSee('data-gallery-dialog', false)
        ->assertSeeText('New moments are coming')
        ->assertSeeText(
            'The next chapter is being prepared.',
        )
        ->assertSeeText(
            'Our food, dining room, and coastal gatherings will appear here soon.',
        )
        ->assertDontSeeText(
            'No visible gallery moments match this collection yet.',
        )
        ->assertDontSeeText('Contact the Team')
        ->assertDontSeeText('Le Jardin')
        ->assertDontSeeText('Private Celebrations')
        ->assertDontSeeText('Reservation Request');

    expect(
        substr_count(
            $response->getContent(),
            'id="gallery-collection"',
        ),
    )->toBe(1);
});
