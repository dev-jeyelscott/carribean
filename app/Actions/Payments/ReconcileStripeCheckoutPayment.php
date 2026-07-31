<?php

namespace App\Actions\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;
use Stripe\StripeClient;

final class ReconcileStripeCheckoutPayment
{
    private ?StripeClient $stripe = null;

    /**
     * Retrieve the stored Stripe Checkout Session and reconcile a successful
     * online payment when the webhook has not updated the order yet.
     */
    public function execute(Order $order): void
    {
        if (
            $order->payment_method
                !== PaymentMethod::Stripe
        ) {
            return;
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
            return;
        }

        $payment = $order
            ->payments()
            ->reorder()
            ->where('provider', 'stripe')
            ->whereNotNull(
                'provider_checkout_session_id',
            )
            ->latest('id')
            ->first();

        if (! $payment instanceof Payment) {
            return;
        }

        $sessionId = trim(
            (string) $payment
                ->provider_checkout_session_id,
        );

        if ($sessionId === '') {
            return;
        }

        $session = $this->stripe()
            ->checkout
            ->sessions
            ->retrieve(
                $sessionId,
                [],
            );

        $this->synchronize(
            $order,
            $payment,
            $session->toArray(),
        );
    }

    /**
     * Synchronize one verified paid Checkout Session with the local payment
     * and order records.
     *
     * This method is public so the synchronization behavior can be tested
     * without making a real Stripe API request.
     *
     * @param  array<string, mixed>  $session
     */
    public function synchronize(
        Order $order,
        Payment $payment,
        array $session,
    ): void {
        if (
            ($session['payment_status'] ?? null)
                !== 'paid'
        ) {
            return;
        }

        $this->assertSessionMatchesRecords(
            $order,
            $payment,
            $session,
        );

        DB::transaction(
            function () use (
                $order,
                $payment,
                $session,
            ): void {
                $lockedOrder = Order::query()
                    ->whereKey($order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedPayment = Payment::query()
                    ->whereKey($payment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->assertSessionMatchesRecords(
                    $lockedOrder,
                    $lockedPayment,
                    $session,
                );

                if (
                    $lockedOrder->payment_status
                        === PaymentStatus::Refunded
                    || $lockedPayment->status
                        === PaymentStatus::Refunded
                ) {
                    return;
                }

                $paidAt = $lockedPayment->paid_at
                    ?? $lockedOrder->paid_at
                    ?? now();

                $lockedPayment->forceFill([
                    'provider_payment_id' => $this->identifier(
                        $session['payment_intent']
                            ?? null,
                    )
                        ?? $lockedPayment
                            ->provider_payment_id,

                    'status' => PaymentStatus::Paid,

                    'paid_at' => $paidAt,

                    'failed_at' => null,

                    'failure_message' => null,
                ])->save();

                $lockedOrder->forceFill([
                    'payment_status' => PaymentStatus::Paid,

                    'paid_at' => $paidAt,
                ])->save();
            },
            3,
        );
    }

    /**
     * Confirm that Stripe's Checkout Session belongs to the expected local
     * order and payment before changing any payment state.
     *
     * @param  array<string, mixed>  $session
     */
    private function assertSessionMatchesRecords(
        Order $order,
        Payment $payment,
        array $session,
    ): void {
        if ($payment->order_id !== $order->id) {
            throw new RuntimeException(
                'The Stripe payment does not belong to the expected order.',
            );
        }

        if ($payment->provider !== 'stripe') {
            throw new RuntimeException(
                'The payment is not a Stripe payment.',
            );
        }

        $sessionId = $this->identifier(
            $session['id'] ?? null,
        );

        if (
            $sessionId === null
            || $sessionId
                !== $payment
                    ->provider_checkout_session_id
        ) {
            throw new RuntimeException(
                'The Stripe Checkout Session does not match the payment record.',
            );
        }

        $sessionOrderId = $this->sessionOrderId(
            $session,
        );

        if (
            $sessionOrderId === null
            || $sessionOrderId
                !== $order->public_id
        ) {
            throw new RuntimeException(
                'The Stripe Checkout Session does not match the order.',
            );
        }

        $amount = (int) (
            $session['amount_total']
                ?? -1
        );

        $currency = strtoupper(
            (string) (
                $session['currency']
                    ?? ''
            ),
        );

        if (
            $amount !== $order->grand_total_cents
            || $amount !== $payment->amount_cents
            || $currency
                !== strtoupper($order->currency)
            || $currency
                !== strtoupper($payment->currency)
        ) {
            throw new RuntimeException(
                'The Stripe payment amount or currency does not match the order.',
            );
        }
    }

    /**
     * Resolve the local public order ID stored in Stripe metadata or the
     * Checkout Session client reference.
     *
     * @param  array<string, mixed>  $session
     */
    private function sessionOrderId(
        array $session,
    ): ?string {
        $metadata = $session['metadata']
            ?? [];

        $metadata = is_array($metadata)
            ? $metadata
            : [];

        return $this->identifier(
            $metadata['order_public_id']
                ?? null,
        )
            ?? $this->identifier(
                $session['client_reference_id']
                    ?? null,
            );
    }

    /**
     * Normalize a Stripe string or expandable object identifier.
     */
    private function identifier(
        mixed $value,
    ): ?string {
        if (is_string($value)) {
            $value = trim($value);

            return $value !== ''
                ? $value
                : null;
        }

        if (
            is_array($value)
            && is_string(
                $value['id'] ?? null,
            )
        ) {
            $identifier = trim(
                $value['id'],
            );

            return $identifier !== ''
                ? $identifier
                : null;
        }

        return null;
    }

    /**
     * Lazily create the Stripe client only when a pending online payment needs
     * reconciliation.
     */
    private function stripe(): StripeClient
    {
        if ($this->stripe instanceof StripeClient) {
            return $this->stripe;
        }

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

        $this->stripe = new StripeClient(
            trim($secret),
        );

        return $this->stripe;
    }
}
