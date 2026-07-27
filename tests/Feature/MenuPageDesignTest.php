<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;

beforeEach(function (): void {
    $category = MenuCategory::query()->create([
        'name' => 'Island Favorites',
        'slug' => 'island-favorites',
        'description' => 'Caribbean dishes made for sharing.',
        'sort_order' => 10,
        'is_visible' => true,
    ]);

    MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => 'Island Jerk Chicken',
        'slug' => 'island-jerk-chicken',
        'description' => 'Flame-grilled jerk chicken with rice and peas.',
        'price_cents' => 2400,
        'sort_order' => 10,
        'is_visible' => true,
        'is_available' => true,
        'is_purchasable' => true,
    ]);
});

test('menu page presents visible orderable items in the Coast and Cay layout', function (): void {
    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('id="menu-selections"', false)
        ->assertSee('aria-label="Menu categories"', false)
        ->assertSee('href="#category-island-favorites"', false)
        ->assertSee('id="category-island-favorites"', false)
        ->assertSeeTextInOrder([
            'The Coast & Cay Menu',
            'Island Favorites',
            'Island Jerk Chicken',
            'Order Online',
        ])
        ->assertDontSeeText('Submit Order Inquiry')
        ->assertDontSeeText('Request a table');
});

test('menu page preserves progressive motion and native content', function (): void {
    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('data-home-motion data-public-motion="menu"', false)
        ->assertSee('data-menu-motion="hero"', false)
        ->assertSee('data-menu-motion="category-nav"', false)
        ->assertSee('data-menu-category-link', false)
        ->assertSee('data-menu-motion="closing-cta"', false)
        ->assertSeeText('Order online for pickup or local delivery');
});
