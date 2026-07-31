<?php

use Illuminate\Support\Facades\File;

test('homepage motion keeps reduced-motion and lifecycle contracts', function (): void {
    $motion = File::get(
        resource_path('js/homepage-section-navigation.js'),
    );

    expect($motion)
        ->toContain('gsap.matchMedia()')
        ->toContain(
            'reducedMotionQuery = "(prefers-reduced-motion: reduce)"',
        )
        ->toContain('media.revert()')
        ->toContain('import.meta.hot.dispose(cleanup)')
        ->toContain('initializeStoryDepth')
        ->not->toContain('initHomeGalleryCarousel');
});

test('full-screen public section transitions use one timing contract', function (): void {
    $homepageMotion = File::get(
        resource_path('js/homepage-section-navigation.js'),
    );

    $aboutMotion = File::get(
        resource_path('js/about-experience.js'),
    );

    $sharedPager = File::get(
        resource_path('js/section-pager.js'),
    );

    expect($homepageMotion)
        ->toContain('sectionTransitionDuration = 0.48')
        ->toContain('wheelActivationThreshold = 8')
        ->toContain('wheelGestureReleaseDelay = 140');

    expect($aboutMotion)
        ->toContain('sectionTransitionDuration = 0.48')
        ->toContain('wheelActivationThreshold = 8')
        ->toContain('wheelGestureReleaseDelay = 140');

    expect($sharedPager)
        ->toContain('sectionTransitionDuration = 0.48')
        ->toContain('wheelActivationThreshold = 8')
        ->toContain('wheelGestureReleaseDelay = 140');
});

test('homepage uses the reusable menu product card', function (): void {
    $homepage = File::get(
        resource_path('views/pages/home.blade.php'),
    );

    $featuredMenu = File::get(
        resource_path(
            'views/components/public/home-featured-menu.blade.php',
        ),
    );

    $menuCard = File::get(
        resource_path(
            'views/components/public/menu-card.blade.php',
        ),
    );

    expect($homepage)
        ->toContain('<x-public.home-featured-menu')
        ->not->toContain('<x-public.home-menu-card');

    expect($featuredMenu)
        ->toContain('<x-public.menu-card')
        ->toContain(':use-modal="false"');

    expect($menuCard)
        ->toContain("'useModal' => true")
        ->toContain('@if ($useModal)')
        ->toContain('data-menu-card');
});

test('homepage story uses the modern editorial component', function (): void {
    $homepage = File::get(
        resource_path('views/pages/home.blade.php'),
    );

    $story = File::get(
        resource_path(
            'views/components/public/home-story-section.blade.php',
        ),
    );

    expect($homepage)
        ->toContain('<x-public.home-story-section');

    expect($story)
        ->toContain('data-home-story-media')
        ->toContain('home-story-card')
        ->toContain('home-story-note')
        ->toContain('data-home-reveal');
});

test('menu and cart opt into the transparent public header', function (): void {
    $menu = File::get(
        resource_path('views/pages/menu.blade.php'),
    );

    $menuHero = File::get(
        resource_path(
            'views/components/public/menu-hero.blade.php',
        ),
    );

    $cart = File::get(
        resource_path('views/pages/cart.blade.php'),
    );

    expect($menu)
        ->toContain(':header-overlay="true"')
        ->toContain('<x-public.menu-hero');

    expect($menuHero)
        ->toContain('data-public-hero')
        ->toContain('data-menu-hero')
        ->toContain('data-menu-hero-depth')
        ->toContain('bg-[linear-gradient');

    expect($cart)
        ->toContain(':header-overlay="true"')
        ->toContain('cart-page-shell');
});

test('dark public banners use shared readability safeguards', function (): void {
    $styles = File::get(
        resource_path('css/homepage-experience.css'),
    );

    expect($styles)
        ->toContain('[data-public-hero]')
        ->toContain('#about-hero')
        ->toContain('#gallery-hero')
        ->toContain('#contact-hero')
        ->toContain('text-shadow')
        ->toContain('opacity: 1 !important')
        ->toContain('visibility: visible !important');
});

test(
    'homepage gallery uses the responsive split composition without retired carousel assets',
    function (): void {
        $homepage = File::get(
            resource_path('views/pages/home.blade.php'),
        );

        $styles = File::get(
            resource_path('css/public.css'),
        );

        expect($homepage)
            ->toContain('id="gallery-preview"')
            ->toContain(
                'lg:grid-cols-[0.72fr_1.28fr]',
            )
            ->toContain(
                'sm:grid-cols-[1.15fr_0.85fr]',
            )
            ->toContain('<x-public.responsive-image')
            ->not->toContain('data-home-gallery');

        expect($styles)
            ->not->toContain(
                '[data-home-gallery-enhanced]',
            )
            ->not->toContain(
                '.home-gallery-controls',
            );
    },
);
