<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOptionGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

/**
 * Create a visible menu category for catalogue tests.
 */
function createCatalogueCategory(
    array $attributes = [],
): MenuCategory {
    return MenuCategory::query()->create([
        'name' => 'Main Courses',
        'slug' => 'main-courses',
        'description' => 'Generous plates.',
        'sort_order' => 1,
        'is_visible' => true,
        ...$attributes,
    ]);
}

/**
 * Create a menu item for catalogue tests.
 */
function createCatalogueItem(
    MenuCategory $category,
    array $attributes = [],
): MenuItem {
    return MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => 'Jerk Chicken',
        'slug' => 'jerk-chicken',
        'description' => 'Jerk-spiced chicken with island herbs.',
        'price_cents' => 1850,
        'sort_order' => 1,
        'is_visible' => true,
        'is_featured' => false,
        'is_available' => true,
        'is_purchasable' => true,
        ...$attributes,
    ]);
}

test('legacy decimal price writes synchronize integer cents', function (): void {
    $category = createCatalogueCategory();

    $item = MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => 'Legacy Price Item',
        'slug' => 'legacy-price-item',
        'price' => '18.50',
        'sort_order' => 1,
        'is_visible' => true,
        'is_available' => true,
        'is_purchasable' => true,
    ]);

    expect($item->refresh()->price_cents)->toBe(1850)
        ->and($item->formattedPrice())->toBe('$18.50');
});

test('cent price writes keep the rollback column synchronized', function (): void {
    $category = createCatalogueCategory();

    $item = createCatalogueItem($category);

    expect($item->refresh()->price)->toBe('18.50')
        ->and($item->price_cents)->toBe(1850);
});

test('a visible menu item has a public detail page', function (): void {
    $category = createCatalogueCategory();

    $item = createCatalogueItem($category, [
        'dietary_labels' => [
            'Gluten-aware',
        ],
        'allergen_information' => 'Contains soy.',
    ]);

    $group = $item->optionGroups()->create([
        'name' => 'Side',
        'is_required' => true,
        'minimum_selections' => 1,
        'maximum_selections' => 1,
        'sort_order' => 1,
    ]);

    $group->options()->create([
        'name' => 'Rice and Peas',
        'additional_price_cents' => 0,
        'is_available' => true,
        'sort_order' => 1,
    ]);

    $group->options()->create([
        'name' => 'Plantain',
        'additional_price_cents' => 200,
        'is_available' => true,
        'sort_order' => 2,
    ]);

    $group->options()->create([
        'name' => 'Unavailable Side',
        'additional_price_cents' => 300,
        'is_available' => false,
        'sort_order' => 3,
    ]);

    $this->get(route('menu-items.show', $item))
        ->assertOk()
        ->assertSee('Jerk Chicken')
        ->assertSee('$18.50')
        ->assertSee('Gluten-aware')
        ->assertSee('Contains soy.')
        ->assertSee('Rice and Peas')
        ->assertSee('Plantain')
        ->assertSee('$2.00')
        ->assertDontSee('Unavailable Side');
});

test('a visible but unavailable item remains viewable', function (): void {
    $category = createCatalogueCategory();

    $item = createCatalogueItem($category, [
        'is_available' => false,
    ]);

    $this->get(route('menu-items.show', $item))
        ->assertOk()
        ->assertSee('currently unavailable');
});

test('a hidden item is not publicly accessible', function (): void {
    $category = createCatalogueCategory();

    $item = createCatalogueItem($category, [
        'is_visible' => false,
    ]);

    $this->get(route('menu-items.show', $item))
        ->assertNotFound();
});

test('an item inside a hidden category is not publicly accessible', function (): void {
    $category = createCatalogueCategory([
        'is_visible' => false,
    ]);

    $item = createCatalogueItem($category);

    $this->get(route('menu-items.show', $item))
        ->assertNotFound();
});

test('required groups must require at least one selection', function (): void {
    $category = createCatalogueCategory();
    $item = createCatalogueItem($category);

    MenuItemOptionGroup::query()->create([
        'menu_item_id' => $item->id,
        'name' => 'Spice Level',
        'is_required' => true,
        'minimum_selections' => 0,
        'maximum_selections' => 1,
        'sort_order' => 1,
    ]);
})->throws(
    ValidationException::class,
);

test('maximum selections cannot be lower than minimum selections', function (): void {
    $category = createCatalogueCategory();
    $item = createCatalogueItem($category);

    MenuItemOptionGroup::query()->create([
        'menu_item_id' => $item->id,
        'name' => 'Sides',
        'is_required' => true,
        'minimum_selections' => 2,
        'maximum_selections' => 1,
        'sort_order' => 1,
    ]);
})->throws(
    ValidationException::class,
);

test('deleting a menu item deletes its option definitions', function (): void {
    $category = createCatalogueCategory();
    $item = createCatalogueItem($category);

    $group = $item->optionGroups()->create([
        'name' => 'Spice Level',
        'is_required' => true,
        'minimum_selections' => 1,
        'maximum_selections' => 1,
        'sort_order' => 1,
    ]);

    $option = $group->options()->create([
        'name' => 'Hot',
        'additional_price_cents' => 0,
        'is_available' => true,
        'sort_order' => 1,
    ]);

    $item->delete();

    $this->assertDatabaseMissing(
        'menu_item_option_groups',
        [
            'id' => $group->id,
        ],
    );

    $this->assertDatabaseMissing(
        'menu_item_options',
        [
            'id' => $option->id,
        ],
    );
});
