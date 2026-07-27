<?php

use Illuminate\Support\Facades\File;

test('homepage renders the approved Caribbean restaurant sections', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Taste the Caribbean.')
        ->assertSeeText('Feel the Islands.')
        ->assertSeeText('Featured Dishes')
        ->assertSeeText('Rooted in Tradition.')
        ->assertSeeText('Find your next favorite')
        ->assertSeeText('Made for Good Company')
        ->assertSeeText('Caribbean warmth, California ease.')
        ->assertSeeText('We can’t wait to welcome you');
});

test('homepage exposes accessible navigation and progressive motion hooks', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('aria-controls="mobile-navigation"', false)
        ->assertSee(':aria-expanded="open.toString()"', false)
        ->assertSee('aria-label="Primary navigation"', false)
        ->assertSee('aria-label="Mobile navigation"', false)
        ->assertSee('data-home-motion', false)
        ->assertSee('data-public-hero', false)
        ->assertSee('data-gsap="hero-content"', false)
        ->assertSee('data-gsap-reveal', false)
        ->assertSee('data-reveal', false);
});

test('public motion no longer loads the retired home gallery carousel or delivery address helper', function (): void {
    $appEntry = File::get(resource_path('js/app.js'));
    $motionModule = File::get(resource_path('js/public-animations.js'));

    expect($appEntry)
        ->toContain('document.querySelector("[data-home-motion]")')
        ->toContain('import("./public-animations")');

    expect($motionModule)
        ->toContain('gsap.matchMedia()')
        ->toContain('reducedMotion')
        ->toContain('media.revert()')
        ->not->toContain('home-gallery-carousel')
        ->not->toContain('initHomeGalleryCarousel')
        ->not->toContain('animateDeliveryAddress');
});

test('homepage does not expose retired workflow labels', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSeeText('Reservation Request')
        ->assertDontSeeText('Order Inquiry')
        ->assertDontSeeText('Banquet Hall')
        ->assertDontSeeText('Private Celebrations');
});
