<?php

/**
 * Read one repository file without booting Laravel or connecting to MySQL.
 */
function readGalleryAssetFile(string $relativePath): string
{
    $absolutePath = dirname(__DIR__, 2)
        .DIRECTORY_SEPARATOR
        .str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $relativePath,
        );

    $contents = file_get_contents($absolutePath);

    if ($contents === false) {
        throw new RuntimeException(
            "Unable to read [{$relativePath}].",
        );
    }

    return $contents;
}

test('gallery has a dedicated native-interactions Vite entry', function (): void {
    $viteConfig = readGalleryAssetFile(
        'vite.config.js',
    );

    $layout = readGalleryAssetFile(
        'resources/views/components/layouts/public.blade.php',
    );

    $galleryEntry = readGalleryAssetFile(
        'resources/js/gallery-page.js',
    );

    expect($viteConfig)
        ->toContain(
            '"resources/js/gallery-page.js"',
        );

    expect($layout)
        ->toContain(
            "request()->routeIs('gallery')",
        )
        ->toContain(
            "\$viteEntries[] = 'resources/js/gallery-page.js';",
        );

    expect($galleryEntry)
        ->toContain(
            'import { initGalleryInteractions }',
        )
        ->toContain(
            'initGalleryInteractions(galleryRoot)',
        )
        ->toContain(
            'cleanupGalleryInteractions',
        )
        ->toContain(
            '"livewire:navigated"',
        )
        ->not->toContain(
            'initGalleryExperience',
        )
        ->not->toContain(
            'gsap/ScrollTrigger',
        );
});

test('shared application entry does not initialize gallery', function (): void {
    $app = readGalleryAssetFile(
        'resources/js/app.js',
    );

    expect($app)
        ->not->toContain(
            'import("./gallery-experience")',
        )
        ->not->toContain(
            'import("./gallery-interactions")',
        )
        ->not->toContain(
            'const galleryPageRoot',
        );
});
