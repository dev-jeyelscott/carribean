<?php

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Livewire\Account\OrderDetail;
use App\Livewire\Orders\GuestOrderDetail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * Create one complete order fixture for tracking-page tests.
 */
function createOrderTrackingFixture(
    ?User $user = null,
    OrderStatus $status = OrderStatus::PendingConfirmation,
    FulfillmentMethod $fulfillmentMethod = FulfillmentMethod::Pickup,
): Order {
    $order = Order::query()->create([
        'public_id' => (string) Str::ulid(),

        'order_number' => 'CC-TEST-'.Str::upper(
            Str::random(8),
        ),

        'user_id' => $user?->id,
        'customer_name' => $user?->name
            ?? 'Guest Customer',
        'customer_email' => $user?->email
            ?? 'guest@example.com',
        'customer_phone' => '+1 555 010 2000',
        'status' => $status,
        'payment_status' => PaymentStatus::Paid,
        'fulfillment_method' => $fulfillmentMethod,

        'payment_method' => $fulfillmentMethod
            === FulfillmentMethod::Pickup
            ? PaymentMethod::CashAtPickup
            : PaymentMethod::CashOnDelivery,

        'currency' => 'USD',
        'subtotal_cents' => 2_500,
        'discount_cents' => 0,
        'tax_cents' => 200,

        'delivery_cents' => $fulfillmentMethod
            === FulfillmentMethod::Delivery
            ? 500
            : 0,

        'grand_total_cents' => $fulfillmentMethod
            === FulfillmentMethod::Delivery
            ? 3_200
            : 2_700,

        'tax_rate_basis_points' => 800,
        'customer_note' => 'Please package carefully.',
        'internal_note' => 'Private staff note.',
        'checkout_idempotency_token' => (string) Str::uuid(),
        'placed_at' => now()->subHour(),
        'paid_at' => now()->subMinutes(55),

        'picked_up_at' => $status
            === OrderStatus::PickedUp
            ? now()->subMinutes(5)
            : null,

        'delivered_at' => $status
            === OrderStatus::Delivered
            ? now()->subMinutes(5)
            : null,

        'completed_at' => $status
            === OrderStatus::Completed
            ? now()->subMinutes(2)
            : null,
    ]);

    $order->items()->create([
        'menu_item_id' => null,
        'name' => 'Jerk Chicken',
        'description' => 'Snapshotted menu item description.',
        'base_unit_price_cents' => 2_300,
        'quantity' => 1,

        'selected_options' => [
            [
                'group_name' => 'Side',
                'name' => 'Plantain',
                'additional_price_cents' => 200,
            ],
        ],

        'option_total_cents' => 200,
        'unit_price_cents' => 2_500,
        'line_total_cents' => 2_500,
    ]);

    if (
        $fulfillmentMethod
            === FulfillmentMethod::Delivery
    ) {
        $order->addresses()->create([
            'type' => 'delivery',
            'recipient_name' => $order->customer_name,
            'street_address' => '123 Ocean Avenue',
            'apartment_or_unit' => 'Unit 4',
            'city' => 'Long Beach',
            'state' => 'CA',
            'postal_code' => '90802',
            'phone' => $order->customer_phone,
            'delivery_instructions' => 'Please ring the doorbell.',
        ]);
    }

    $previousStatus = match ($status) {
        OrderStatus::PendingConfirmation => null,
        OrderStatus::PickedUp => OrderStatus::ReadyForPickup,
        OrderStatus::Delivered => OrderStatus::OutForDelivery,
        OrderStatus::Completed => OrderStatus::Delivered,
        default => OrderStatus::PendingConfirmation,
    };

    $order->statusHistories()->create([
        'changed_by_user_id' => null,
        'previous_status' => $previousStatus,
        'new_status' => $status,
        'public_note' => 'Customer-facing progress update.',
        'internal_note' => 'Private staff history note.',
    ]);

    return $order;
}

