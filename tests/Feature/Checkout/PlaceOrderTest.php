<?php

use App\Actions\Orders\PlaceOrder;
use App\Enums\CouponType;
use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Coupon;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Cart\SessionCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

/**
 * Update an online-ordering setting for checkout tests.
 */
function setCheckoutSetting(
    string $key,
    string $value,
): void {
    SiteSetting::query()->updateOrCreate(
        ['key' => $key],
        [
            'value' => $value,
            'group' => 'ordering',
        ],
    );
}

/**
 * Create one available and purchasable checkout item.
 */
function createCheckoutItem(
    int $priceCents = 2_000,
): MenuItem {
    $category = MenuCategory::query()->create([
        'name' => 'Main Courses',
        'slug' => 'checkout-main-courses',
        'description' => 'Checkout test category.',
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    return MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => 'Jerk Chicken',
        'slug' => 'checkout-jerk-chicken',
        'description' => 'Island-spiced chicken.',
        'price_cents' => $priceCents,
        'sort_order' => 1,
        'is_visible' => true,
        'is_featured' => false,
        'is_available' => true,
        'is_purchasable' => true,
    ]);
}

/**
 * Return standard guest customer details.
 *
 * @return array{name: string, email: string, phone: string}
 */
function checkoutCustomer(): array
{
    return [
        'name' => 'Jordan Smith',
        'email' => 'jordan@example.com',
        'phone' => '555-0100',
    ];
}

beforeEach(function (): void {
    session()->forget('shopping_cart');

    setCheckoutSetting(
        'accepting_online_orders',
        '1',
    );

    setCheckoutSetting(
        'accepted_delivery_zip_codes',
        '90001',
    );

    setCheckoutSetting(
        'delivery_fee_cents',
        '500',
    );

    setCheckoutSetting(
        'delivery_minimum_cents',
        '1000',
    );

    setCheckoutSetting(
        'tax_rate_basis_points',
        '825',
    );

    setCheckoutSetting(
        'cash_at_pickup_enabled',
        '1',
    );

    setCheckoutSetting(
        'cash_on_delivery_enabled',
        '1',
    );
});

test(
    'guest cash pickup checkout creates immutable order records',
    function (): void {
        $menuItem = createCheckoutItem();

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            2,
        );

        $order = app(PlaceOrder::class)->execute(
            app(SessionCart::class),
            null,
            checkoutCustomer(),
            FulfillmentMethod::Pickup,
            PaymentMethod::CashAtPickup,
            null,
            'Please pack sauces separately.',
            (string) Str::uuid(),
        );

        expect($order)
            ->status->toBe(
                OrderStatus::PendingConfirmation,
            )
            ->payment_status->toBe(
                PaymentStatus::Pending,
            )
            ->fulfillment_method->toBe(
                FulfillmentMethod::Pickup,
            )
            ->payment_method->toBe(
                PaymentMethod::CashAtPickup,
            )
            ->subtotal_cents->toBe(4_000)
            ->tax_cents->toBe(330)
            ->delivery_cents->toBe(0)
            ->grand_total_cents->toBe(4_330)
            ->customer_note->toBe(
                'Please pack sauces separately.',
            );

        expect($order->public_id)
            ->toHaveLength(26);

        expect($order->order_number)
            ->toStartWith('CC-');

        expect($order->items)
            ->toHaveCount(1);

        expect($order->items->first())
            ->name->toBe('Jerk Chicken')
            ->base_unit_price_cents->toBe(2_000)
            ->quantity->toBe(2)
            ->line_total_cents->toBe(4_000);

        expect($order->addresses)
            ->toHaveCount(0);

        expect($order->payments)
            ->toHaveCount(1);

        expect($order->payments->first())
            ->provider->toBe('cash')
            ->status->toBe(
                PaymentStatus::Pending,
            )
            ->amount_cents->toBe(4_330);

        expect($order->statusHistories)
            ->toHaveCount(1);

        expect(
            app(SessionCart::class)->count(),
        )->toBe(0);
    },
);

