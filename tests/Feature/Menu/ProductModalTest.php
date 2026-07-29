<?php

use App\Livewire\Menu\ProductModal;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\MenuItemOptionGroup;
use App\Support\Cart\SessionCart;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Create one category for product-modal tests.
 */
function createProductModalCategory(): MenuCategory
{
    return MenuCategory::query()->create([
        'name' => 'Signature Entrées',
        'slug' => 'signature-entrees',
        'description' => 'Polished Caribbean classics.',
        'sort_order' => 1,
        'is_visible' => true,
    ]);
}

/**
 * Create one menu item for product-modal tests.
 *
 * @param  array<string, mixed>  $overrides
 */
function createProductModalItem(
    MenuCategory $category,
    array $overrides = [],
): MenuItem {
    $name = $overrides['name']
        ?? 'Island Jerk Chicken';

    return MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => $name,
        'slug' => $overrides['slug']
            ?? Str::slug($name).'-'.Str::lower(Str::random(5)),
        'description' => 'Jerk chicken with warm island seasoning.',
        'price' => '24.00',
        'price_cents' => 2400,
        'image_path' => null,
        'image_alt_text' => null,
        'sort_order' => 1,
        'is_visible' => true,
        'is_featured' => false,
        'is_available' => true,
        'is_purchasable' => true,
        'dietary_labels' => ['Gluten conscious'],
        'allergen_information' => 'Prepared in a shared kitchen.',
        ...$overrides,
    ]);
}

/**
 * Create required and optional option groups for one test item.
 *
 * @return array{
 *     spice_group: MenuItemOptionGroup,
 *     mild: MenuItemOption,
 *     extras_group: MenuItemOptionGroup,
 *     avocado: MenuItemOption
 * }
 */
function createProductModalOptions(
    MenuItem $menuItem,
): array {
    $spiceGroup = $menuItem->optionGroups()->create([
        'name' => 'Spice Level',
        'is_required' => true,
        'minimum_selections' => 1,
        'maximum_selections' => 1,
        'sort_order' => 1,
    ]);

    $mild = $spiceGroup->options()->create([
        'name' => 'Mild',
        'additional_price_cents' => 0,
        'is_available' => true,
        'sort_order' => 1,
    ]);

    $spiceGroup->options()->create([
        'name' => 'Hot',
        'additional_price_cents' => 0,
        'is_available' => true,
        'sort_order' => 2,
    ]);

    $extrasGroup = $menuItem->optionGroups()->create([
        'name' => 'Extras',
        'is_required' => false,
        'minimum_selections' => 0,
        'maximum_selections' => 2,
        'sort_order' => 2,
    ]);

    $avocado = $extrasGroup->options()->create([
        'name' => 'Avocado',
        'additional_price_cents' => 200,
        'is_available' => true,
        'sort_order' => 1,
    ]);

    $extrasGroup->options()->create([
        'name' => 'Extra Chicken',
        'additional_price_cents' => 500,
        'is_available' => true,
        'sort_order' => 2,
    ]);

    return [
        'spice_group' => $spiceGroup,
        'mild' => $mild,
        'extras_group' => $extrasGroup,
        'avocado' => $avocado,
    ];
}

it('opens a visible item with its available option groups', function () {
    $category = createProductModalCategory();
    $menuItem = createProductModalItem($category);
    $options = createProductModalOptions($menuItem);

    Livewire::test(ProductModal::class)
        ->dispatch(
            'open-product-modal',
            menuItemId: $menuItem->id,
        )
        ->assertSet('menuItemId', $menuItem->id)
        ->assertSet(
            'selections.'.$options['spice_group']->id,
            null,
        )
        ->assertSet(
            'selections.'.$options['extras_group']->id,
            [],
        )
        ->assertSee('Island Jerk Chicken')
        ->assertSee('Spice Level')
        ->assertSee('Extras')
        ->assertSee('Avocado')
        ->assertDispatched('product-modal-open');
});

it('updates the displayed total using options and quantity', function () {
    $category = createProductModalCategory();
    $menuItem = createProductModalItem($category);
    $options = createProductModalOptions($menuItem);

    Livewire::test(ProductModal::class)
        ->dispatch(
            'open-product-modal',
            menuItemId: $menuItem->id,
        )
        ->set(
            'selections.'.$options['extras_group']->id,
            [(string) $options['avocado']->id],
        )
        ->set('quantity', 2)
        ->assertSee('$52.00');
});

