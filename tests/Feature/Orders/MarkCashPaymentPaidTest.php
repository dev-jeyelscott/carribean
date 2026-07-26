<?php

use App\Actions\Payments\MarkCashPaymentPaid;
use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

/**
 * Create an order with one payment for payment-action tests.
 */
function createPaymentActionOrder(
    PaymentMethod $paymentMethod,
): Order {
    $order = Order::query()->create([
        'public_id' => (string) Str::ulid(),
        'order_number' => 'CC-TEST-000001',
        'customer_name' => 'Jamie Lee',
        'customer_email' => 'jamie@example.com',
        'customer_phone' => '555-0100',
        'status' => OrderStatus::Confirmed,
        'payment_status' => PaymentStatus::Pending,
        'fulfillment_method' => FulfillmentMethod::Pickup,
        'payment_method' => $paymentMethod,
        'currency' => 'USD',
        'subtotal_cents' => 2_000,
        'discount_cents' => 0,
        'tax_cents' => 0,
        'delivery_cents' => 0,
        'grand_total_cents' => 2_000,
        'tax_rate_basis_points' => 0,
        'checkout_idempotency_token' => (string) Str::uuid(),
        'placed_at' => now(),
    ]);

    $order->payments()->create([
        'provider' => $paymentMethod->provider(),
        'payment_method' => $paymentMethod,
        'status' => PaymentStatus::Pending,
        'amount_cents' => 2_000,
        'currency' => 'USD',
    ]);

    return $order;
}

test(
    'staff can mark an eligible cash payment paid',
    function (): void {
        $order = createPaymentActionOrder(
            PaymentMethod::CashAtPickup,
        );

        $updatedOrder = app(
            MarkCashPaymentPaid::class,
        )->execute($order);

        expect($updatedOrder)
            ->payment_status->toBe(
                PaymentStatus::Paid,
            )
            ->paid_at->not->toBeNull();

        expect(
            $updatedOrder->payments->first()?->status,
        )->toBe(
            PaymentStatus::Paid,
        );
    },
);

test(
    'staff cannot manually mark a stripe payment paid',
    function (): void {
        $order = createPaymentActionOrder(
            PaymentMethod::Stripe,
        );

        expect(
            fn () => app(
                MarkCashPaymentPaid::class,
            )->execute($order),
        )->toThrow(
            ValidationException::class,
        );
    },
);
