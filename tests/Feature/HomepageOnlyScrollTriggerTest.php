<?php

use Illuminate\Support\Facades\File;

/**
 * Return normalized JavaScript paths that import the ScrollTrigger plugin.
 *
 * @return array<int, string>
 */
function scrollTriggerImportPaths(): array
{
    return collect(File::allFiles(resource_path('js')))
        ->filter(
            fn (SplFileInfo $file): bool => $file->getExtension() === 'js',
        )
        ->filter(
            fn (SplFileInfo $file): bool => str_contains(
                File::get($file->getPathname()),
                'gsap/ScrollTrigger',
            ),
        )
        ->map(
            fn (SplFileInfo $file): string => str_replace(
                '\\',
                '/',
                $file->getPathname(),
            ),
        )
        ->sort()
        ->values()
        ->all();
}

test('ScrollTrigger imports are isolated to homepage modules', function (): void {
    $allowedPaths = collect([
        resource_path('js/homepage-section-navigation.js'),
        resource_path('js/public-animations.js'),
    ])
        ->map(fn (string $path): string => str_replace('\\', '/', $path))
        ->sort()
        ->values()
        ->all();

    expect(scrollTriggerImportPaths())->toBe($allowedPaths);
});

test('shared bootstrap only loads ScrollTrigger modules for the homepage', function (): void {
    $app = File::get(resource_path('js/app.js'));

    expect($app)
        ->toContain(
            '[data-home-motion][data-home-section-pager]',
        )
        ->toContain('import("./public-animations")')
        ->toContain('import("./homepage-section-navigation")')
        ->toContain('import("./menu-experience")')
        ->not->toContain('import("./about-experience")')
        ->not->toContain('import("./menu-category-scroll")')
        ->not->toContain('import("./contact-experience")')
        ->not->toContain('import("./public-page-experience")');
});

test('menu and gallery interactions do not depend on ScrollTrigger', function (): void {
    $menu = File::get(resource_path('js/menu-experience.js'));
    $gallery = File::get(resource_path('js/gallery-interactions.js'));
    $galleryEntry = File::get(resource_path('js/gallery-page.js'));

    expect($menu)
        ->toContain('IntersectionObserver')
        ->toContain('scrollToCategory')
        ->toContain('import gsap from "gsap"')
        ->not->toContain('gsap/ScrollTrigger')
        ->not->toContain('ScrollTrigger')
        ->not->toContain('gsap/ScrollToPlugin');

    expect($gallery)
        ->toContain('initGalleryInteractions')
        ->toContain('initializeLoadMore')
        ->toContain('initializeDialog')
        ->not->toContain('gsap')
        ->not->toContain('ScrollTrigger');

    expect($galleryEntry)
        ->toContain('initGalleryInteractions')
        ->not->toContain('ScrollTrigger');
});

test('section motion configuration exposes homepage only', function (): void {
    $configuration = File::get(
        resource_path('js/section-scroll-trigger-config.js'),
    );

    expect($configuration)
        ->toContain('homepageSectionScrollTriggerConfig')
        ->not->toContain('aboutSectionScrollTriggerConfig')
        ->not->toContain('gallerySectionScrollTriggerConfig');
});