it('adds a valid configured item to the session cart', function () {
    $category = createProductModalCategory();
    $menuItem = createProductModalItem($category);
    $options = createProductModalOptions($menuItem);

    Livewire::test(ProductModal::class)
        ->dispatch(
            'open-product-modal',
            menuItemId: $menuItem->id,
        )
        ->set(
            'selections.'.$options['spice_group']->id,
            (string) $options['mild']->id,
        )
        ->set(
            'selections.'.$options['extras_group']->id,
            [(string) $options['avocado']->id],
        )
        ->set('quantity', 2)
        ->call('addToCart')
        ->assertHasNoErrors()
        ->assertDispatched('cart-updated')
        ->assertDispatched('product-modal-close')
        ->assertDispatched('cart-notification');

    $cartItems = app(SessionCart::class)->items();

    expect($cartItems)->toHaveCount(1);

    $cartLine = array_values($cartItems)[0];

    expect($cartLine)
        ->menu_item_id->toBe($menuItem->id)
        ->quantity->toBe(2)
        ->option_ids->toBe([
            $options['mild']->id,
            $options['avocado']->id,
        ]);
});

it('rejects a missing required option', function () {
    $category = createProductModalCategory();
    $menuItem = createProductModalItem($category);
    createProductModalOptions($menuItem);

    Livewire::test(ProductModal::class)
        ->dispatch(
            'open-product-modal',
            menuItemId: $menuItem->id,
        )
        ->call('addToCart')
        ->assertHasErrors('selections')
        ->assertDispatched('product-modal-error')
        ->assertDispatched('cart-notification');

    expect(app(SessionCart::class)->items())
        ->toBe([]);
});

it('rejects an option belonging to another item', function () {
    $category = createProductModalCategory();

    $menuItem = createProductModalItem($category);
    $options = createProductModalOptions($menuItem);

    $otherItem = createProductModalItem(
        $category,
        [
            'name' => 'Other Item',
            'slug' => 'other-item',
            'sort_order' => 2,
        ],
    );

    $otherOptions = createProductModalOptions($otherItem);

    Livewire::test(ProductModal::class)
        ->dispatch(
            'open-product-modal',
            menuItemId: $menuItem->id,
        )
        ->set(
            'selections.'.$options['spice_group']->id,
            (string) $otherOptions['mild']->id,
        )
        ->call('addToCart')
        ->assertHasErrors('selections')
        ->assertDispatched('product-modal-error');

    expect(app(SessionCart::class)->items())
        ->toBe([]);
});

it('does not add an unavailable item', function () {
    $category = createProductModalCategory();

    $menuItem = createProductModalItem(
        $category,
        [
            'is_available' => false,
        ],
    );

    Livewire::test(ProductModal::class)
        ->dispatch(
            'open-product-modal',
            menuItemId: $menuItem->id,
        )
        ->call('addToCart')
        ->assertHasErrors('cart')
        ->assertDispatched('product-modal-error');

    expect(app(SessionCart::class)->items())
        ->toBe([]);
});

it('does not open a hidden item', function () {
    $category = createProductModalCategory();

    $menuItem = createProductModalItem(
        $category,
        [
            'is_visible' => false,
        ],
    );

    Livewire::test(ProductModal::class)
        ->dispatch(
            'open-product-modal',
            menuItemId: $menuItem->id,
        )
        ->assertSet('menuItemId', null)
        ->assertDispatched('cart-notification')
        ->assertNotDispatched('product-modal-open');
});

it('enforces the maximum product quantity', function () {
    $category = createProductModalCategory();
    $menuItem = createProductModalItem($category);

    Livewire::test(ProductModal::class)
        ->dispatch(
            'open-product-modal',
            menuItemId: $menuItem->id,
        )
        ->set(
            'quantity',
            SessionCart::MAX_QUANTITY + 1,
        )
        ->call('addToCart')
        ->assertHasErrors('quantity')
        ->assertDispatched('product-modal-error');

    expect(app(SessionCart::class)->items())
        ->toBe([]);
});

it('increments and decrements the modal quantity', function () {
    $category = createProductModalCategory();
    $menuItem = createProductModalItem($category);

    Livewire::test(ProductModal::class)
        ->dispatch(
            'open-product-modal',
            menuItemId: $menuItem->id,
        )
        ->assertSet('quantity', 1)
        ->call('incrementQuantity')
        ->assertSet('quantity', 2)
        ->assertSee('$48.00')
        ->call('decrementQuantity')
        ->assertSet('quantity', 1)
        ->assertSee('$24.00');
});
