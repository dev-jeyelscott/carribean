<?php

use Illuminate\Support\Facades\File;

test('homepage section motion consumes the reusable ScrollTrigger profile', function (): void {
    $configuration = File::get(
        resource_path('js/section-scroll-trigger-config.js'),
    );

    $homepageNavigation = File::get(
        resource_path('js/homepage-section-navigation.js'),
    );

    $app = File::get(resource_path('js/app.js'));

    expect($configuration)
        ->toContain('createSectionScrollTriggerConfig')
        ->toContain('homepageSectionScrollTriggerConfig')
        ->toContain('start: "top 76%"')
        ->toContain('start: "top 52%"')
        ->toContain('scrub: true')
        ->toContain('enabled: false')
        ->toContain('enabled: true');

    expect($homepageNavigation)
        ->toContain('homepageSectionScrollTriggerConfig')
        ->toContain('...reveal.trigger')
        ->toContain('...tracking.trigger')
        ->toContain('...depth.trigger')
        ->toContain('wheel.enabled')
        ->not->toContain('const desktopQuery')
        ->not->toContain('const sectionRevealDuration')
        ->not->toContain('const sectionRevealStagger');

    expect($app)
        ->toContain('import("./homepage-section-navigation")');
});
