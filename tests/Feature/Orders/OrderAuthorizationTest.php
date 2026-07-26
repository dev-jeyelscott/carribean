<?php

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * Create an order owned by the supplied user.
 */
function createAuthorizedOrder(
    User $user,
): Order {
    return Order::query()->create([
        'public_id' => (string) Str::ulid(),
        'order_number' => 'CC-TEST-000001',
        'user_id' => $user->id,
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'customer_phone' => $user->phone ?? '555-0104',
        'status' => OrderStatus::PendingConfirmation,
        'payment_status' => PaymentStatus::Pending,
        'fulfillment_method' => FulfillmentMethod::Pickup,
        'payment_method' => PaymentMethod::CashAtPickup,
        'currency' => 'USD',
        'subtotal_cents' => 1_000,
        'discount_cents' => 0,
        'tax_cents' => 0,
        'delivery_cents' => 0,
        'grand_total_cents' => 1_000,
        'tax_rate_basis_points' => 0,
        'checkout_idempotency_token' => (string) Str::uuid(),
        'placed_at' => now(),
    ]);
}

test(
    'customer may view only their own order',
    function (): void {
        $owner = User::factory()->create();
        $otherCustomer =
            User::factory()->create();

        $order = createAuthorizedOrder(
            $owner,
        );

        expect(
            Gate::forUser($owner)->allows(
                'view',
                $order,
            ),
        )->toBeTrue();

        expect(
            Gate::forUser(
                $otherCustomer,
            )->allows(
                'view',
                $order,
            ),
        )->toBeFalse();
    },
);
