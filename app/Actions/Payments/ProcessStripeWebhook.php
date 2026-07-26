<?php

namespace App\Actions\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Stripe\Event;
use Throwable;

final class ProcessStripeWebhook
{
    /**
     * Persist and idempotently process one verified Stripe event.
     */
    public function execute(
        Event $event,
    ): void {
        $eventId = trim(
            (string) $event->id,
        );

        if ($eventId === '') {
            throw new RuntimeException(
                'Stripe event ID is missing.',
            );
        }

        $eventRecord =
            PaymentWebhookEvent::query()
                ->firstOrCreate(
                    [
                        'provider' => 'stripe',

                        'provider_event_id' => $eventId,
                    ],
                    [
                        'event_type' => (string) $event->type,

                        'payload' => $event->toArray(),

                        'received_at' => now(),
                    ],
                );

        if (
            $eventRecord->processed_at
                !== null
        ) {
            return;
        }

        try {
            DB::transaction(
                function () use (
                    $event,
                    $eventRecord,
                ): void {
                    $lockedEvent =
                        PaymentWebhookEvent::query()
                            ->whereKey(
                                $eventRecord->id,
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    if (
                        $lockedEvent->processed_at
                            !== null
                    ) {
                        return;
                    }

                    $this->processEvent(
                        $event,
                    );

                    $lockedEvent->forceFill([
                        'processed_at' => now(),
                        'failed_at' => null,
                        'failure_message' => null,
                    ])->save();
                },
                3,
            );
        } catch (Throwable $exception) {
            PaymentWebhookEvent::query()
                ->whereKey($eventRecord->id)
                ->update([
                    'failed_at' => now(),

                    'failure_message' => mb_substr(
                        $exception->getMessage(),
                        0,
                        2_000,
                    ),
                ]);

            throw $exception;
        }
    }

    /**
     * Route supported Stripe events to their local handlers.
     */
    private function processEvent(
        Event $event,
    ): void {
        $eventPayload =
            $event->toArray();

        $object =
            $eventPayload['data']['object']
                ?? [];

        if (! is_array($object)) {
            return;
        }

        match ((string) $event->type) {
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded' => $this->handleCheckoutSucceeded(
                $object,
            ),

            'checkout.session.async_payment_failed',
            'checkout.session.expired' => $this->handleCheckoutFailed(
                $object,
                (string) $event->type,
            ),

            'refund.created',
            'refund.updated' => $this->handleRefund(
                $object,
            ),

            default => null,
        };
    }

    /**
     * Mark a successfully paid Checkout Session as paid locally.
     *
     * @param  array<string, mixed>  $session
     */
    private function handleCheckoutSucceeded(
        array $session,
    ): void {
        if (
            ($session['payment_status'] ?? null)
                !== 'paid'
        ) {
            return;
        }

        $order =
            $this->findOrderFromSession(
                $session,
            );

        if (! $order instanceof Order) {
            return;
        }

        $this->assertSessionMatchesOrder(
            $session,
            $order,
        );

        $payment =
            $this->findPaymentFromSession(
                $session,
                $order,
            );

        if (! $payment instanceof Payment) {
            throw new RuntimeException(
                'Stripe payment record was not found.',
            );
        }

        if (
            $payment->status
                === PaymentStatus::Refunded
        ) {
            return;
        }

        $paidAt =
            $payment->paid_at
                ?? now();

        $payment->forceFill([
            'provider_checkout_session_id' => $this->identifier(
                $session['id'] ?? null,
            )
                ?? $payment
                    ->provider_checkout_session_id,

            'provider_payment_id' => $this->identifier(
                $session[
                    'payment_intent'
                ] ?? null,
            )
                ?? $payment
                    ->provider_payment_id,

            'status' => PaymentStatus::Paid,

            'paid_at' => $paidAt,

            'failed_at' => null,

            'failure_message' => null,
        ])->save();

        if (
            $order->payment_status
                !== PaymentStatus::Refunded
        ) {
            $order->forceFill([
                'payment_status' => PaymentStatus::Paid,

                'paid_at' => $order->paid_at
                        ?? $paidAt,
            ])->save();
        }
    }

