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

test('page-scoped public motion preserves intentional transition contracts', function (): void {
    $homepageMotion = File::get(
        resource_path('js/homepage-section-navigation.js'),
    );

    $aboutMotion = File::get(
        resource_path('js/about-experience.js'),
    );

    $galleryMotion = File::get(
        resource_path('js/gallery-experience.js'),
    );

    expect($homepageMotion)
        ->toContain('sectionTransitionDuration = 0.48')
        ->toContain('wheelActivationThreshold = 8')
        ->toContain('wheelGestureReleaseDelay = 140');

    expect($aboutMotion)
        ->toContain('sectionTransitionDuration = 0.48')
        ->toContain('wheelActivationThreshold = 8')
        ->toContain('wheelGestureReleaseDelay = 140');

    expect($galleryMotion)
        ->toContain('gsap.registerPlugin(ScrollTrigger);')
        ->toContain('const reducedMotionQuery =')
        ->toContain('"(prefers-reduced-motion: reduce)"')
        ->not->toContain('wheelActivationThreshold')
        ->not->toContain('sectionTransitionDuration = 0.48');
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

    $menuCatalog = File::get(
        resource_path(
            'views/components/public/menu-catalog.blade.php',
        ),
    );

    $cart = File::get(
        resource_path('views/pages/cart.blade.php'),
    );

    expect($menu)
        ->toContain(':header-overlay="true"')
        ->toContain('<livewire:menu.catalog')
        ->not->toContain('<x-public.menu-hero');

    expect($menuCatalog)
        ->toContain('id="menu-catalog"')
        ->toContain('data-menu-category-link')
        ->toContain('data-menu-section')
        ->toContain('data-menu-carousel');

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
