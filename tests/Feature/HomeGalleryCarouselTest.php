<?php

use Illuminate\Support\Facades\File;

test('homepage motion keeps reduced-motion and lifecycle contracts', function (): void {
    $motion = File::get(resource_path('js/public-animations.js'));

    expect($motion)
        ->toContain('gsap.matchMedia()')
        ->toContain('reducedMotion: "(prefers-reduced-motion: reduce)"')
        ->toContain('const cleanup = () => media.revert()')
        ->toContain('import.meta.hot.dispose(cleanup)')
        ->not->toContain('initHomeGalleryCarousel');
});

test('homepage motion is initialized through the shared public animation runtime', function (): void {
    $entry = file_get_contents(resource_path('js/app.js'));

    expect($entry)
        ->not->toBeFalse()
        ->toContain('import("./public-animations")')
        ->toContain('initPublicAnimations()')
        ->not->toContain('from "gsap"');
});

test('homepage gallery uses the editorial grid without retired carousel assets', function (): void {
    $homepage = File::get(resource_path('views/pages/home.blade.php'));
    $styles = file_get_contents(resource_path('css/public.css'));

    expect($homepage)
        ->toContain('lg:grid-cols-[0.85fr_1.7fr_0.85fr]')
        ->toContain('<x-public.gallery-tile')
        ->not->toContain('data-home-gallery');

    expect($styles)
        ->not->toBeFalse()
        ->not->toContain('[data-home-gallery-enhanced]')
        ->not->toContain('.home-gallery-controls');
});
