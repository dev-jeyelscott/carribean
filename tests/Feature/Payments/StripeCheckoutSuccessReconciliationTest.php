<?php

use App\Actions\Orders\PlaceOrder;
use App\Actions\Payments\ReconcileStripeCheckoutPayment;
use App\Enums\FulfillmentMethod;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SiteSetting;
use App\Support\Cart\SessionCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;

uses(RefreshDatabase::class);

/**
 * Create a pending Stripe order with one associated payment attempt.
 *
 * @return array{order: Order, payment: Payment}
 */
function createStripeReconciliationOrder(): array
{
    config([
        'services.stripe.secret' => 'sk_test_example',
    ]);

    foreach ([
        'accepting_online_orders' => '1',
        'tax_rate_basis_points' => '825',
    ] as $key => $value) {
        SiteSetting::query()->updateOrCreate(
            [
                'key' => $key,
            ],
            [
                'value' => $value,
                'group' => 'ordering',
            ],
        );
    }

    $category = MenuCategory::query()->create([
        'name' => 'Reconciliation Test',
        'slug' => 'reconciliation-test-category',
        'description' => null,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $menuItem = MenuItem::query()->create([
        'menu_category_id' => $category->id,

        'name' => 'Reconciliation Jerk Chicken',

        'slug' => 'reconciliation-jerk-chicken',

        'description' => null,
        'price_cents' => 2_000,
        'sort_order' => 1,
        'is_visible' => true,
        'is_featured' => false,
        'is_available' => true,
        'is_purchasable' => true,
    ]);

    $cart = app(
        SessionCart::class,
    );

    $cart->clear();

    $cart->add(
        $menuItem->id,
        [],
        1,
    );

    $order = app(
        PlaceOrder::class,
    )->execute(
        $cart,
        null,
        [
            'name' => 'Stripe Reconciliation Customer',

            'email' => 'reconciliation@example.com',

            'phone' => '555-0199',
        ],
        FulfillmentMethod::Pickup,
        PaymentMethod::Stripe,
        null,
        null,
        (string) Str::uuid(),
    );

    $payment = $order
        ->payments()
        ->firstOrFail();

    $payment->forceFill([
        'provider_checkout_session_id' => 'cs_test_reconciliation',
    ])->save();

    return [
        'order' => $order,
        'payment' => $payment,
    ];
}

/**
 * Build a Stripe Checkout Session payload for local reconciliation tests.
 *
 * @return array<string, mixed>
 */
function stripeReconciliationSession(
    Order $order,
    array $overrides = [],
): array {
    return array_replace_recursive(
        [
            'id' => 'cs_test_reconciliation',

            'object' => 'checkout.session',

            'client_reference_id' => $order->public_id,

            'metadata' => [
                'order_public_id' => $order->public_id,
            ],

            'amount_total' => $order->grand_total_cents,

            'currency' => strtolower(
                $order->currency,
            ),

            'payment_status' => 'paid',

            'payment_intent' => 'pi_test_reconciliation',
        ],
        $overrides,
    );
}

test(
    'successful stripe session marks the order and payment paid',
    function (): void {
        [
            'order' => $order,
            'payment' => $payment,
        ] = createStripeReconciliationOrder();

        app(
            ReconcileStripeCheckoutPayment::class,
        )->synchronize(
            $order,
            $payment,
            stripeReconciliationSession(
                $order,
            ),
        );

        $order->refresh();
        $payment->refresh();

        expect($order)
            ->payment_status->toBe(
                PaymentStatus::Paid,
            )
            ->paid_at->not->toBeNull();

        expect($payment)
            ->status->toBe(
                PaymentStatus::Paid,
            )
            ->provider_payment_id->toBe(
                'pi_test_reconciliation',
            )
            ->paid_at->not->toBeNull()
            ->failed_at->toBeNull()
            ->failure_message->toBeNull();
    },
);

test(
    'unpaid stripe session keeps the order payment pending',
    function (): void {
        [
            'order' => $order,
            'payment' => $payment,
        ] = createStripeReconciliationOrder();

        app(
            ReconcileStripeCheckoutPayment::class,
        )->synchronize(
            $order,
            $payment,
            stripeReconciliationSession(
                $order,
                [
                    'payment_status' => 'unpaid',
                ],
            ),
        );

        expect($order->refresh()->payment_status)
            ->toBe(
                PaymentStatus::Pending,
            );

        expect($payment->refresh()->status)
            ->toBe(
                PaymentStatus::Pending,
            );
    },
);

test(
    'stripe session with a mismatched amount cannot mark the order paid',
    function (): void {
        [
            'order' => $order,
            'payment' => $payment,
        ] = createStripeReconciliationOrder();

        expect(
            fn () => app(
                ReconcileStripeCheckoutPayment::class,
            )->synchronize(
                $order,
                $payment,
                stripeReconciliationSession(
                    $order,
                    [
                        'amount_total' => $order
                            ->grand_total_cents + 1,
                    ],
                ),
            ),
        )->toThrow(
            RuntimeException::class,
            'The Stripe payment amount or currency does not match the order.',
        );

        expect($order->refresh()->payment_status)
            ->toBe(
                PaymentStatus::Pending,
            );

        expect($payment->refresh()->status)
            ->toBe(
                PaymentStatus::Pending,
            );
    },
);
