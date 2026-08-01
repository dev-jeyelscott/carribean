<?php

/**
 * Read a project file without booting the Laravel application.
 */
function readGalleryProjectFile(string $relativePath): string
{
    $projectRoot = dirname(__DIR__, 2);

    $absolutePath = $projectRoot
        .DIRECTORY_SEPARATOR
        .str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $relativePath,
        );

    $contents = file_get_contents($absolutePath);

    if ($contents === false) {
        throw new RuntimeException(
            "Unable to read project file [{$relativePath}].",
        );
    }

    return $contents;
}

test('gallery blade exposes the complete ScrollTrigger contract', function (): void {
    $gallery = readGalleryProjectFile(
        'resources/views/pages/gallery.blade.php',
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
    $configuration = readGalleryProjectFile(
        'resources/js/section-scroll-trigger-config.js',
    );

    $motion = readGalleryProjectFile(
        'resources/js/gallery-experience.js',
    );

    expect($configuration)
        ->toContain(
            'motionAllowed: "(prefers-reduced-motion: no-preference)"',
        )
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
