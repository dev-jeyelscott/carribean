<?php

use Illuminate\Support\Facades\File;

test('homepage featured menu uses a balanced three-card layout', function (): void {
    $featuredMenu = File::get(
        resource_path(
            'views/components/public/home-featured-menu.blade.php',
        ),
    );

    expect($featuredMenu)
        ->toContain('->take(3)')
        ->toContain('xl:grid-cols-3')
        ->toContain('pt-28')
        ->not->toContain('->take(4)');
});

test('homepage hero keeps its scroll control without the visible section pager', function (): void {
    $homepageHero = File::get(
        resource_path(
            'views/components/public/homepage-hero.blade.php',
        ),
    );

    expect($homepageHero)
        /*
         * Keep the accessible control that moves visitors to the next
         * homepage section.
         */
        ->toContain('data-home-scroll-link')
        ->toContain('href="#featured"')
        ->toContain('Scroll')
        /*
         * The retired right-side text navigation must not return.
         */
        ->not->toContain('<x-public.section-pager')
        ->not->toContain('label="Homepage sections"')
        ->not->toContain('context="home"')
        ->not->toContain("'id' => 'featured'")
        ->not->toContain("'id' => 'visit'");
});

test('contact hero refresh enhances the current conversion structure', function (): void {
    $contactPage = File::get(
        resource_path('views/pages/contact.blade.php'),
    );

    $contactStyles = File::get(
        resource_path('css/contact-hero-refresh.css'),
    );

    expect($contactPage)
        ->toContain('Explore the Menu')
        ->toContain('Send an Inquiry')
        ->toContain('contact-quick-card')
        ->not->toContain('Reserve a Table')
        ->not->toContain('#reservation')
        ->not->toContain('private-event');

    expect($contactStyles)
        ->toContain('#contact-hero')
        ->toContain('[data-gsap="hero-content"]')
        ->toContain('.contact-hero-media')
        ->toContain('.contact-quick-card')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

test('customer authentication uses the branded interactive shell', function (): void {
    $layout = File::get(
        resource_path('views/layouts/auth/simple.blade.php'),
    );

    $login = File::get(
        resource_path('views/pages/auth/login.blade.php'),
    );

    $register = File::get(
        resource_path('views/pages/auth/register.blade.php'),
    );

    $styles = File::get(
        resource_path('css/auth.css'),
    );

    expect($layout)
        ->toContain('auth-visual-panel')
        ->toContain('auth-benefit-card')
        ->toContain('auth-route-switch')
        ->toContain('Secure customer account');

    expect($login)
        ->toContain('Welcome back')
        ->toContain('input:class="auth-input"')
        ->toContain('auth-submit-button');

    expect($register)
        ->toContain('Create your account')
        ->toContain('Phone number')
        ->toContain('input:class="auth-input"')
        ->toContain('auth-submit-button');

    expect($styles)
        ->toContain('.auth-page')
        ->toContain('.auth-route-switch__link')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

test('theme imports the scoped experience styles', function (): void {
    $theme = File::get(
        resource_path('css/theme.css'),
    );

    expect($theme)
        ->toContain('@import "./auth.css";')
        ->toContain(
            '@import "./contact-hero-refresh.css";',
        );
});
