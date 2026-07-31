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
        ->assertSee('data-menu-page', false)
        ->assertSee('id="menu-catalog"', false)
        ->assertSee('aria-label="Menu categories"', false)
        ->assertSee(
            'href="#category-island-favorites"',
            false,
        )
        ->assertSee(
            'id="category-island-favorites"',
            false,
        )
        ->assertSeeText('Island Favorites')
        ->assertSeeText('Island Jerk Chicken')
        ->assertSeeText('Customize order')
        ->assertDontSee('data-menu-hero', false)
        ->assertDontSeeText('Submit Order Inquiry')
        ->assertDontSeeText('Request a table');
});

test('menu page preserves progressive motion and native content', function (): void {
    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('data-menu-page', false)
        ->assertSee('data-menu-catalog', false)
        ->assertSee('data-menu-mobile-categories', false)
        ->assertSee('data-menu-sidebar', false)
        ->assertSee('data-menu-category-link', false)
        ->assertSee('data-menu-section', false)
        ->assertSee('data-menu-section-heading', false)
        ->assertSee('data-menu-section-rule', false)
        ->assertSee('data-menu-carousel', false)
        ->assertSee('data-menu-carousel-track', false)
        ->assertSee('data-menu-closing', false)
        ->assertSeeText('Review Your Cart')
        ->assertDontSee('data-menu-hero-item', false);
});