    /**
     * Mark an unpaid or expired Checkout Session as failed.
     *
     * @param  array<string, mixed>  $session
     */
    private function handleCheckoutFailed(
        array $session,
        string $eventType,
    ): void {
        $order =
            $this->findOrderFromSession(
                $session,
            );

        if (! $order instanceof Order) {
            return;
        }

        $payment =
            $this->findPaymentFromSession(
                $session,
                $order,
            );

        if (! $payment instanceof Payment) {
            return;
        }

        if (
            in_array(
                $payment->status,
                [
                    PaymentStatus::Paid,
                    PaymentStatus::Refunded,
                ],
                true,
            )
        ) {
            return;
        }

        $message =
            $eventType
                === 'checkout.session.expired'
            ? 'The Stripe Checkout Session expired.'
            : 'Stripe reported that the payment failed.';

        $payment->forceFill([
            'status' => PaymentStatus::Failed,

            'failed_at' => now(),

            'failure_message' => $message,
        ])->save();

        if (
            ! in_array(
                $order->payment_status,
                [
                    PaymentStatus::Paid,
                    PaymentStatus::Refunded,
                ],
                true,
            )
        ) {
            $order->forceFill([
                'payment_status' => PaymentStatus::Failed,
            ])->save();
        }
    }

    /**
     * Mark a successful full refund against the associated payment.
     *
     * @param  array<string, mixed>  $refund
     */
    private function handleRefund(
        array $refund,
    ): void {
        if (
            ($refund['status'] ?? null)
                !== 'succeeded'
        ) {
            return;
        }

        $paymentIntentId =
            $this->identifier(
                $refund[
                    'payment_intent'
                ] ?? null,
            );

        if ($paymentIntentId === null) {
            return;
        }

        $payment = Payment::query()
            ->where('provider', 'stripe')
            ->where(
                'provider_payment_id',
                $paymentIntentId,
            )
            ->lockForUpdate()
            ->first();

        if (! $payment instanceof Payment) {
            return;
        }

        $refundAmount =
            (int) (
                $refund['amount']
                    ?? 0
            );

        // Partial refunds remain visible in Stripe but do not incorrectly
        // convert the local payment into a fully refunded state.
        if (
            $refundAmount
                < $payment->amount_cents
        ) {
            return;
        }

        $refundedAt =
            $payment->refunded_at
                ?? now();

        $payment->forceFill([
            'status' => PaymentStatus::Refunded,

            'refunded_at' => $refundedAt,
        ])->save();

        $payment->order()
            ->lockForUpdate()
            ->firstOrFail()
            ->forceFill([
                'payment_status' => PaymentStatus::Refunded,
            ])
            ->save();
    }

    /**
     * Locate and lock the local order referenced by a Checkout Session.
     *
     * @param  array<string, mixed>  $session
     */
    private function findOrderFromSession(
        array $session,
    ): ?Order {
        $metadata =
            $session['metadata']
                ?? [];

        $metadata =
            is_array($metadata)
            ? $metadata
            : [];

        $publicId =
            $this->identifier(
                $metadata[
                    'order_public_id'
                ] ?? null,
            )
            ?? $this->identifier(
                $session[
                    'client_reference_id'
                ] ?? null,
            );

        if ($publicId === null) {
            return null;
        }

        return Order::query()
            ->where(
                'public_id',
                $publicId,
            )
            ->lockForUpdate()
            ->first();
    }

    /**
     * Find the payment associated with a Checkout Session.
     *
     * @param  array<string, mixed>  $session
     */
    private function findPaymentFromSession(
        array $session,
        Order $order,
    ): ?Payment {
        $sessionId =
            $this->identifier(
                $session['id'] ?? null,
            );

        if ($sessionId !== null) {
            $payment = $order
                ->payments()
                ->where(
                    'provider_checkout_session_id',
                    $sessionId,
                )
                ->lockForUpdate()
                ->first();

            if ($payment instanceof Payment) {
                return $payment;
            }
        }

        return $order
            ->payments()
            ->where('provider', 'stripe')
            ->latest('id')
            ->lockForUpdate()
            ->first();
    }

    /**
     * Ensure Stripe's currency and amount match the order snapshot.
     *
     * @param  array<string, mixed>  $session
     */
    private function assertSessionMatchesOrder(
        array $session,
        Order $order,
    ): void {
        $amount =
            (int) (
                $session['amount_total']
                    ?? -1
            );

        $currency =
            strtoupper(
                (string) (
                    $session['currency']
                        ?? ''
                ),
            );

        if (
            $amount
                !== $order->grand_total_cents
            || $currency
                !== strtoupper(
                    $order->currency,
                )
        ) {
            throw new RuntimeException(
                'Stripe payment amount or currency does not match the order.',
            );
        }
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
}
