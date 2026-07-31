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
        ->storeAs('gallery', $filename, 'public');

    expect($path)->toBeString();

    return $path;
}

test('gallery page presents visible Coast and Cay images without retired copy', function (): void {
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

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSee('data-gallery-page', false)
        ->assertSee('data-gallery-motion', false)
        ->assertSee('data-public-hero', false)
        ->assertSee('data-gallery-collection', false)
        ->assertSee('data-gallery-dialog', false)
        ->assertSee('data-gsap="hero-content"', false)
        ->assertSeeText('An Island, Framed.')
        ->assertSeeText(
            'A visual rhythm of flavor, place, and welcome.',
        )
        ->assertSeeText('Coastal Dining Room')
        ->assertSeeText('Island Jerk Chicken')
        ->assertDontSeeText('Le Jardin')
        ->assertDontSeeText('Private Celebrations')
        ->assertDontSeeText('private-event');
});

test('gallery page limits each editorial volume to five images', function (): void {
    foreach (range(1, 7) as $index) {
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

    $expectedFirstPageTitles = collect(range(1, 5))
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
                return $galleryImages->perPage() === 5
                    && $galleryImages->currentPage() === 1
                    && $galleryImages
                        ->getCollection()
                        ->pluck('title')
                        ->all() === $expectedFirstPageTitles;
            },
        )
        ->assertSeeText('Coast and Cay Moment 01')
        ->assertSeeText('Coast and Cay Moment 05')
        ->assertDontSeeText('Coast and Cay Moment 06')
        ->assertSee('rel="next"', false);

    $this->get(route('gallery', ['page' => 2]))
        ->assertOk()
        ->assertSeeText('Coast and Cay Moment 06')
        ->assertSeeText('Coast and Cay Moment 07')
        ->assertDontSeeText('Coast and Cay Moment 05');
});

test('gallery page renders sanitized managed editorial copy', function (): void {
    Page::query()->create([
        'slug' => 'gallery',
        'title' => 'Gallery',
        'content' => '<p>Every image reflects <strong>Caribbean hospitality</strong>.</p><p onclick="alert(1)">Managed through the CMS.</p>',
        'is_published' => true,
    ]);

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSeeText(
            'Every image reflects Caribbean hospitality',
        )
        ->assertSeeText('Managed through the CMS.')
        ->assertDontSee('<strong>', false)
        ->assertDontSee('onclick=', false)
        ->assertDontSee('alert(1)', false);
});

test('gallery page uses the current scope-safe empty state', function (): void {
    $this->get(route('gallery'))
        ->assertOk()
        ->assertSeeText(
            'No visible gallery moments match this collection yet.',
        )
        ->assertSeeText('Contact the Team')
        ->assertDontSeeText('Le Jardin')
        ->assertDontSeeText('Private Celebrations')
        ->assertDontSeeText('Reservation Request');
});
