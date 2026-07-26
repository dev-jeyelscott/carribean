<?php

use App\Enums\CouponType;
use App\Enums\FulfillmentMethod;
use App\Livewire\Cart\CartPage;
use App\Models\Coupon;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Support\Cart\SessionCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    session()->forget('shopping_cart');

    foreach (
        [
            'accepting_online_orders' => '1',
            'accepted_delivery_zip_codes' => '90001',
            'delivery_fee_cents' => '350',
            'delivery_minimum_cents' => '1000',
            'tax_rate_basis_points' => '825',
        ] as $key => $value
    ) {
        SiteSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => 'ordering',
            ],
        );
    }

    $category = MenuCategory::query()->create([
        'name' => 'Cart Pricing',
        'slug' => 'cart-pricing',
        'description' => 'Cart pricing tests.',
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $menuItem = MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => 'Curry Goat',
        'slug' => 'cart-pricing-curry-goat',
        'description' => 'Curry goat.',
        'price_cents' => 2_000,
        'sort_order' => 1,
        'is_visible' => true,
        'is_featured' => false,
        'is_available' => true,
        'is_purchasable' => true,
    ]);

    app(SessionCart::class)->add(
        $menuItem->id,
        [],
        1,
    );
});

test(
    'a coupon can be applied through the cart',
    function (): void {
        Coupon::query()->create([
            'code' => 'WELCOME5',
            'type' => CouponType::FixedAmount,
            'fixed_discount_cents' => 500,
            'minimum_subtotal_cents' => 0,
            'times_used' => 0,
            'is_active' => true,
        ]);

        Livewire::test(CartPage::class)
            ->set('couponCode', 'welcome5')
            ->call('applyCoupon')
            ->assertHasNoErrors()
            ->assertDispatched('cart-notification')
            ->assertSee('WELCOME5')
            ->assertSee('-$5.00')
            ->assertSee('$16.24');

        expect(
            app(SessionCart::class)->couponCode(),
        )->toBe('WELCOME5');
    },
);

test(
    'an invalid coupon is rejected',
    function (): void {
        Livewire::test(CartPage::class)
            ->set('couponCode', 'MISSING')
            ->call('applyCoupon')
            ->assertHasErrors([
                'couponCode',
            ]);

        expect(
            app(SessionCart::class)->couponCode(),
        )->toBeNull();
    },
);

test(
    'local delivery can be selected for an accepted zip',
    function (): void {
        Livewire::test(CartPage::class)
            ->set(
                'fulfillmentMethod',
                FulfillmentMethod::Delivery->value,
            )
            ->set('deliveryZip', '90001')
            ->call('saveFulfillment')
            ->assertHasNoErrors()
            ->assertSee('$3.50');

        $cart = app(SessionCart::class);

        expect($cart->fulfillmentMethod())
            ->toBe(FulfillmentMethod::Delivery)
            ->and($cart->deliveryZip())
            ->toBe('90001');
    },
);

test(
    'unsupported delivery zip is rejected',
    function (): void {
        Livewire::test(CartPage::class)
            ->set(
                'fulfillmentMethod',
                FulfillmentMethod::Delivery->value,
            )
            ->set('deliveryZip', '99999')
            ->call('saveFulfillment')
            ->assertHasErrors([
                'deliveryZip',
            ]);

        expect(
            app(SessionCart::class)
                ->fulfillmentMethod(),
        )->toBe(FulfillmentMethod::Pickup);
    },
);

test(
    'clearing the cart removes pricing context',
    function (): void {
        $cart = app(SessionCart::class);

        $cart->setCouponCode('WELCOME5');

        $cart->setFulfillmentMethod(
            FulfillmentMethod::Delivery,
        );

        $cart->setDeliveryZip('90001');

        Livewire::test(CartPage::class)
            ->call('clearCart')
            ->assertSee('Your cart is empty');

        expect($cart->items())
            ->toBe([])
            ->and($cart->couponCode())
            ->toBeNull()
            ->and($cart->fulfillmentMethod())
            ->toBe(FulfillmentMethod::Pickup)
            ->and($cart->deliveryZip())
            ->toBeNull();
    },
);
