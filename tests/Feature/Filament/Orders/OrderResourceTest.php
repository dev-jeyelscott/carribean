<?php

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Mail\Orders\CustomerOrderUpdate;
use App\Models\Order;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use function Pest\Livewire\livewire;

/**
 * Create one deterministic order for Filament acceptance tests.
 */
function createFilamentOrder(
    array $overrides = [],
): Order {
    $order = Order::query()->create([
        'public_id' => (string) Str::ulid(),
        'order_number' => 'CC-'.Str::upper(
            Str::random(10),
        ),
        'user_id' => null,
        'customer_name' => 'Acceptance Guest',
        'customer_email' => 'acceptance@example.com',
        'customer_phone' => '555-0100',
        'status' => OrderStatus::PendingConfirmation,
        'payment_status' => PaymentStatus::Pending,
        'fulfillment_method' => FulfillmentMethod::Pickup,
        'payment_method' => PaymentMethod::CashAtPickup,
        'currency' => 'USD',
        'subtotal_cents' => 2_400,
        'discount_cents' => 0,
        'tax_cents' => 0,
        'delivery_cents' => 0,
        'grand_total_cents' => 2_400,
        'tax_rate_basis_points' => 0,
        'coupon_code' => null,
        'coupon_snapshot' => null,
        'customer_note' => 'Please prepare utensils.',
        'internal_note' => null,
        'checkout_idempotency_token' => (string) Str::uuid(),
        'placed_at' => now(),
        ...$overrides,
    ]);

    $order->items()->create([
        'menu_item_id' => null,
        'name' => 'Island Jerk Chicken',
        'description' => 'Acceptance-test order item.',
        'base_unit_price_cents' => 2_400,
        'quantity' => 1,
        'selected_options' => [
            [
                'group_name' => 'Spice Level',
                'name' => 'Mild',
                'additional_price_cents' => 0,
            ],
            [
                'group_name' => 'Choose a Side',
                'name' => 'Rice and Peas',
                'additional_price_cents' => 0,
            ],
        ],
        'option_total_cents' => 0,
        'unit_price_cents' => 2_400,
        'line_total_cents' => 2_400,
    ]);

    $order->payments()->create([
        'provider' => 'cash',
        'payment_method' => PaymentMethod::CashAtPickup,
        'status' => PaymentStatus::Pending,
        'amount_cents' => 2_400,
        'currency' => 'USD',
    ]);

    $order->statusHistories()->create([
        'previous_status' => null,
        'new_status' => OrderStatus::PendingConfirmation,
        'public_note' => 'Order received.',
        'internal_note' => null,
    ]);

    return $order;
}

beforeEach(function (): void {
    $administrator = User::factory()->create([
        'name' => 'Test Administrator',
        'email' => config('admin.seed_user.email'),
    ]);

    $this->actingAs($administrator);

    Filament::setCurrentPanel(
        Filament::getPanel('admin'),
    );
});

test(
    'administrator can view and search customer orders',
    function (): void {
        $order = createFilamentOrder();

        livewire(ListOrders::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$order])
            ->searchTable($order->order_number)
            ->assertCanSeeTableRecords([$order]);
    },
);

test(
    'administrator can inspect an order detail',
    function (): void {
        $order = createFilamentOrder();

        livewire(
            ViewOrder::class,
            [
                'record' => $order->getRouteKey(),
            ],
        )
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Acceptance Guest')
            ->assertSee('Island Jerk Chicken')
            ->assertSee('$24.00')
            ->assertSee('Pending Confirmation');
    },
);

test(
    'administrator sees only valid lifecycle actions',
    function (): void {
        $order = createFilamentOrder();

        livewire(
            ViewOrder::class,
            [
                'record' => $order->getRouteKey(),
            ],
        )
            ->assertActionVisible(
                'transitionToConfirmed',
            )
            ->assertActionVisible(
                'transitionToRejected',
            )
            ->assertActionVisible(
                'transitionToCancelled',
            )
            ->assertActionHidden(
                'transitionToDelivered',
            )
            ->callAction(
                'transitionToConfirmed',
            )
            ->assertHasNoActionErrors();

        expect(
            $order->refresh()->status,
        )->toBe(
            OrderStatus::Confirmed,
        );

        expect(
            $order->statusHistories()
                ->latest('id')
                ->first()
                ?->new_status,
        )->toBe(
            OrderStatus::Confirmed,
        );
    },
);

test(
    'administrator can mark an eligible cash payment paid',
    function (): void {
        $order = createFilamentOrder();

        livewire(
            ViewOrder::class,
            [
                'record' => $order->getRouteKey(),
            ],
        )
            ->assertActionVisible(
                'markCashPaymentPaid',
            )
            ->callAction(
                'markCashPaymentPaid',
            )
            ->assertHasNoActionErrors();

        $order->refresh();

        expect($order)
            ->payment_status->toBe(
                PaymentStatus::Paid,
            )
            ->paid_at->not->toBeNull();

        expect(
            $order->payments()
                ->latest('id')
                ->first()
                ?->status,
        )->toBe(
            PaymentStatus::Paid,
        );
    },
);

test(
    'administrator can store an internal note',
    function (): void {
        $order = createFilamentOrder();

        livewire(
            ViewOrder::class,
            [
                'record' => $order->getRouteKey(),
            ],
        )
            ->callAction(
                'addInternalNote',
                data: [
                    'internal_note' => 'Customer called to confirm pickup time.',
                ],
            )
            ->assertHasNoActionErrors();

        expect(
            $order->refresh()->internal_note,
        )->toBe(
            'Customer called to confirm pickup time.',
        );
    },
);

test(
    'administrator can resend the current customer update',
    function (): void {
        Mail::fake();

        $order = createFilamentOrder();

        livewire(
            ViewOrder::class,
            [
                'record' => $order->getRouteKey(),
            ],
        )
            ->callAction(
                'resendCustomerNotification',
            )
            ->assertHasNoActionErrors();

        Mail::assertQueued(
            CustomerOrderUpdate::class,
            function (
                CustomerOrderUpdate $mail,
            ) use ($order): bool {
                return $mail->hasTo(
                    $order->customer_email,
                );
            },
        );
    },
);
