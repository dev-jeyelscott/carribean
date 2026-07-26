<?php

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Mail\Orders\AdminOrderAlert;
use App\Mail\Orders\CustomerOrderUpdate;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Create one order for after-commit observer testing.
 */
function createNotificationOrder(): Order
{
    return Order::query()->create([
        'public_id' => (string) Str::ulid(),
        'order_number' => 'CC-NOTIFY-000001',
        'customer_name' => 'Jamie Lee',
        'customer_email' => 'jamie@example.com',
        'customer_phone' => '555-0100',
        'status' => OrderStatus::PendingConfirmation,
        'payment_status' => PaymentStatus::Pending,
        'fulfillment_method' => FulfillmentMethod::Pickup,
        'payment_method' => PaymentMethod::CashAtPickup,
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
}

beforeEach(function (): void {
    config([
        'mail.inquiries_to' => 'orders@example.com',
    ]);

    Mail::fake();
});

test(
    'new orders queue customer and administrator emails',
    function (): void {
        createNotificationOrder();

        Mail::assertQueued(
            CustomerOrderUpdate::class,
            1,
        );

        Mail::assertQueued(
            AdminOrderAlert::class,
            1,
        );
    },
);

test(
    'customer lifecycle email is queued after a status change',
    function (): void {
        $order = createNotificationOrder();

        $order->forceFill([
            'status' => OrderStatus::Confirmed,
        ])->save();

        Mail::assertQueued(
            CustomerOrderUpdate::class,
            2,
        );
    },
);

test(
    'payment failure queues an administrator alert',
    function (): void {
        $order = createNotificationOrder();

        $order->forceFill([
            'payment_status' => PaymentStatus::Failed,
        ])->save();

        Mail::assertQueued(
            AdminOrderAlert::class,
            2,
        );
    },
);
