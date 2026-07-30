<?php

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the about page renders the reusable section pager', function (): void {
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
        ->assertSee(
            'data-section-pager-context="about"',
            false,
        )
        ->assertSee(
            'data-section-pager-enhancer="about"',
            false,
        )
        ->assertSee('data-about-section-link', false)
        ->assertSee('href="#about-hero"', false)
        ->assertSee('href="#island-roots"', false)
        ->assertSee('href="#invitation"', false);
});

test('the gallery renders a snapping shared pager and full screen targets', function (): void {
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
        ->assertSee(
            'data-section-pager-context="gallery"',
            false,
        )
        ->assertSee(
            'data-section-pager-enhancer="shared"',
            false,
        )
        ->assertSee(
            'data-section-pager-snap="true"',
            false,
        )
        ->assertSee('data-gallery-section-link', false)
        ->assertSee('id="gallery-hero"', false)
        ->assertSee('id="gallery-signature"', false)
        ->assertSee('id="gallery-collection"', false)
        ->assertSee('id="gallery-invitation"', false)
        ->assertSee('data-gallery-panel', false);
});
