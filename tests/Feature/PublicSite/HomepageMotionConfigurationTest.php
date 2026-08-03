<?php

use Illuminate\Support\Facades\File;

test('homepage section motion consumes the homepage-only ScrollTrigger profile', function (): void {
    $configuration = File::get(
        resource_path('js/section-scroll-trigger-config.js'),
    );

    $homepageNavigation = File::get(
        resource_path('js/homepage-section-navigation.js'),
    );

    $app = File::get(
        resource_path('js/app.js'),
    );

    expect($configuration)
        ->toContain(
            'Homepage-only ScrollTrigger configuration.',
        )
        ->toContain(
            'export const homepageSectionScrollTriggerConfig = {',
        )
        ->toContain(
            'start: "top 76%"',
        )
        ->toContain(
            'start: "top 52%"',
        )
        ->toContain(
            'start: "top bottom"',
        )
        ->toContain(
            'end: "bottom top"',
        )
        ->toContain(
            'scrub: true',
        )
        ->toContain(
            'enabled: true',
        )
        ->not->toContain(
            'createSectionScrollTriggerConfig',
        )
        ->not->toContain(
            'aboutSectionScrollTriggerConfig',
        )
        ->not->toContain(
            'gallerySectionScrollTriggerConfig',
        );

    expect($homepageNavigation)
        ->toContain(
            'homepageSectionScrollTriggerConfig',
        )
        ->toContain(
            '...reveal.trigger',
        )
        ->toContain(
            '...tracking.trigger',
        )
        ->toContain(
            '...depth.trigger',
        )
        ->toContain(
            'wheel.enabled',
        )
        ->not->toContain(
            'const desktopQuery',
        )
        ->not->toContain(
            'const sectionRevealDuration',
        )
        ->not->toContain(
            'const sectionRevealStagger',
        );

    expect($app)
        ->toContain(
            'import("./homepage-section-navigation")',
        )
        ->not->toContain(
            'import("./about-experience")',
        )
        ->not->toContain(
            'import("./menu-category-scroll")',
        )
        ->not->toContain(
            'import("./contact-experience")',
        )
        ->not->toContain(
            'import("./public-page-experience")',
        );
});
