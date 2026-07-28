<?php

use App\Livewire\Menu\Catalog;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Support\Cart\SessionCart;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Create a public menu category for catalogue tests.
 *
 * @param  array<string, mixed>  $overrides
 */
function createCatalogCategory(array $overrides = []): MenuCategory
{
    return MenuCategory::query()->create([
        'name' => 'Starters',
        'slug' => 'starters',
        'description' => 'Small plates made for sharing.',
        'sort_order' => 1,
        'is_visible' => true,
        ...$overrides,
    ]);
}

/**
 * Create one menu item for catalogue tests.
 *
 * @param  array<string, mixed>  $overrides
 */
function createCatalogItem(
    MenuCategory $category,
    int $position,
    array $overrides = [],
): MenuItem {
    $name = 'Menu Item '.$position;

    return MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.$position,
        'description' => null,
        'price' => '12.00',
        'price_cents' => 1200,
        'image_path' => null,
        'image_alt_text' => null,
        'sort_order' => $position,
        'is_visible' => true,
        'is_featured' => false,
        'is_available' => true,
        'is_purchasable' => true,
        'dietary_labels' => null,
        'allergen_information' => null,
        ...$overrides,
    ]);
}

it('renders the first menu-item batch for each visible category', function () {
    $category = createCatalogCategory();

    foreach (range(1, 10) as $position) {
        createCatalogItem($category, $position);
    }

    Livewire::test(Catalog::class)
        ->assertSee('Starters')
        ->assertSee('Menu Item 1')
        ->assertSee('Menu Item 8')
        ->assertDontSee('Menu Item 9')
        ->assertSee('Load more island flavors');
});

it('appends another category batch without removing loaded items', function () {
    $category = createCatalogCategory();

    foreach (range(1, 10) as $position) {
        createCatalogItem($category, $position);
    }

    Livewire::test(Catalog::class)
        ->assertSee('Menu Item 1')
        ->assertDontSee('Menu Item 9')
        ->call('loadMore', $category->id)
        ->assertSee('Menu Item 1')
        ->assertSee('Menu Item 9')
        ->assertSee('Menu Item 10');
});

it('does not render hidden categories or hidden menu items', function () {
    $visibleCategory = createCatalogCategory();

    createCatalogItem($visibleCategory, 1, [
        'name' => 'Visible Item',
        'slug' => 'visible-item',
    ]);

    createCatalogItem($visibleCategory, 2, [
        'name' => 'Hidden Item',
        'slug' => 'hidden-item',
        'is_visible' => false,
    ]);

    createCatalogCategory([
        'name' => 'Hidden Category',
        'slug' => 'hidden-category',
        'sort_order' => 2,
        'is_visible' => false,
    ]);

    Livewire::test(Catalog::class)
        ->assertSee('Visible Item')
        ->assertDontSee('Hidden Item')
        ->assertDontSee('Hidden Category');
});

it('adds a simple menu item using the selected quantity', function () {
    $category = createCatalogCategory();
    $menuItem = createCatalogItem($category, 1);

    Livewire::test(Catalog::class)
        ->call('incrementQuantity', $menuItem->id)
        ->call('addToCart', $menuItem->id)
        ->assertDispatched('cart-updated')
        ->assertDispatched('cart-notification');

    $cartLines = session()->get(
        'shopping_cart.items',
        [],
    );

    expect($cartLines)->toHaveCount(1);

    $cartLine = array_values($cartLines)[0];

    expect($cartLine)
        ->menu_item_id->toBe($menuItem->id)
        ->quantity->toBe(2);
});

it('does not add unavailable menu items', function () {
    $category = createCatalogCategory();

    $menuItem = createCatalogItem($category, 1, [
        'is_available' => false,
    ]);

    Livewire::test(Catalog::class)
        ->call('addToCart', $menuItem->id)
        ->assertDispatched('cart-notification');

    expect(app(SessionCart::class)->items())->toBe([]);
});
