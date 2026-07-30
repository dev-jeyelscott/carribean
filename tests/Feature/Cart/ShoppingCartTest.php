<?php

use App\Livewire\Cart\AddToCart;
use App\Livewire\Cart\CartPage;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\MenuItemOptionGroup;
use App\Support\Cart\SessionCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    session()->forget('shopping_cart.items');
});

/**
 * Create a visible category for shopping-cart tests.
 */
function createCartCategory(
    array $attributes = [],
): MenuCategory {
    return MenuCategory::query()->create([
        'name' => 'Main Courses',
        'slug' => 'main-courses',
        'description' => 'Caribbean main dishes.',
        'sort_order' => 1,
        'is_visible' => true,
        ...$attributes,
    ]);
}

/**
 * Create a purchasable item for shopping-cart tests.
 */
function createCartMenuItem(
    MenuCategory $category,
    array $attributes = [],
): MenuItem {
    return MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => 'Jerk Chicken',
        'slug' => 'jerk-chicken',
        'description' => 'Jerk chicken with island herbs.',
        'price_cents' => 1850,
        'sort_order' => 1,
        'is_visible' => true,
        'is_featured' => false,
        'is_available' => true,
        'is_purchasable' => true,
        ...$attributes,
    ]);
}

/**
 * Add a required single-choice option group to a menu item.
 *
 * @return array{
 *     group: MenuItemOptionGroup,
 *     included: MenuItemOption,
 *     premium: MenuItemOption
 * }
 */
function createCartSideOptions(
    MenuItem $menuItem,
): array {
    $group = $menuItem->optionGroups()->create([
        'name' => 'Side',
        'is_required' => true,
        'minimum_selections' => 1,
        'maximum_selections' => 1,
        'sort_order' => 1,
    ]);

    $included = $group->options()->create([
        'name' => 'Rice and Peas',
        'additional_price_cents' => 0,
        'is_available' => true,
        'sort_order' => 1,
    ]);

    $premium = $group->options()->create([
        'name' => 'Plantain',
        'additional_price_cents' => 200,
        'is_available' => true,
        'sort_order' => 2,
    ]);

    return [
        'group' => $group,
        'included' => $included,
        'premium' => $premium,
    ];
}

test('the public cart page renders its livewire component', function (): void {
    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSeeLivewire(CartPage::class)
        ->assertSee('Review Your Cart')
        ->assertSee('Your cart is empty');
});

test('a configured item can be added through livewire', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);
    $options = createCartSideOptions($menuItem);

    Livewire::test(AddToCart::class, [
        'menuItem' => $menuItem,
    ])
        ->set(
            'selections.'.$options['group']->id,
            $options['premium']->id,
        )
        ->set('quantity', 2)
        ->call('addToCart')
        ->assertHasNoErrors()
        ->assertDispatched('cart-updated')
        ->assertDispatched('cart-notification');

    $cartItems = app(SessionCart::class)->items();

    expect($cartItems)
        ->toHaveCount(1);

    $cartItem = array_values($cartItems)[0];

    expect($cartItem['menu_item_id'])
        ->toBe($menuItem->id)
        ->and($cartItem['option_ids'])
        ->toBe([$options['premium']->id])
        ->and($cartItem['quantity'])
        ->toBe(2);
});

test('required option groups are enforced', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);
    $options = createCartSideOptions($menuItem);

    Livewire::test(AddToCart::class, [
        'menuItem' => $menuItem,
    ])
        ->call('addToCart')
        ->assertHasErrors([
            'selections.'.$options['group']->id,
        ]);

    expect(app(SessionCart::class)->items())
        ->toBe([]);
});

test('an unavailable option cannot be submitted manually', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);
    $options = createCartSideOptions($menuItem);

    $unavailableOption = $options['group']->options()->create([
        'name' => 'Unavailable Side',
        'additional_price_cents' => 300,
        'is_available' => false,
        'sort_order' => 3,
    ]);

    Livewire::test(AddToCart::class, [
        'menuItem' => $menuItem,
    ])
        ->set(
            'selections.'.$options['group']->id,
            $unavailableOption->id,
        )
        ->call('addToCart')
        ->assertHasErrors([
            'selections',
        ]);

    expect(app(SessionCart::class)->items())
        ->toBe([]);
});

