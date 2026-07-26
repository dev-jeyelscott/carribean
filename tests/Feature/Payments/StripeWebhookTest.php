<?php

use App\Actions\Orders\PlaceOrder;
use App\Enums\FulfillmentMethod;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\PaymentWebhookEvent;
use App\Models\SiteSetting;
use App\Support\Cart\SessionCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * Create a pending Stripe order for webhook feature tests.
 */
function createStripeWebhookTestOrder(): Order
{
    config([
        'services.stripe.secret' => 'sk_test_example',

        'services.stripe.webhook_secret' => 'whsec_test_example',
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

    $category =
        MenuCategory::query()->create([
            'name' => 'Stripe Test',
            'slug' => 'stripe-test-category',
            'description' => null,
            'sort_order' => 1,
            'is_visible' => true,
        ]);

    $menuItem =
        MenuItem::query()->create([
            'menu_category_id' => $category->id,

            'name' => 'Stripe Jerk Chicken',

            'slug' => 'stripe-jerk-chicken',

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

    return app(
        PlaceOrder::class,
    )->execute(
        $cart,
        null,
        [
            'name' => 'Stripe Customer',
            'email' => 'stripe@example.com',
            'phone' => '555-0110',
        ],
        FulfillmentMethod::Pickup,
        PaymentMethod::Stripe,
        null,
        null,
        (string) Str::uuid(),
    );
}

/**
 * Build a signed Stripe Checkout Session event payload.
 *
 * @param  array<string, mixed>  $overrides
 */
function stripeCheckoutEventPayload(
    Order $order,
    string $eventId,
    string $eventType,
    array $overrides = [],
): string {
    $session = array_replace(
        [
            'id' => 'cs_test_phase_seven',
            'object' => 'checkout.session',

            'client_reference_id' => $order->public_id,

            'metadata' => [
                'order_public_id' => $order->public_id,
            ],

            'amount_total' => $order->grand_total_cents,

            'currency' => 'usd',
            'payment_status' => 'paid',

            'payment_intent' => 'pi_test_phase_seven',
        ],
        $overrides,
    );

    return json_encode(
        [
            'id' => $eventId,
            'object' => 'event',
            'type' => $eventType,
            'created' => now()->timestamp,
            'data' => [
                'object' => $session,
            ],
        ],
        JSON_THROW_ON_ERROR,
    );
}

/**
 * Build a valid test Stripe-Signature header.
 */
function stripeWebhookTestSignature(
    string $payload,
): string {
    $timestamp = time();

    $signedPayload = sprintf(
        '%d.%s',
        $timestamp,
        $payload,
    );

    $signature = hash_hmac(
        'sha256',
        $signedPayload,
        'whsec_test_example',
    );

    return sprintf(
        't=%d,v1=%s',
        $timestamp,
        $signature,
    );
}

test(
    'verified checkout webhook marks order and payment paid',
    function (): void {
        $order =
            createStripeWebhookTestOrder();

        $payment =
            $order->payments()
                ->firstOrFail();

        $payment->update([
            'provider_checkout_session_id' => 'cs_test_phase_seven',
        ]);

        $payload =
            stripeCheckoutEventPayload(
                $order,
                'evt_test_checkout_paid',
                'checkout.session.completed',
            );

        $signature =
            stripeWebhookTestSignature(
                $payload,
            );

        $response = $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => $signature,

                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        );

        $response->assertOk();

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
                'pi_test_phase_seven',
            )
            ->paid_at->not->toBeNull();

        expect(
            PaymentWebhookEvent::query()
                ->where(
                    'provider_event_id',
                    'evt_test_checkout_paid',
                )
                ->firstOrFail()
                ->processed_at,
        )->not->toBeNull();
    },
);

test(
    'duplicate webhook delivery is processed once',
    function (): void {
        $order =
            createStripeWebhookTestOrder();

        $order->payments()
            ->firstOrFail()
            ->update([
                'provider_checkout_session_id' => 'cs_test_phase_seven',
            ]);

        $payload =
            stripeCheckoutEventPayload(
                $order,
                'evt_test_duplicate',
                'checkout.session.completed',
            );

        $signature =
            stripeWebhookTestSignature(
                $payload,
            );

        foreach (range(1, 2) as $attempt) {
            $this->call(
                'POST',
                route('webhooks.stripe'),
                [],
                [],
                [],
                [
                    'HTTP_STRIPE_SIGNATURE' => $signature,

                    'CONTENT_TYPE' => 'application/json',
                ],
                $payload,
            )->assertOk();
        }

        expect(
            PaymentWebhookEvent::query()
                ->where(
                    'provider_event_id',
                    'evt_test_duplicate',
                )
                ->count(),
        )->toBe(1);
    },
);

test(
    'invalid webhook signature is rejected',
    function (): void {
        config([
            'services.stripe.webhook_secret' => 'whsec_test_example',
        ]);

        $payload = json_encode(
            [
                'id' => 'evt_invalid',
                'object' => 'event',
                'type' => 'checkout.session.completed',
                'data' => [
                    'object' => [],
                ],
            ],
            JSON_THROW_ON_ERROR,
        );

        $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => 't=1,v1=invalid',

                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        )->assertStatus(400);

        expect(
            PaymentWebhookEvent::query()
                ->count(),
        )->toBe(0);
    },
);

test(
    'asynchronous payment failure marks pending payment failed',
    function (): void {
        $order =
            createStripeWebhookTestOrder();

        $payment =
            $order->payments()
                ->firstOrFail();

        $payment->update([
            'provider_checkout_session_id' => 'cs_test_phase_seven',
        ]);

        $payload =
            stripeCheckoutEventPayload(
                $order,
                'evt_test_checkout_failed',
                'checkout.session.async_payment_failed',
                [
                    'payment_status' => 'unpaid',
                ],
            );

        $signature =
            stripeWebhookTestSignature(
                $payload,
            );

        $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => $signature,

                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        )->assertOk();

        $order->refresh();
        $payment->refresh();

        expect($order->payment_status)
            ->toBe(
                PaymentStatus::Failed,
            );

        expect($payment)
            ->status->toBe(
                PaymentStatus::Failed,
            )
            ->failed_at->not->toBeNull();
    },
);

