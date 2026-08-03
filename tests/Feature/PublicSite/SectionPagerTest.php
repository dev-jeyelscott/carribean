<?php

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the homepage keeps its section runtime without rendering the visible section pager', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee(
            'data-home-section-pager',
            false,
        )
        ->assertDontSee(
            'data-section-pager-context="home"',
            false,
        )
        ->assertDontSee(
            'data-section-pager-enhancer="shared"',
            false,
        )
        ->assertDontSee(
            'aria-label="Homepage sections"',
            false,
        );
});

test('the about page uses its dedicated full-screen section contract', function (): void {
    Page::query()->create([
        'slug' => 'about',
        'title' => 'About',
        'excerpt' => 'Our Coast and Cay story.',
        'content' => 'Caribbean warmth and California ease.',
        'is_published' => true,
    ]);

    $response = $this->get(route('about'));

    $response
        ->assertOk()
        ->assertSee('data-about-page', false)
        ->assertSee('data-about-panel', false)
        ->assertSee('id="about-hero"', false)
        ->assertSee('id="island-roots"', false)
        ->assertSee('id="values"', false)
        ->assertSee('id="heritage"', false)
        ->assertSee('id="experience"', false)
        ->assertSee('id="invitation"', false)
        ->assertDontSee(
            'data-section-pager-context="about"',
            false,
        );
});

test('the gallery uses one collage section without the retired section pager', function (): void {
    Page::query()->create([
        'slug' => 'gallery',
        'title' => 'Gallery',
        'excerpt' => 'A visual journal of Coast and Cay.',
        'content' => 'Food, hospitality, and coastal moments.',
        'is_published' => true,
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
        ->assertSee('data-gallery-dialog', false)
        ->assertDontSee('data-gallery-panel', false)
        ->assertDontSee('id="gallery-hero"', false)
        ->assertDontSee(
            'id="gallery-signature"',
            false,
        )
        ->assertDontSee(
            'id="gallery-invitation"',
            false,
        )
        ->assertDontSee(
            'data-section-pager-context="gallery"',
            false,
        );

    expect(
        substr_count(
            $response->getContent(),
            'id="gallery-collection"',
        ),
    )->toBe(1);
});
