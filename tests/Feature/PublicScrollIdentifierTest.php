<?php

use App\Models\Page;

beforeEach(function (): void {
    Page::factory()->create([
        'slug' => 'about',
        'title' => 'About Coast & Cay',
        'is_published' => true,
    ]);

    Page::factory()->create([
        'slug' => 'contact',
        'title' => 'Contact Coast & Cay',
        'is_published' => true,
    ]);
});

it('renders the shared scroll identifier on immersive public pages')
    ->with([
        'menu' => 'menu',
        'about' => 'about',
        'gallery' => 'gallery',
        'journal' => 'blog.index',
        'contact' => 'contact.create',
    ])
    ->test(function (string $routeName): void {
        $this->get(route($routeName))
            ->assertSuccessful()
            ->assertSee('data-public-scroll-identifier', false);
    });

it('does not render the shared scroll identifier on the homepage', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('data-public-scroll-identifier', false);
});
