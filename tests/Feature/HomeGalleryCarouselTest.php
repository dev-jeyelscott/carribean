<?php

use Illuminate\Support\Facades\File;

test('homepage motion keeps reduced-motion and lifecycle contracts', function (): void {
    $configuration = File::get(
        resource_path('js/section-scroll-trigger-config.js'),
    );

    $motion = File::get(
        resource_path('js/homepage-section-navigation.js'),
    );

    expect($configuration)
        ->toContain(
            'reducedMotion: "(prefers-reduced-motion: reduce)"',
        );

    expect($motion)
        ->toContain('gsap.matchMedia()')
        ->toContain('homepageSectionScrollTriggerConfig')
        ->toContain('matchMedia.revert()')
        ->toContain('import.meta.hot.dispose(cleanup)')
        ->toContain('initializeStoryDepth')
        ->not->toContain('initHomeGalleryCarousel');
});

test('page-scoped public motion preserves intentional transition contracts', function (): void {
    $configuration = File::get(
        resource_path('js/section-scroll-trigger-config.js'),
    );

    $homepageMotion = File::get(
        resource_path('js/homepage-section-navigation.js'),
    );

    $aboutMotion = File::get(
        resource_path('js/about-experience.js'),
    );

    $galleryMotion = File::get(
        resource_path('js/gallery-experience.js'),
    );

    /*
     * The reusable profile owns the homepage transition values.
     */
    expect($configuration)
        ->toContain('duration: 0.48')
        ->toContain('activationThreshold: 8')
        ->toContain('gestureReleaseDelay: 140');

    /*
     * The homepage controller consumes the shared profile rather than
     * redeclaring its own timing constants.
     */
    expect($homepageMotion)
        ->toContain('homepageSectionScrollTriggerConfig')
        ->toContain('duration: navigation.duration')
        ->toContain('wheel.activationThreshold')
        ->toContain('wheel.gestureReleaseDelay')
        ->not->toContain('sectionTransitionDuration = 0.48')
        ->not->toContain('wheelActivationThreshold = 8')
        ->not->toContain('wheelGestureReleaseDelay = 140');

    /*
     * The About controller consumes the reusable profile while retaining its
     * page-specific 42px content reveal distance.
     */
    expect($configuration)
        ->toContain('aboutSectionScrollTriggerConfig')
        ->toContain('distance: 42');

    expect($aboutMotion)
        ->toContain('aboutSectionScrollTriggerConfig')
        ->toContain('duration: navigation.duration')
        ->toContain('wheel.activationThreshold')
        ->toContain('wheel.gestureReleaseDelay')
        ->toContain('...reveal.trigger')
        ->toContain('...tracking.trigger')
        ->toContain('...depth.trigger')
        ->toContain('wheel.enabled')
        ->not->toContain('sectionTransitionDuration = 0.48')
        ->not->toContain('wheelActivationThreshold = 8')
        ->not->toContain('wheelGestureReleaseDelay = 140')
        ->not->toContain('const desktopQuery')
        ->not->toContain('const reducedMotionQuery')
        ->not->toContain('const shortViewportQuery');

    /*
     * Gallery keeps native scrolling but now consumes the homepage-derived
     * media, reveal-trigger, and depth-trigger configuration.
     */
    expect($configuration)
        ->toContain('gallerySectionScrollTriggerConfig')
        ->toContain('duration: 0.85')
        ->toContain('stagger: 0.08');

    expect($galleryMotion)
        ->toContain('gsap.registerPlugin(ScrollTrigger);')
        ->toContain('gallerySectionScrollTriggerConfig')
        ->toContain('mediaQueries.desktop')
        ->toContain('mediaQueries.reducedMotion')
        ->toContain('y: reveal.distance')
        ->toContain('duration: reveal.duration')
        ->toContain('stagger: reveal.stagger')
        ->toContain('...reveal.trigger')
        ->toContain('...depth.trigger')
        ->not->toContain('const desktopQuery')
        ->not->toContain('const reducedMotionQuery')
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
        ->toContain('<x-public.home-featured-menu');

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
