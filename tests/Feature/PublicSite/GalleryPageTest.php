<?php

use App\Models\GalleryImage;

/**
 * Insert predictable Gallery records without invoking image-file processing.
 */
function seedGalleryPageImages(
    int $count,
    bool $visible = true,
    string $prefix = 'Gallery',
): void {
    $timestamp = now();

    GalleryImage::query()->insert(
        collect(range(1, $count))
            ->map(
                fn (int $index): array => [
                    'title' => "{$prefix} moment {$index}",
                    'alt_text' => "{$prefix} image {$index}",
                    'image_path' => "gallery/{$prefix}-{$index}.jpg",
                    'category' => $index % 2 === 0
                        ? 'Dining'
                        : 'Cuisine',
                    'sort_order' => $index,
                    'is_visible' => $visible,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
            )
            ->all(),
    );
}

test('gallery renders one collage experience with a load more fallback', function (): void {
    seedGalleryPageImages(13);

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSee('data-gallery-page', false)
        ->assertSee('data-gallery-section', false)
        ->assertSee('data-gallery-grid', false)
        ->assertSee('data-gallery-layout="feature"', false)
        ->assertSee('data-gallery-layout="portrait"', false)
        ->assertSee('data-gallery-layout="square"', false)
        ->assertSee('data-gallery-layout="landscape"', false)
        ->assertSee('data-gallery-load-more', false)
        ->assertSee('data-gallery-dialog', false)
        ->assertSeeText('Load more moments')
        ->assertDontSee('id="gallery-hero"', false)
        ->assertDontSee('id="gallery-signature"', false)
        ->assertDontSee('id="gallery-invitation"', false);
});

test('gallery returns the next server-rendered fragment as json', function (): void {
    seedGalleryPageImages(13);

    $response = $this
        ->withHeader(
            'Accept',
            'application/json',
        )
        ->get(
            route(
                'gallery',
                [
                    'page' => 2,
                ],
            ),
        );

    $response
        ->assertOk()
        ->assertJsonStructure([
            'html',
            'next_page_url',
        ])
        ->assertJsonPath(
            'next_page_url',
            null,
        );

    expect($response->json('html'))
        ->toContain('data-gallery-item')
        ->toContain('Gallery moment 13');
});

test('gallery excludes images that are not publicly visible', function (): void {
    seedGalleryPageImages(
        1,
        true,
        'Visible',
    );

    seedGalleryPageImages(
        1,
        false,
        'Hidden',
    );

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSeeText('Visible moment 1')
        ->assertDontSeeText('Hidden moment 1');
});
