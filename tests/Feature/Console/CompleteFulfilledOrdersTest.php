<?php

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * Create one fulfilled order eligible for automatic completion.
 */
function createFulfilledOrder(
    OrderStatus $status,
    string $orderNumber,
): Order {
    return Order::query()->create([
        'public_id' => (string) Str::ulid(),
        'order_number' => $orderNumber,
        'customer_name' => 'Jamie Lee',
        'customer_email' => 'jamie@example.com',
        'customer_phone' => '555-0100',
        'status' => $status,
        'payment_status' => PaymentStatus::Paid,
        'fulfillment_method' => $status
            === OrderStatus::PickedUp
            ? FulfillmentMethod::Pickup
            : FulfillmentMethod::Delivery,
        'payment_method' => $status
            === OrderStatus::PickedUp
            ? PaymentMethod::CashAtPickup
            : PaymentMethod::CashOnDelivery,
        'currency' => 'USD',
        'subtotal_cents' => 2_000,
        'discount_cents' => 0,
        'tax_cents' => 0,
        'delivery_cents' => 0,
        'grand_total_cents' => 2_000,
        'tax_rate_basis_points' => 0,
        'checkout_idempotency_token' => (string) Str::uuid(),
        'placed_at' => now()->subHours(3),
        'paid_at' => now()->subHours(2),
        'picked_up_at' => $status
            === OrderStatus::PickedUp
            ? now()->subHours(2)
            : null,
        'delivered_at' => $status
            === OrderStatus::Delivered
            ? now()->subHours(2)
            : null,
    ]);
}

test(
    'fulfilled orders older than one hour are completed',
    function (): void {
        $pickupOrder = createFulfilledOrder(
            OrderStatus::PickedUp,
            'CC-AUTO-000001',
        );

        $deliveryOrder = createFulfilledOrder(
            OrderStatus::Delivered,
            'CC-AUTO-000002',
        );

        $this->artisan(
            'orders:complete-fulfilled',
        )->assertSuccessful();

        expect(
            $pickupOrder->fresh()?->status,
        )->toBe(
            OrderStatus::Completed,
        );

        expect(
            $deliveryOrder->fresh()?->status,
        )->toBe(
            OrderStatus::Completed,
        );
    },
);

test(
    'recently fulfilled orders are not completed early',
    function (): void {
        $order = createFulfilledOrder(
            OrderStatus::PickedUp,
            'CC-AUTO-000003',
        );

        $order->forceFill([
            'picked_up_at' => now()->subMinutes(30),
        ])->save();

        $this->artisan(
            'orders:complete-fulfilled',
        )->assertSuccessful();

        expect(
            $order->fresh()?->status,
        )->toBe(
            OrderStatus::PickedUp,
        );
    },
);
