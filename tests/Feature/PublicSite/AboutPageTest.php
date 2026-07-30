<?php

use App\Models\GalleryImage;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->aboutPage = Page::query()->create([
        'slug' => 'about',
        'title' => 'About Coast & Cay',
        'excerpt' => 'A restaurant inspired by Caribbean warmth and California ease.',
        'content' => '<p>Our restaurant story.</p>',
        'sections' => [
            'hero' => [
                'title' => 'About Coast & Cay.',
                'accent' => 'Rooted in the Caribbean. Inspired by California.',
            ],
        ],
        'meta_title' => 'About Coast & Cay',
        'meta_description' => 'Learn about Coast & Cay.',
        'is_published' => true,
    ]);
});

it('renders the dedicated about page', function (): void {
    $response = $this->get(route('about'));

    $response
        ->assertOk()
        ->assertViewIs('pages.about')
        ->assertViewHas(
            'page',
            fn (Page $page): bool => $page->is($this->aboutPage),
        )
        ->assertSee('About Coast & Cay.')
        ->assertSee(
            'Rooted in the Caribbean. Inspired by California.',
        )
        ->assertSee('data-about-page', false)
        ->assertSee('id="island-roots"', false)
        ->assertSee('id="values"', false)
        ->assertSee('id="heritage"', false)
        ->assertSee('id="experience"', false)
        ->assertSee('id="invitation"', false);
});

it('returns not found when the about page is unpublished', function (): void {
    $this->aboutPage->update([
        'is_published' => false,
    ]);

    $this->get(route('about'))
        ->assertNotFound();
});

it('uses only visible gallery images', function (): void {
    $visibleImage = GalleryImage::query()->create([
        'title' => 'Visible interior',
        'alt_text' => 'Visible restaurant interior',
        'image_path' => 'gallery/visible-interior.jpg',
        'category' => 'interior',
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    GalleryImage::query()->create([
        'title' => 'Hidden interior',
        'alt_text' => 'Hidden restaurant interior',
        'image_path' => 'gallery/hidden-interior.jpg',
        'category' => 'about-hero',
        'sort_order' => 0,
        'is_visible' => false,
    ]);

    $response = $this->get(route('about'));

    $response
        ->assertOk()
        ->assertViewHas(
            'heroImage',
            fn (?GalleryImage $image): bool => $image?->is(
                $visibleImage,
            ) ?? false,
        )
        ->assertViewHas(
            'valueImages',
            fn (Collection $images): bool => $images->every(
                fn (GalleryImage $image): bool => $image->is_visible,
            ),
        )
        ->assertViewHas(
            'experienceImages',
            fn (Collection $images): bool => $images->every(
                fn (GalleryImage $image): bool => $image->is_visible,
            ),
        );
});
