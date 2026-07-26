<?php

use App\Enums\CouponType;
use App\Enums\FulfillmentMethod;
use App\Models\Coupon;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Support\Cart\SessionCart;
use App\Support\Orders\OrderTotalsCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Update a required ordering setting for pricing tests.
 */
function setPricingSetting(
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
 * Create one purchasable menu item for pricing tests.
 */
function createPricingMenuItem(): MenuItem
{
    $category = MenuCategory::query()->create([
        'name' => 'Main Courses',
        'slug' => 'pricing-main-courses',
        'description' => 'Pricing test dishes.',
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    return MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => 'Jerk Chicken',
        'slug' => 'pricing-jerk-chicken',
        'description' => 'Jerk chicken.',
        'price_cents' => 2_000,
        'sort_order' => 1,
        'is_visible' => true,
        'is_featured' => false,
        'is_available' => true,
        'is_purchasable' => true,
    ]);
}

beforeEach(function (): void {
    session()->forget('shopping_cart');

    setPricingSetting(
        'accepting_online_orders',
        '1',
    );

    setPricingSetting(
        'accepted_delivery_zip_codes',
        "90001\n90002",
    );

    setPricingSetting(
        'delivery_fee_cents',
        '350',
    );

    setPricingSetting(
        'delivery_minimum_cents',
        '3000',
    );

    setPricingSetting(
        'tax_rate_basis_points',
        '825',
    );
});

test(
    'pickup totals include tax without delivery fee',
    function (): void {
        $menuItem = createPricingMenuItem();

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            2,
        );

        $calculation = app(
            OrderTotalsCalculator::class,
        )->calculate(
            app(SessionCart::class)->items(),
            null,
            FulfillmentMethod::Pickup,
        );

        expect($calculation)
            ->subtotal_cents->toBe(4_000)
            ->discount_cents->toBe(0)
            ->tax_cents->toBe(330)
            ->delivery_fee_cents->toBe(0)
            ->grand_total_cents->toBe(4_330)
            ->is_checkout_ready->toBeTrue();
    },
);

test(
    'a fixed coupon is capped and applied before tax',
    function (): void {
        $menuItem = createPricingMenuItem();

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            2,
        );

        Coupon::query()->create([
            'code' => 'ISLAND5',
            'type' => CouponType::FixedAmount,
            'fixed_discount_cents' => 500,
            'minimum_subtotal_cents' => 0,
            'times_used' => 0,
            'is_active' => true,
        ]);

        $calculation = app(
            OrderTotalsCalculator::class,
        )->calculate(
            app(SessionCart::class)->items(),
            'island5',
            FulfillmentMethod::Pickup,
        );

        expect($calculation)
            ->discount_cents->toBe(500)
            ->discounted_subtotal_cents->toBe(3_500)
            ->tax_cents->toBe(289)
            ->grand_total_cents->toBe(3_789)
            ->coupon_error->toBeNull();
    },
);

test(
    'a percentage coupon uses integer basis points',
    function (): void {
        $menuItem = createPricingMenuItem();

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            2,
        );

        Coupon::query()->create([
            'code' => 'ISLAND10',
            'type' => CouponType::Percentage,
            'percentage_basis_points' => 1_000,
            'minimum_subtotal_cents' => 0,
            'times_used' => 0,
            'is_active' => true,
        ]);

        $calculation = app(
            OrderTotalsCalculator::class,
        )->calculate(
            app(SessionCart::class)->items(),
            'island10',
            FulfillmentMethod::Pickup,
        );

        expect($calculation)
            ->discount_cents->toBe(400)
            ->discounted_subtotal_cents->toBe(3_600)
            ->tax_cents->toBe(297)
            ->grand_total_cents->toBe(3_897);
    },
);

test(
    'valid local delivery adds the configured fee',
    function (): void {
        $menuItem = createPricingMenuItem();

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            2,
        );

        $calculation = app(
            OrderTotalsCalculator::class,
        )->calculate(
            app(SessionCart::class)->items(),
            null,
            FulfillmentMethod::Delivery,
            '90001',
        );

        expect($calculation)
            ->delivery_fee_cents->toBe(350)
            ->fulfillment_error->toBeNull()
            ->grand_total_cents->toBe(4_680)
            ->is_checkout_ready->toBeTrue();
    },
);

test(
    'delivery rejects an unsupported zip code',
    function (): void {
        $menuItem = createPricingMenuItem();

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            2,
        );

        $calculation = app(
            OrderTotalsCalculator::class,
        )->calculate(
            app(SessionCart::class)->items(),
            null,
            FulfillmentMethod::Delivery,
            '99999',
        );

        expect($calculation)
            ->delivery_fee_cents->toBe(0)
            ->fulfillment_error
            ->toBe(
                'Local delivery is not available for this ZIP code.',
            )
            ->is_checkout_ready->toBeFalse();
    },
);

test(
    'delivery minimum uses subtotal after discount',
    function (): void {
        $menuItem = createPricingMenuItem();

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            2,
        );

        Coupon::query()->create([
            'code' => 'LESS15',
            'type' => CouponType::FixedAmount,
            'fixed_discount_cents' => 1_500,
            'minimum_subtotal_cents' => 0,
            'times_used' => 0,
            'is_active' => true,
        ]);

        $calculation = app(
            OrderTotalsCalculator::class,
        )->calculate(
            app(SessionCart::class)->items(),
            'LESS15',
            FulfillmentMethod::Delivery,
            '90001',
        );

        expect($calculation)
            ->discounted_subtotal_cents->toBe(2_500)
            ->delivery_fee_cents->toBe(0)
            ->fulfillment_error
            ->toContain(
                'minimum merchandise total of $30.00',
            )
            ->is_checkout_ready->toBeFalse();
    },
);

test(
    'expired coupons fail closed without altering totals',
    function (): void {
        $menuItem = createPricingMenuItem();

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            2,
        );

        Coupon::factory()
            ->expired()
            ->create([
                'code' => 'EXPIRED5',
                'minimum_subtotal_cents' => 0,
            ]);

        $calculation = app(
            OrderTotalsCalculator::class,
        )->calculate(
            app(SessionCart::class)->items(),
            'EXPIRED5',
            FulfillmentMethod::Pickup,
        );

        expect($calculation)
            ->discount_cents->toBe(0)
            ->coupon_error->toBe(
                'This coupon has expired.',
            )
            ->grand_total_cents->toBe(4_330)
            ->is_checkout_ready->toBeFalse();
    },
);

test(
    'paused online ordering disables checkout readiness',
    function (): void {
        setPricingSetting(
            'accepting_online_orders',
            '0',
        );

        $menuItem = createPricingMenuItem();

        app(SessionCart::class)->add(
            $menuItem->id,
            [],
            2,
        );

        $calculation = app(
            OrderTotalsCalculator::class,
        )->calculate(
            app(SessionCart::class)->items(),
        );

        expect($calculation)
            ->accepting_online_orders->toBeFalse()
            ->is_checkout_ready->toBeFalse();
    },
);
