<?php

use App\Models\GalleryImage;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create one deterministic gallery record without invoking image processing.
 *
 * @param  array<string, mixed>  $overrides
 */
function createGalleryPageImage(array $overrides = []): GalleryImage
{
    return GalleryImage::withoutEvents(
        fn (): GalleryImage => GalleryImage::query()->create([
            'title' => 'Island Supper',
            'alt_text' => 'A Caribbean-inspired dinner at Coast and Cay',
            'image_path' => 'gallery/island-supper.jpg',
            'category' => 'dish',
            'sort_order' => 1,
            'is_visible' => true,
            ...$overrides,
        ]),
    );
}

beforeEach(function (): void {
    Page::query()->create([
        'slug' => 'gallery',
        'title' => 'Gallery',
        'excerpt' => 'A visual journal of Coast and Cay.',
        'content' => 'Food, hospitality, and coastal moments.',
        'is_published' => true,
    ]);
});

test('the gallery renders the editorial experience with visible images', function (): void {
    createGalleryPageImage();

    createGalleryPageImage([
        'title' => 'Sunset Dining Room',
        'image_path' => 'gallery/sunset-room.jpg',
        'category' => 'ambiance',
        'sort_order' => 2,
    ]);

    createGalleryPageImage([
        'title' => 'Hidden Draft',
        'image_path' => 'gallery/hidden-draft.jpg',
        'is_visible' => false,
        'sort_order' => 3,
    ]);

    $response = $this->get(route('gallery'));

    $response
        ->assertOk()
        ->assertSee('data-gallery-page', false)
        ->assertSee('data-gallery-collection', false)
        ->assertSee('data-gallery-dialog', false)
        ->assertSeeText('Island Supper')
        ->assertSeeText('Sunset Dining Room')
        ->assertDontSeeText('Hidden Draft');
});

test('the gallery filters visible images by a valid category', function (): void {
    createGalleryPageImage();

    createGalleryPageImage([
        'title' => 'Ocean Room',
        'image_path' => 'gallery/ocean-room.jpg',
        'category' => 'ambiance',
        'sort_order' => 2,
    ]);

    $response = $this->get(route('gallery', [
        'category' => 'dish',
    ]));

    $response
        ->assertOk()
        ->assertSeeText('Island Supper')
        ->assertDontSeeText('Ocean Room')
        ->assertSee('data-gallery-category="dish"', false)
        ->assertSee('aria-current="true"', false);
});

test('an unknown category safely falls back to the complete collection', function (): void {
    createGalleryPageImage();

    createGalleryPageImage([
        'title' => 'Warm Welcome',
        'image_path' => 'gallery/warm-welcome.jpg',
        'category' => 'people',
        'sort_order' => 2,
    ]);

    $response = $this->get(route('gallery', [
        'category' => 'not-a-real-category',
    ]));

    $response
        ->assertOk()
        ->assertSeeText('Island Supper')
        ->assertSeeText('Warm Welcome')
        ->assertSeeText('The complete contact sheet');
});
