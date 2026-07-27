<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    SiteSetting::query()->create([
        'key' => 'restaurant_name',
        'value' => 'Coast & Cay',
        'group' => 'general',
    ]);

    SiteSetting::query()->create([
        'key' => 'tagline',
        'value' => 'Caribbean warmth, California ease.',
        'group' => 'general',
    ]);

    Page::query()->create([
        'slug' => 'home',
        'title' => 'Island Hospitality, Made for the California Coast',
        'excerpt' => 'A warm gathering place.',
        'content' => 'Caribbean roots and California ease.',
        'meta_title' => 'Coast & Cay',
        'meta_description' => 'Caribbean restaurant.',
        'is_published' => true,
    ]);

    Page::query()->create([
        'slug' => 'about',
        'title' => 'Caribbean Roots, California Rhythm',
        'excerpt' => 'Our restaurant story.',
        'content' => '<p>A restaurant made for gathering.</p>',
        'meta_title' => 'About Coast & Cay',
        'meta_description' => 'About Coast & Cay.',
        'is_published' => true,
    ]);

    Page::query()->create([
        'slug' => 'menu',
        'title' => 'Island Favorites, Made to Gather Around',
        'excerpt' => 'Explore the menu.',
        'content' => 'Food made with warmth.',
        'meta_title' => 'Menu',
        'meta_description' => 'Current menu.',
        'is_published' => true,
    ]);
});

test('the refreshed homepage exposes the approved public navigation', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Coast &amp; Cay', false)
        ->assertSee('About');
});

test('the published about page renders through the public cms shell', function (): void {
    $response = $this->get(route('about'));

    $response
        ->assertOk()
        ->assertSee('Caribbean Roots, California Rhythm')
        ->assertSee('A restaurant made for gathering.');
});

test('the menu displays authoritative cent prices in us dollars', function (): void {
    $category = MenuCategory::query()->create([
        'name' => 'Main Courses',
        'slug' => 'main-courses',
        'description' => 'Generous plates.',
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => 'Jerk Chicken',
        'slug' => 'jerk-chicken',
        'description' => 'Jerk-spiced chicken.',
        'price_cents' => 1850,
        'sort_order' => 1,
        'is_visible' => true,
        'is_available' => true,
        'is_purchasable' => true,
    ]);

    $response = $this->get(route('menu'));

    $response
        ->assertOk()
        ->assertSee('$18.50')
        ->assertDontSee('₱18.50');
});

test('an unpublished about page is not publicly accessible', function (): void {
    Page::query()
        ->where('slug', 'about')
        ->update(['is_published' => false]);

    $this->get(route('about'))
        ->assertNotFound();
});
