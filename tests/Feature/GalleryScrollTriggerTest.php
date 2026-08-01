<?php

use Illuminate\Support\Facades\File;

test('gallery blade exposes the complete ScrollTrigger contract', function (): void {
    $gallery = File::get(
        resource_path('views/pages/gallery.blade.php'),
    );

    expect($gallery)
        ->toContain('data-gallery-page')
        ->toContain('data-gallery-motion-state="loading"')
        ->toContain('data-gallery-active-section="gallery-hero"')
        ->toContain('data-gallery-panel')
        ->toContain('data-gallery-section')
        ->toContain('data-gallery-label="Introduction"')
        ->toContain('data-gallery-label="Our Story"')
        ->toContain('data-gallery-label="Collection"')
        ->toContain('data-gallery-label="Visit Coast & Cay"')
        ->toContain('data-gallery-reveal')
        ->toContain('data-gallery-depth');
});

test('gallery motion consumes the shared ScrollTrigger configuration', function (): void {
    $configuration = File::get(
        resource_path('js/section-scroll-trigger-config.js'),
    );

    $motion = File::get(
        resource_path('js/gallery-experience.js'),
    );

    expect($configuration)
        ->toContain('motionAllowed: "(prefers-reduced-motion: no-preference)"')
        ->toContain('gallerySectionScrollTriggerConfig')
        ->toContain('distance: 42')
        ->toContain('duration: 0.85');

    expect($motion)
        ->toContain('gallerySectionScrollTriggerConfig')
        ->toContain('mediaQueries.motionAllowed')
        ->toContain('mediaQueries.reducedMotion')
        ->toContain('...reveal.trigger')
        ->toContain('...tracking.trigger')
        ->toContain('...depth.trigger')
        ->toContain('ScrollTrigger.refresh()')
        ->toContain('gsap.matchMedia()')
        ->toContain('matchMedia')
        ->not->toContain('const desktopQuery')
        ->not->toContain('const reducedMotionQuery');
});
