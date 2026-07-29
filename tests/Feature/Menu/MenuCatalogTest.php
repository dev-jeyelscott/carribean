<?php

use App\Livewire\Menu\Catalog;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Create one category for catalogue rendering tests.
 *
 * @param  array<string, mixed>  $overrides
 */
function createMenuCatalogCategory(
    array $overrides = [],
): MenuCategory {
    return MenuCategory::query()->create([
        'name' => 'Small Plates',
        'slug' => 'small-plates',
        'description' => 'Caribbean starters designed for sharing.',
        'sort_order' => 1,
        'is_visible' => true,
        ...$overrides,
    ]);
}

/**
 * Create one public menu item for catalogue rendering tests.
 *
 * @param  array<string, mixed>  $overrides
 */
function createMenuCatalogItem(
    MenuCategory $category,
    int $position,
    array $overrides = [],
): MenuItem {
    $name = 'Catalogue Item '.$position;

    return MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.$position,
        'description' => 'A Caribbean-inspired menu item.',
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

it('renders every visible menu item for each category', function () {
    $category = createMenuCatalogCategory();

    foreach (range(1, 10) as $position) {
        createMenuCatalogItem(
            $category,
            $position,
        );
    }

    Livewire::test(Catalog::class)
        ->assertSee('Small Plates')
        ->assertSee('Catalogue Item 1')
        ->assertSee('Catalogue Item 8')
        ->assertSee('Catalogue Item 9')
        ->assertSee('Catalogue Item 10')
        ->assertDontSee('Load more island flavors');
});

it('does not render hidden categories or hidden menu items', function () {
    $visibleCategory = createMenuCatalogCategory();

    createMenuCatalogItem(
        $visibleCategory,
        1,
        [
            'name' => 'Visible Item',
            'slug' => 'visible-item',
        ],
    );

    createMenuCatalogItem(
        $visibleCategory,
        2,
        [
            'name' => 'Hidden Item',
            'slug' => 'hidden-item',
            'is_visible' => false,
        ],
    );

    $hiddenCategory = createMenuCatalogCategory([
        'name' => 'Hidden Category',
        'slug' => 'hidden-category',
        'sort_order' => 2,
        'is_visible' => false,
    ]);

    createMenuCatalogItem(
        $hiddenCategory,
        1,
        [
            'name' => 'Hidden Category Item',
            'slug' => 'hidden-category-item',
        ],
    );

    Livewire::test(Catalog::class)
        ->assertSee('Visible Item')
        ->assertDontSee('Hidden Item')
        ->assertDontSee('Hidden Category')
        ->assertDontSee('Hidden Category Item');
});

it('renders reusable modal triggers with detail-page fallbacks', function () {
    $category = createMenuCatalogCategory();

    $menuItem = createMenuCatalogItem(
        $category,
        1,
        [
            'name' => 'Jerk Chicken Spring Rolls',
            'slug' => 'jerk-chicken-spring-rolls',
        ],
    );

    Livewire::test(Catalog::class)
        ->assertSee('Jerk Chicken Spring Rolls')
        ->assertSeeHtml('data-product-modal-trigger')
        ->assertSeeHtml(
            route('menu-items.show', $menuItem),
        );
});

it('renders the authoritative visible item count', function () {
    $category = createMenuCatalogCategory();

    foreach (range(1, 5) as $position) {
        createMenuCatalogItem(
            $category,
            $position,
        );
    }

    createMenuCatalogItem(
        $category,
        6,
        [
            'is_visible' => false,
        ],
    );

    Livewire::test(Catalog::class)
        ->assertViewHas(
            'categories',
            function ($categories): bool {
                $category = $categories->first();

                return $category !== null
                    && (int) $category->getAttribute(
                        'visible_menu_items_count',
                    ) === 5;
            },
        )
        ->assertSee('1–4 of 5');
});
