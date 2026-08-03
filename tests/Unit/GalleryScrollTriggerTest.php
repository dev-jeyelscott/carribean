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

test('gallery interactions support loading and accessible navigation without ScrollTrigger', function (): void {
    $configuration = readGalleryProjectFile(
        'resources/js/section-scroll-trigger-config.js',
    );

    $interactions = readGalleryProjectFile(
        'resources/js/gallery-interactions.js',
    );

    $galleryEntry = readGalleryProjectFile(
        'resources/js/gallery-page.js',
    );

    expect($configuration)
        ->toContain(
            'homepageSectionScrollTriggerConfig',
        )
        ->not->toContain(
            'gallerySectionScrollTriggerConfig',
        )
        ->not->toContain(
            'aboutSectionScrollTriggerConfig',
        );

    expect($interactions)
        ->toContain(
            'export function initGalleryInteractions',
        )
        ->toContain(
            'initializeThumbnailState',
        )
        ->toContain(
            'initializeLoadMore',
        )
        ->toContain(
            'initializeDialog',
        )
        ->toContain(
            'dialog.showModal()',
        )
        ->toContain(
            '"ArrowLeft"',
        )
        ->toContain(
            '"ArrowRight"',
        )
        ->toContain(
            '"pointerdown"',
        )
        ->toContain(
            '"pointerup"',
        )
        ->toContain(
            'activeOpener.focus',
        )
        ->toContain(
            'Accept: "application/json"',
        )
        ->not->toContain(
            'gsap/ScrollTrigger',
        )
        ->not->toContain(
            'ScrollTrigger.',
        );

    expect($galleryEntry)
        ->toContain(
            'initGalleryInteractions',
        )
        ->not->toContain(
            'initGalleryExperience',
        )
        ->not->toContain(
            'gsap/ScrollTrigger',
        );
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
        ->toContain(
            '@media (prefers-reduced-motion: reduce)',
        )
        ->toContain(
            'backdrop-filter: blur(',
        );
});

test('gallery uses the transparent header and green semantic tokens', function (): void {
    $gallery = readGalleryProjectFile(
        'resources/views/pages/gallery.blade.php',
    );

    $styles = readGalleryProjectFile(
        'resources/css/gallery.css',
    );

    expect($gallery)
        ->toContain(':header-overlay="true"')
        ->toContain(':header-transparent="true"')
        ->not->toContain(':header-overlay="false"')
        ->not->toContain(':header-transparent="false"');

    expect($styles)
        ->toContain('Gallery green token surface')
        ->toContain(
            'background: var(--color-primary-deep);',
        )
        ->toContain('var(--color-primary-soft)')
        ->toContain('var(--color-ocean)')
        ->toContain('var(--color-canvas)')
        ->toContain('var(--color-sun)')
        ->toContain('var(--color-coral)');
});