test(
    'successful full refund marks payment and order refunded',
    function (): void {
        $order =
            createStripeWebhookTestOrder();

        $payment =
            $order->payments()
                ->firstOrFail();

        $order->forceFill([
            'payment_status' => PaymentStatus::Paid,

            'paid_at' => now(),
        ])->save();

        $payment->forceFill([
            'provider_payment_id' => 'pi_test_refund',

            'status' => PaymentStatus::Paid,

            'paid_at' => now(),
        ])->save();

        $payload = json_encode(
            [
                'id' => 'evt_test_refund',
                'object' => 'event',
                'type' => 'refund.created',
                'created' => now()->timestamp,

                'data' => [
                    'object' => [
                        'id' => 're_test_refund',

                        'object' => 'refund',

                        'status' => 'succeeded',

                        'payment_intent' => 'pi_test_refund',

                        'amount' => $payment->amount_cents,
                    ],
                ],
            ],
            JSON_THROW_ON_ERROR,
        );

        $signature =
            stripeWebhookTestSignature(
                $payload,
            );

        $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => $signature,

                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        )->assertOk();

        $order->refresh();
        $payment->refresh();

        expect($order->payment_status)
            ->toBe(
                PaymentStatus::Refunded,
            );

        expect($payment)
            ->status->toBe(
                PaymentStatus::Refunded,
            )
            ->refunded_at->not->toBeNull();
    },
);

test(
    'stripe retry and cancel routes require valid signatures',
    function (): void {
        $order =
            createStripeWebhookTestOrder();

        $this->get(
            route(
                'checkout.stripe.cancel',
                $order,
            ),
        )->assertForbidden();

        $signedCancelUrl =
            URL::temporarySignedRoute(
                'checkout.stripe.cancel',
                now()->addMinutes(10),
                [
                    'order' => $order,
                ],
            );

        $this->get(
            $signedCancelUrl,
        )
            ->assertOk()
            ->assertSee(
                $order->order_number,
            );

        $this->post(
            route(
                'checkout.stripe.create',
                $order,
            ),
        )->assertForbidden();
    },
);
