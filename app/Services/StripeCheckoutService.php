<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\URL;
use LogicException;
use Stripe\Checkout\Session;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;

class StripeCheckoutService
{
    private StripeClient $stripe;

    /**
     * Create the Stripe API client from environment configuration.
     */
    public function __construct(
        ?StripeClient $stripe = null,
    ) {
        $this->stripe = $stripe
            ?? new StripeClient(
                $this->secretKey(),
            );
    }

    /**
     * Determine whether Stripe Checkout can be offered.
     */
    public static function isConfigured(): bool
    {
        $secret = config(
            'services.stripe.secret',
        );

        return is_string($secret)
            && trim($secret) !== '';
    }

    /**
     * Create or reuse a hosted Stripe Checkout Session.
     */
    public function createCheckoutSession(
        Order $order,
    ): Session {
        $this->assertOrderCanUseStripe(
            $order,
        );

        $payment = $this->resolvePaymentAttempt(
            $order,
        );

        $existingSession =
            $this->retrieveExistingSession(
                $payment,
            );

        if (
            $existingSession instanceof Session
            && in_array(
                $existingSession->status,
                ['open', 'complete'],
                true,
            )
        ) {
            return $existingSession;
        }

        if (
            $existingSession instanceof Session
            || $payment->status
                === PaymentStatus::Failed
        ) {
            $this->markAttemptFailed(
                $payment,
                'The previous Stripe Checkout Session is no longer active.',
            );

            $payment = $this->createPaymentAttempt(
                $order,
            );
        }

        $checkoutSession =
            $this->stripe
                ->checkout
                ->sessions
                ->create(
                    [
                        'mode' => 'payment',

                        'client_reference_id' => $order->public_id,

                        'customer_email' => $order->customer_email,

                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' => strtolower(
                                        $order->currency,
                                    ),

                                    'unit_amount' => $order->grand_total_cents,

                                    'product_data' => [
                                        'name' => sprintf(
                                            'Coast & Cay Order %s',
                                            $order->order_number,
                                        ),

                                        'description' => sprintf(
                                            '%s restaurant order',
                                            $order
                                                ->fulfillment_method
                                                ->label(),
                                        ),
                                    ],
                                ],

                                'quantity' => 1,
                            ],
                        ],

                        'metadata' => [
                            'order_public_id' => $order->public_id,

                            'payment_record_id' => (string) $payment->id,
                        ],

                        'payment_intent_data' => [
                            'metadata' => [
                                'order_public_id' => $order->public_id,

                                'payment_record_id' => (string) $payment->id,
                            ],
                        ],

                        'success_url' => $this->successUrl(
                            $order,
                        ),

                        'cancel_url' => $this->cancelUrl(
                            $order,
                        ),

                        'expires_at' => now()
                            ->addMinutes(30)
                            ->timestamp,

                        'submit_type' => 'pay',
                    ],
                    [
                        'idempotency_key' => sprintf(
                            'checkout-session:%s:%d',
                            $order->public_id,
                            $payment->id,
                        ),
                    ],
                );

        $payment->forceFill([
            'provider_checkout_session_id' => $checkoutSession->id,

            'failure_message' => null,
        ])->save();

        return $checkoutSession;
    }

    /**
     * Return the configured Stripe secret key.
     */
    private function secretKey(): string
    {
        $secret = config(
            'services.stripe.secret',
        );

        if (
            ! is_string($secret)
            || trim($secret) === ''
        ) {
            throw new LogicException(
                'Stripe is not configured.',
            );
        }

        return trim($secret);
    }

    /**
     * Ensure that this order represents a payable Stripe order.
     */
    private function assertOrderCanUseStripe(
        Order $order,
    ): void {
        if (
            $order->payment_method
                !== PaymentMethod::Stripe
        ) {
            throw new LogicException(
                'This order does not use Stripe.',
            );
        }

        if (
            in_array(
                $order->payment_status,
                [
                    PaymentStatus::Paid,
                    PaymentStatus::Refunded,
                ],
                true,
            )
        ) {
            throw new LogicException(
                'This order no longer requires payment.',
            );
        }

        if ($order->grand_total_cents <= 0) {
            throw new LogicException(
                'Stripe Checkout requires a positive order total.',
            );
        }
    }

    /**
     * Return the newest reusable Stripe payment attempt.
     */
    private function resolvePaymentAttempt(
        Order $order,
    ): Payment {
        $payment = $order
            ->payments()
            ->where('provider', 'stripe')
            ->latest('id')
            ->first();

        if (
            ! $payment instanceof Payment
            || $payment->status
                === PaymentStatus::Failed
        ) {
            return $this->createPaymentAttempt(
                $order,
            );
        }

        return $payment;
    }

    /**
     * Create a fresh pending Stripe payment attempt.
     */
    private function createPaymentAttempt(
        Order $order,
    ): Payment {
        return $order->payments()->create([
            'provider' => 'stripe',

            'payment_method' => PaymentMethod::Stripe,

            'status' => PaymentStatus::Pending,

            'amount_cents' => $order->grand_total_cents,

            'currency' => $order->currency,
        ]);
    }

    /**
     * Retrieve a previously created Checkout Session when available.
     */
    private function retrieveExistingSession(
        Payment $payment,
    ): ?Session {
        $sessionId =
            $payment
                ->provider_checkout_session_id;

        if (
            ! is_string($sessionId)
            || $sessionId === ''
        ) {
            return null;
        }

        try {
            return $this->stripe
                ->checkout
                ->sessions
                ->retrieve(
                    $sessionId,
                    [],
                );
        } catch (InvalidRequestException) {
            return null;
        }
    }

    /**
     * Mark an abandoned or unusable payment attempt as failed.
     */
    private function markAttemptFailed(
        Payment $payment,
        string $message,
    ): void {
        if (
            $payment->status
                !== PaymentStatus::Pending
        ) {
            return;
        }

        $payment->forceFill([
            'status' => PaymentStatus::Failed,

            'failed_at' => now(),

            'failure_message' => $message,
        ])->save();
    }

    /**
     * Build the signed local success URL for this order.
     */
    private function successUrl(
        Order $order,
    ): string {
        return URL::temporarySignedRoute(
            'checkout.success',
            now()->addDay(),
            [
                'order' => $order,
            ],
        );
    }

    /**
     * Build the signed cancellation and retry URL.
     */
    private function cancelUrl(
        Order $order,
    ): string {
        return URL::temporarySignedRoute(
            'checkout.stripe.cancel',
            now()->addDay(),
            [
                'order' => $order,
            ],
        );
    }
}
