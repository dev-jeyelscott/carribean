<?php

use Illuminate\Support\Facades\File;

test('homepage renders the approved Caribbean restaurant sections', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Flavors worth sharing')
        ->assertSeeText('Caribbean warmth. California ease.')
        ->assertSeeText('Island favorites')
        ->assertSeeText('Dine your way')
        ->assertSeeText('A taste of the island')
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
        ->assertSee('data-gsap="hero-image"', false)
        ->assertSee('data-gsap="menu"', false)
        ->assertSee('data-gsap="panel"', false);
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