test(
    'customers see only their own order history',
    function (): void {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        $ownOrder = createOrderTrackingFixture(
            $customer,
        );

        $otherOrder = createOrderTrackingFixture(
            $otherCustomer,
        );

        $this->actingAs($customer)
            ->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee($ownOrder->order_number)
            ->assertDontSee($otherOrder->order_number);
    },
);

test(
    'customers cannot view another customer order detail',
    function (): void {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        $order = createOrderTrackingFixture(
            $customer,
        );

        $this->actingAs($otherCustomer)
            ->get(
                route(
                    'account.orders.show',
                    [
                        'order' => $order,
                    ],
                ),
            )
            ->assertForbidden();
    },
);

test(
    'guest order detail requires a valid signed url',
    function (): void {
        $order = createOrderTrackingFixture(
            status: OrderStatus::Delivered,
            fulfillmentMethod: FulfillmentMethod::Delivery,
        );

        $signedUrl = URL::temporarySignedRoute(
            'guest.orders.show',
            now()->addHour(),
            [
                'order' => $order,
            ],
        );

        $this->get($signedUrl)
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Customer-facing progress update.')
            ->assertDontSee('Private staff note.')
            ->assertDontSee('Private staff history note.');

        $this->get(
            route(
                'guest.orders.show',
                [
                    'order' => $order,
                ],
            ),
        )->assertForbidden();
    },
);

test(
    'registered orders cannot use the guest tracking route',
    function (): void {
        $customer = User::factory()->create();

        $order = createOrderTrackingFixture(
            $customer,
        );

        $signedUrl = URL::temporarySignedRoute(
            'guest.orders.show',
            now()->addHour(),
            [
                'order' => $order,
            ],
        );

        $this->get($signedUrl)
            ->assertNotFound();
    },
);

test(
    'registered customer can confirm a fulfilled order',
    function (): void {
        $customer = User::factory()->create();

        $order = createOrderTrackingFixture(
            $customer,
            OrderStatus::Delivered,
            FulfillmentMethod::Delivery,
        );

        Livewire::actingAs($customer)
            ->test(
                OrderDetail::class,
                [
                    'order' => $order,
                ],
            )
            ->assertSee('Confirm received')
            ->call('confirmReceived')
            ->assertSee(
                'Thanks. Your order is now marked complete.',
            );

        expect($order->fresh()?->status)
            ->toBe(OrderStatus::Completed);

        $this->assertDatabaseHas(
            'order_status_histories',
            [
                'order_id' => $order->id,
                'changed_by_user_id' => $customer->id,
                'new_status' => OrderStatus::Completed->value,
                'public_note' => 'The customer confirmed that the order was received.',
            ],
        );
    },
);

test(
    'guest can confirm a fulfilled order',
    function (): void {
        $order = createOrderTrackingFixture(
            status: OrderStatus::PickedUp,
        );

        Livewire::test(
            GuestOrderDetail::class,
            [
                'order' => $order,
            ],
        )
            ->assertSee('Confirm received')
            ->call('confirmReceived')
            ->assertSee(
                'Thanks. Your order is now marked complete.',
            );

        expect($order->fresh()?->status)
            ->toBe(OrderStatus::Completed);

        $this->assertDatabaseHas(
            'order_status_histories',
            [
                'order_id' => $order->id,
                'changed_by_user_id' => null,
                'new_status' => OrderStatus::Completed->value,
                'public_note' => 'The guest customer confirmed that the order was received.',
            ],
        );
    },
);

test(
    'confirm received is hidden before fulfillment',
    function (): void {
        $customer = User::factory()->create();

        $order = createOrderTrackingFixture(
            $customer,
            OrderStatus::Preparing,
        );

        Livewire::actingAs($customer)
            ->test(
                OrderDetail::class,
                [
                    'order' => $order,
                ],
            )
            ->assertDontSee('Confirm received');
    },
);
