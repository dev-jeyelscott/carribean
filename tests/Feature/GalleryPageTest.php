<?php

use App\Models\GalleryImage;
use App\Models\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

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
        'image_path' => storeGalleryPageTestImage('coastal-dining-room.jpg'),
        'category' => 'Ambiance',
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    GalleryImage::query()->create([
        'title' => 'Island Jerk Chicken',
        'alt_text' => 'Island jerk chicken with rice and peas',
        'image_path' => storeGalleryPageTestImage('island-jerk-chicken.jpg'),
        'category' => 'Cuisine',
        'sort_order' => 2,
        'is_visible' => true,
    ]);

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSee('id="gallery-collection"', false)
        ->assertSee('data-home-motion', false)
        ->assertSee('data-gallery-motion', false)
        ->assertSee('data-gsap="hero-image"', false)
        ->assertSeeText('A taste of the island')
        ->assertSeeText('Food, hospitality, and coastal moments')
        ->assertSeeText('Coastal Dining Room')
        ->assertSeeText('Island Jerk Chicken')
        ->assertDontSeeText('Le Jardin')
        ->assertDontSeeText('Private Celebrations')
        ->assertDontSeeText('private-event');
});

test('gallery page bounds visible images with simple pagination', function (): void {
    foreach (range(1, 14) as $index) {
        GalleryImage::query()->create([
            'title' => sprintf('Coast and Cay Moment %02d', $index),
            'alt_text' => "Coast and Cay moment {$index}",
            'image_path' => "gallery/coast-and-cay-{$index}.jpg",
            'category' => $index % 2 === 0 ? 'Cuisine' : 'Ambiance',
            'sort_order' => $index,
            'is_visible' => true,
        ]);
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
            function (Paginator $galleryImages) use ($expectedFirstPageTitles): bool {
                return $galleryImages->perPage() === 12
                    && $galleryImages->currentPage() === 1
                    && $galleryImages->getCollection()->pluck('title')->all()
                        === $expectedFirstPageTitles;
            },
        )
        ->assertSeeText('Coast and Cay Moment 01')
        ->assertSeeText('Coast and Cay Moment 12')
        ->assertDontSeeText('Coast and Cay Moment 13')
        ->assertSee('rel="next"', false);
});

test('gallery page renders sanitized rich editor content', function (): void {
    Page::query()->create([
        'slug' => 'gallery',
        'title' => 'Gallery',
        'content' => '<p>Every image reflects <strong>Caribbean hospitality</strong>.</p><p onclick="alert(1)">Managed through the CMS.</p>',
        'is_published' => true,
    ]);

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSee('<strong>Caribbean hospitality</strong>', false)
        ->assertSeeText('Managed through the CMS.')
        ->assertDontSee('onclick=', false);
});

test('gallery page uses a scope-safe empty state', function (): void {
    $this->get(route('gallery'))
        ->assertOk()
        ->assertSeeText('Our gallery is currently being curated.')
        ->assertSeeText('Contact Us')
        ->assertDontSeeText('Le Jardin')
        ->assertDontSeeText('Private Celebrations');
});