test(
    'registered delivery checkout snapshots address and coupon usage',
    function (): void {
        $user = User::factory()->create([
            'phone' => '555-0101',
        ]);

        $menuItem = createCheckoutItem(
            3_000,
        );

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            1,
        );

        app(SessionCart::class)
            ->setCouponCode('ISLAND5');

        Coupon::query()->create([
            'code' => 'ISLAND5',
            'type' => CouponType::FixedAmount,
            'fixed_discount_cents' => 500,
            'minimum_subtotal_cents' => 0,
            'times_used' => 0,
            'is_active' => true,
        ]);

        $order = app(PlaceOrder::class)->execute(
            app(SessionCart::class),
            $user,
            checkoutCustomer(),
            FulfillmentMethod::Delivery,
            PaymentMethod::CashOnDelivery,
            [
                'recipient_name' => 'Jordan Smith',
                'street_address' => '123 Ocean Avenue',
                'apartment_or_unit' => 'Unit 2',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90001',
                'phone' => '555-0100',
                'delivery_instructions' => 'Ring the bell.',
            ],
            null,
            (string) Str::uuid(),
        );

        expect($order)
            ->user_id->toBe($user->id)
            ->discount_cents->toBe(500)
            ->tax_cents->toBe(206)
            ->delivery_cents->toBe(500)
            ->grand_total_cents->toBe(3_206);

        expect($order->addresses)
            ->toHaveCount(1);

        expect($order->addresses->first())
            ->postal_code->toBe('90001')
            ->street_address->toBe(
                '123 Ocean Avenue',
            );

        expect($order->couponUsage)
            ->not->toBeNull()
            ->code->toBe('ISLAND5')
            ->discount_cents->toBe(500);

        expect(
            Coupon::query()
                ->where('code', 'ISLAND5')
                ->value('times_used'),
        )->toBe(1);
    },
);

test(
    'repeating an idempotency token returns the original order',
    function (): void {
        $menuItem = createCheckoutItem();

        $cart = app(SessionCart::class);

        $cart->add(
            $menuItem->id,
            [],
            1,
        );

        $token = (string) Str::uuid();

        $firstOrder =
            app(PlaceOrder::class)->execute(
                $cart,
                null,
                checkoutCustomer(),
                FulfillmentMethod::Pickup,
                PaymentMethod::CashAtPickup,
                null,
                null,
                $token,
            );

        $secondOrder =
            app(PlaceOrder::class)->execute(
                $cart,
                null,
                checkoutCustomer(),
                FulfillmentMethod::Pickup,
                PaymentMethod::CashAtPickup,
                null,
                null,
                $token,
            );

        expect($secondOrder->is($firstOrder))
            ->toBeTrue();

        expect(Order::query()->count())
            ->toBe(1);
    },
);

test(
    'checkout recalculates prices from current database values',
    function (): void {
        $menuItem = createCheckoutItem(
            2_000,
        );

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            1,
        );

        $menuItem->update([
            'price_cents' => 2_500,
        ]);

        $order = app(PlaceOrder::class)->execute(
            app(SessionCart::class),
            null,
            checkoutCustomer(),
            FulfillmentMethod::Pickup,
            PaymentMethod::CashAtPickup,
            null,
            null,
            (string) Str::uuid(),
        );

        expect($order)
            ->subtotal_cents->toBe(2_500);

        expect($order->items->first())
            ->base_unit_price_cents
            ->toBe(2_500);
    },
);

test(
    'pickup rejects a delivery-only cash method',
    function (): void {
        $menuItem = createCheckoutItem();

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            1,
        );

        expect(
            fn () => app(
                PlaceOrder::class,
            )->execute(
                app(SessionCart::class),
                null,
                checkoutCustomer(),
                FulfillmentMethod::Pickup,
                PaymentMethod::CashOnDelivery,
                null,
                null,
                (string) Str::uuid(),
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect(Order::query()->count())
            ->toBe(0);

        expect(
            app(SessionCart::class)->count(),
        )->toBe(1);
    },
);
