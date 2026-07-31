<?php

use Illuminate\Support\Facades\File;

test('homepage renders the approved Caribbean restaurant sections', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Taste the Caribbean.')
        ->assertSeeText('Feel the Islands.')
        ->assertSeeText("Chef's favorites")
        ->assertSeeText('A little taste of the islands.')
        ->assertSeeText(
            'Rooted in the Caribbean. At home on the coast.',
        )
        ->assertSeeText('Find your next favorite')
        ->assertSeeText('Food, warmth, and coastal evenings.')
        ->assertSeeText('Ready for good food and island vibes?')
        ->assertSeeText('Caribbean warmth, California ease.')
        ->assertSeeText('We can’t wait to welcome you');
});

test('homepage exposes accessible navigation and dedicated motion hooks', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('aria-controls="mobile-navigation"', false)
        ->assertSee(':aria-expanded="open.toString()"', false)
        ->assertSee('aria-label="Primary navigation"', false)
        ->assertSee('aria-label="Mobile navigation"', false)
        ->assertSee('data-home-motion', false)
        ->assertSee('data-home-section-pager', false)
        ->assertSee('data-home-panel', false)
        ->assertSee('data-home-reveal', false)
        ->assertSee('data-public-hero', false);
});

test('public motion loads dedicated homepage and shared page modules progressively', function (): void {
    $appEntry = File::get(resource_path('js/app.js'));

    $homepageModule = File::get(
        resource_path('js/homepage-section-navigation.js'),
    );

    $sharedMotionModule = File::get(
        resource_path('js/public-animations.js'),
    );

    expect($appEntry)
        ->toContain('[data-home-motion]')
        ->toContain('import("./public-animations")')
        ->toContain('[data-home-section-pager]')
        ->toContain('import("./homepage-section-navigation")');

    expect($homepageModule)
        ->toContain('gsap.matchMedia()')
        ->toContain('querySelectorAll("[data-home-reveal]")')
        ->toContain('prefers-reduced-motion: reduce')
        ->not->toContain('home-gallery-carousel')
        ->not->toContain('initHomeGalleryCarousel')
        ->not->toContain('animateDeliveryAddress');

    expect($sharedMotionModule)
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
