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

test('gallery blade exposes one collage section and one fullscreen viewer', function (): void {
    $gallery = readGalleryProjectFile(
        'resources/views/pages/gallery.blade.php',
    );

    expect(substr_count($gallery, '<section'))
        ->toBe(1);

    expect($gallery)
        ->toContain('data-gallery-page')
        ->toContain('data-gallery-section')
        ->toContain('data-gallery-grid')
        ->toContain('data-gallery-load-more')
        ->toContain('data-gallery-dialog')
        ->toContain('data-gallery-previous')
        ->toContain('data-gallery-next')
        ->not->toContain('id="gallery-hero"')
        ->not->toContain('id="gallery-signature"')
        ->not->toContain('id="gallery-invitation"')
        ->not->toContain('gallery-film-index');
});

test('gallery card exposes landscape portrait square and feature layouts', function (): void {
    $items = readGalleryProjectFile(
        'resources/views/partials/public/gallery-items.blade.php',
    );

    $card = readGalleryProjectFile(
        'resources/views/components/public/gallery-card.blade.php',
    );

    expect($items)
        ->toContain("'feature'")
        ->toContain("'portrait'")
        ->toContain("'square'")
        ->toContain("'landscape'")
        ->toContain('variant="collage"');

    expect($card)
        ->toContain('$isCollage')
        ->toContain('data-gallery-layout="{{ $layout }}"')
        ->toContain('data-gallery-open')
        ->toContain('data-gallery-srcset');
});

test('gallery motion supports progressive loading and accessible navigation', function (): void {
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
        ->toContain(
            'reducedMotion: "(prefers-reduced-motion: reduce)"',
        )
        ->toContain('gallerySectionScrollTriggerConfig');

    expect($motion)
        ->toContain('gallerySectionScrollTriggerConfig')
        ->toContain('gsap.context(')
        ->toContain('gsap.matchMedia()')
        ->toContain('ScrollTrigger.batch(')
        ->toContain('ScrollTrigger.refresh()')
        ->toContain('mediaQueries.motionAllowed')
        ->toContain('mediaQueries.reducedMotion')
        ->toContain('dialog.showModal()')
        ->toContain('"ArrowLeft"')
        ->toContain('"ArrowRight"')
        ->toContain('"pointerdown"')
        ->toContain('"pointerup"')
        ->toContain('activeOpener?.focus')
        ->toContain('Accept: "application/json"');
});

test('gallery styles define all collage shapes and reduced motion', function (): void {
    $styles = readGalleryProjectFile(
        'resources/css/gallery.css',
    );

    expect($styles)
        ->toContain(
            '.gallery-collage-card[data-gallery-layout="feature"]',
        )
        ->toContain(
            '.gallery-collage-card[data-gallery-layout="landscape"]',
        )
        ->toContain(
            '.gallery-collage-card[data-gallery-layout="portrait"]',
        )
        ->toContain(
            '.gallery-collage-card[data-gallery-layout="square"]',
        )
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->toContain('backdrop-filter: blur(');
});
