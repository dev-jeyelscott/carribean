<?php

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Support\Dashboard\DashboardAnalytics;
use App\Support\Dashboard\DashboardPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

beforeEach(function (): void {
    Carbon::setTestNow(
        Carbon::create(
            2026,
            8,
            1,
            12,
            0,
            0,
            config('app.timezone'),
        ),
    );
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('aggregates existing transactional order data', function (): void {
    $paidPickup = dashboardAnalyticsOrder([
        'customer_name' => 'Maya Thompson',
        'status' => OrderStatus::Completed,
        'payment_status' => PaymentStatus::Paid,
        'fulfillment_method' => FulfillmentMethod::Pickup,
        'payment_method' => PaymentMethod::CashAtPickup,
        'grand_total_cents' => 5000,
        'subtotal_cents' => 5000,
        'placed_at' => now()->subDay(),
        'paid_at' => now()->subDay(),
        'completed_at' => now()->subDay(),
    ]);

    dashboardAnalyticsItem(
        order: $paidPickup,
        name: 'Jerk Chicken',
        quantity: 2,
        lineTotalCents: 5000,
    );

    $pendingDelivery = dashboardAnalyticsOrder([
        'customer_name' => 'Liam Carter',
        'status' => OrderStatus::PendingConfirmation,
        'payment_status' => PaymentStatus::Pending,
        'fulfillment_method' => FulfillmentMethod::Delivery,
        'payment_method' => PaymentMethod::CashOnDelivery,
        'grand_total_cents' => 3000,
        'subtotal_cents' => 3000,
        'placed_at' => now(),
        'paid_at' => null,
    ]);

    dashboardAnalyticsItem(
        order: $pendingDelivery,
        name: 'Sorrel Cooler',
        quantity: 2,
        lineTotalCents: 3000,
    );

    $preparingDelivery = dashboardAnalyticsOrder([
        'customer_name' => 'Sophia Williams',
        'status' => OrderStatus::Preparing,
        'payment_status' => PaymentStatus::Paid,
        'fulfillment_method' => FulfillmentMethod::Delivery,
        'payment_method' => PaymentMethod::Stripe,
        'grand_total_cents' => 2500,
        'subtotal_cents' => 2500,
        'placed_at' => now()->subDays(2),
        'paid_at' => now()->subDays(2),
    ]);

    dashboardAnalyticsItem(
        order: $preparingDelivery,
        name: 'Plantain Croquettes',
        quantity: 1,
        lineTotalCents: 2500,
    );

    $previousOrder = dashboardAnalyticsOrder([
        'customer_name' => 'Previous Customer',
        'status' => OrderStatus::Completed,
        'payment_status' => PaymentStatus::Paid,
        'fulfillment_method' => FulfillmentMethod::Pickup,
        'payment_method' => PaymentMethod::CashAtPickup,
        'grand_total_cents' => 4000,
        'subtotal_cents' => 4000,
        'placed_at' => now()->subDays(8),
        'paid_at' => now()->subDays(8),
        'completed_at' => now()->subDays(8),
    ]);

    dashboardAnalyticsItem(
        order: $previousOrder,
        name: 'Jerk Chicken',
        quantity: 1,
        lineTotalCents: 4000,
    );

    $period = DashboardPeriod::fromFilters([
        'startDate' => now()->subDays(6)->toDateString(),
        'endDate' => now()->toDateString(),
    ]);

    $analytics = app(
        DashboardAnalytics::class,
    );

    $overview = $analytics->overview($period);
    $statuses = $analytics->statusCounts($period);
    $fulfillment =
        $analytics->fulfillmentCounts($period);
    $topItems =
        $analytics->topSellingItems($period);
    $recentOrders =
        $analytics->recentOrders($period);
    $revenueSeries =
        $analytics->revenueSeries($period);

    expect($overview['current']['orders_count'])
        ->toBe(3)
        ->and(
            $overview['current']['revenue_cents'],
        )
        ->toBe(7500)
        ->and(
            $overview['current'][
                'paid_orders_count'
            ],
        )
        ->toBe(2)
        ->and(
            $overview['current'][
                'average_order_value_cents'
            ],
        )
        ->toBe(3750)
        ->and(
            $overview['current']['pending_count'],
        )
        ->toBe(1)
        ->and(
            $overview['current']['preparing_count'],
        )
        ->toBe(1)
        ->and(
            $overview['previous']['revenue_cents'],
        )
        ->toBe(4000);

    expect($statuses['completed'])
        ->toBe(1)
        ->and($statuses['processing'])
        ->toBe(1)
        ->and($statuses['pending'])
        ->toBe(1)
        ->and($statuses['cancelled'])
        ->toBe(0);

    expect($fulfillment['pickup'])
        ->toBe(1)
        ->and($fulfillment['delivery'])
        ->toBe(2)
        ->and($fulfillment['total'])
        ->toBe(3);

    expect($topItems)
        ->toHaveCount(2)
        ->and($topItems[0]['name'])
        ->toBe('Jerk Chicken')
        ->and($topItems[0]['quantity_sold'])
        ->toBe(2)
        ->and($topItems[0]['revenue_cents'])
        ->toBe(5000)
        ->and($topItems[0]['change_percent'])
        ->toBe(100.0);

    expect($recentOrders)
        ->toHaveCount(3)
        ->and(array_sum($revenueSeries['current']))
        ->toBe(7500);
});

it('applies the fulfillment filter across dashboard queries', function (): void {
    dashboardAnalyticsOrder([
        'status' => OrderStatus::Completed,
        'payment_status' => PaymentStatus::Paid,
        'fulfillment_method' => FulfillmentMethod::Pickup,
        'payment_method' => PaymentMethod::CashAtPickup,
        'grand_total_cents' => 5000,
        'subtotal_cents' => 5000,
    ]);

    dashboardAnalyticsOrder([
        'status' => OrderStatus::Preparing,
        'payment_status' => PaymentStatus::Paid,
        'fulfillment_method' => FulfillmentMethod::Delivery,
        'payment_method' => PaymentMethod::Stripe,
        'grand_total_cents' => 2500,
        'subtotal_cents' => 2500,
    ]);

    dashboardAnalyticsOrder([
        'status' => OrderStatus::PendingConfirmation,
        'payment_status' => PaymentStatus::Pending,
        'fulfillment_method' => FulfillmentMethod::Delivery,
        'payment_method' => PaymentMethod::CashOnDelivery,
        'grand_total_cents' => 3000,
        'subtotal_cents' => 3000,
        'paid_at' => null,
    ]);

    $period = DashboardPeriod::fromFilters([
        'startDate' => now()->subDays(6)->toDateString(),
        'endDate' => now()->toDateString(),
        'fulfillmentMethod' => FulfillmentMethod::Delivery->value,
    ]);

    $analytics = app(
        DashboardAnalytics::class,
    );

    $summary = $analytics->summary($period);

    $fulfillment =
        $analytics->fulfillmentCounts($period);

    expect($summary['orders_count'])
        ->toBe(2)
        ->and($summary['revenue_cents'])
        ->toBe(2500)
        ->and($summary['pending_count'])
        ->toBe(1)
        ->and($summary['preparing_count'])
        ->toBe(1)
        ->and($fulfillment['pickup'])
        ->toBe(0)
        ->and($fulfillment['delivery'])
        ->toBe(2);
});

/**
 * Create a complete order fixture for dashboard aggregation tests.
 *
 * @param  array<string, mixed>  $overrides
 */
function dashboardAnalyticsOrder(
    array $overrides = [],
): Order {
    $placedAt = $overrides['placed_at']
        ?? now();

    $paymentStatus =
        $overrides['payment_status']
        ?? PaymentStatus::Paid;

    $defaults = [
        'public_id' => (string) Str::ulid(),
        'order_number' => 'CC-TEST-'.Str::upper(
            Str::random(8),
        ),
        'user_id' => null,
        'customer_name' => 'Dashboard Customer',
        'customer_email' => Str::lower(Str::random(10))
            .'@example.test',
        'customer_phone' => '555-0100',
        'status' => OrderStatus::Completed,
        'payment_status' => PaymentStatus::Paid,
        'fulfillment_method' => FulfillmentMethod::Pickup,
        'payment_method' => PaymentMethod::CashAtPickup,
        'currency' => 'USD',
        'subtotal_cents' => 2000,
        'discount_cents' => 0,
        'tax_cents' => 0,
        'delivery_cents' => 0,
        'grand_total_cents' => 2000,
        'tax_rate_basis_points' => 0,
        'checkout_idempotency_token' => (string) Str::uuid(),
        'placed_at' => $placedAt,
        'paid_at' => $paymentStatus === PaymentStatus::Paid
                ? $placedAt
                : null,
    ];

    return Order::query()->create([
        ...$defaults,
        ...$overrides,
    ]);
}

/**
 * Add one immutable order-item snapshot to an order fixture.
 */
function dashboardAnalyticsItem(
    Order $order,
    string $name,
    int $quantity,
    int $lineTotalCents,
): void {
    $unitPriceCents = (int) round(
        $lineTotalCents / $quantity,
    );

    $order->items()->create([
        'menu_item_id' => null,
        'name' => $name,
        'description' => null,
        'base_unit_price_cents' => $unitPriceCents,
        'quantity' => $quantity,
        'selected_options' => null,
        'option_total_cents' => 0,
        'unit_price_cents' => $unitPriceCents,
        'line_total_cents' => $lineTotalCents,
    ]);
}