test('an option belonging to another item is rejected', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);
    $options = createCartSideOptions($menuItem);

    $otherItem = createCartMenuItem($category, [
        'name' => 'Curry Goat',
        'slug' => 'curry-goat',
    ]);

    $otherOptions = createCartSideOptions($otherItem);

    Livewire::test(AddToCart::class, [
        'menuItem' => $menuItem,
    ])
        ->set(
            'selections.'.$options['group']->id,
            $otherOptions['included']->id,
        )
        ->call('addToCart')
        ->assertHasErrors([
            'selections',
        ]);

    expect(app(SessionCart::class)->items())
        ->toBe([]);
});

test('identical configurations merge into one line', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);
    $options = createCartSideOptions($menuItem);
    $cart = app(SessionCart::class);

    $firstLineKey = $cart->add(
        $menuItem->id,
        [$options['premium']->id],
        1,
    );

    $secondLineKey = $cart->add(
        $menuItem->id,
        [$options['premium']->id],
        2,
    );

    expect($secondLineKey)
        ->toBe($firstLineKey)
        ->and($cart->items())
        ->toHaveCount(1)
        ->and($cart->items()[$firstLineKey]['quantity'])
        ->toBe(3);
});

test('different configurations remain separate lines', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);
    $options = createCartSideOptions($menuItem);
    $cart = app(SessionCart::class);

    $includedLineKey = $cart->add(
        $menuItem->id,
        [$options['included']->id],
        1,
    );

    $premiumLineKey = $cart->add(
        $menuItem->id,
        [$options['premium']->id],
        1,
    );

    expect($premiumLineKey)
        ->not->toBe($includedLineKey)
        ->and($cart->items())
        ->toHaveCount(2);
});

test('cart totals use the current database price', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);

    app(SessionCart::class)->add(
        $menuItem->id,
        [],
        2,
    );

    $menuItem->update([
        'price_cents' => 2400,
    ]);

    Livewire::test(CartPage::class)
        ->assertSee('$24.00')
        ->assertSee('$48.00')
        ->assertDontSee('$18.50');
});

test('cart quantities can be updated', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);
    $cart = app(SessionCart::class);

    $lineKey = $cart->add(
        $menuItem->id,
        [],
        1,
    );

    Livewire::test(CartPage::class)
        ->set('quantities.'.$lineKey, 3)
        ->call('updateQuantity', $lineKey)
        ->assertHasNoErrors()
        ->assertDispatched('cart-updated');

    expect($cart->items()[$lineKey]['quantity'])
        ->toBe(3);
});

test('cart lines can be removed', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);
    $cart = app(SessionCart::class);

    $lineKey = $cart->add(
        $menuItem->id,
        [],
        1,
    );

    Livewire::test(CartPage::class)
        ->call('removeItem', $lineKey)
        ->assertDispatched('cart-updated');

    expect($cart->items())
        ->toBe([]);
});

test('the entire cart can be cleared', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);
    $cart = app(SessionCart::class);

    $cart->add(
        $menuItem->id,
        [],
        2,
    );

    Livewire::test(CartPage::class)
        ->call('clearCart')
        ->assertDispatched('cart-updated')
        ->assertSee('Your cart is empty');

    expect($cart->items())
        ->toBe([]);
});

test('unavailable items are removed when the cart loads', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);
    $cart = app(SessionCart::class);

    $cart->add(
        $menuItem->id,
        [],
        1,
    );

    $menuItem->update([
        'is_available' => false,
    ]);

    Livewire::test(CartPage::class)
        ->assertSee(
            'One or more unavailable items or stale options were removed from your cart.',
        )
        ->assertSee('Your cart is empty');

    expect($cart->items())
        ->toBe([]);
});

test('quantity cannot exceed the configured cart limit', function (): void {
    $category = createCartCategory();
    $menuItem = createCartMenuItem($category);

    Livewire::test(AddToCart::class, [
        'menuItem' => $menuItem,
    ])
        ->set(
            'quantity',
            SessionCart::MAX_QUANTITY + 1,
        )
        ->call('addToCart')
        ->assertHasErrors([
            'quantity',
        ]);

    expect(app(SessionCart::class)->items())
        ->toBe([]);
});
