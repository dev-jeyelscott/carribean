<?php

namespace App\Actions\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class MarkCashPaymentPaid
{
    /**
     * Atomically mark an eligible cash order and payment as paid.
     */
    public function execute(Order $order): Order
    {
        return DB::transaction(
            function () use ($order): Order {
                $lockedOrder = Order::query()
                    ->whereKey($order->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->assertOrderMayBePaid(
                    $lockedOrder,
                );

                if (
                    $lockedOrder->payment_status
                    === PaymentStatus::Paid
                ) {
                    return $this->loadOrder(
                        $lockedOrder,
                    );
                }

                $payment = $lockedOrder
                    ->payments()
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                if (! $payment instanceof Payment) {
                    throw ValidationException::withMessages([
                        'payment' => 'This order does not have a payment record.',
                    ]);
                }

                if (! $payment->payment_method->isCash()) {
                    throw ValidationException::withMessages([
                        'payment' => 'Only cash payments may be marked paid manually.',
                    ]);
                }

                if (
                    $payment->status
                    === PaymentStatus::Refunded
                ) {
                    throw ValidationException::withMessages([
                        'payment' => 'A refunded payment cannot be marked paid.',
                    ]);
                }

                $paidAt =
                    $payment->paid_at
                    ?? now();

                $payment->forceFill([
                    'status' => PaymentStatus::Paid,
                    'paid_at' => $paidAt,
                    'failed_at' => null,
                    'failure_message' => null,
                ])->save();

                $lockedOrder->forceFill([
                    'payment_status' => PaymentStatus::Paid,
                    'paid_at' => $lockedOrder->paid_at
                        ?? $paidAt,
                ])->save();

                return $this->loadOrder(
                    $lockedOrder,
                );
            },
            3,
        );
    }

    /**
     * Reject payment recording for Stripe or invalid terminal orders.
     */
    private function assertOrderMayBePaid(
        Order $order,
    ): void {
        if (! $order->payment_method->isCash()) {
            throw ValidationException::withMessages([
                'payment' => 'Stripe payments are updated only through verified webhooks.',
            ]);
        }

        if (
            in_array(
                $order->status,
                [
                    OrderStatus::Rejected,
                    OrderStatus::Cancelled,
                ],
                true,
            )
        ) {
            throw ValidationException::withMessages([
                'payment' => 'A rejected or cancelled order cannot collect a cash payment.',
            ]);
        }

        if (
            $order->payment_status
            === PaymentStatus::Refunded
        ) {
            throw ValidationException::withMessages([
                'payment' => 'A refunded order cannot be marked paid.',
            ]);
        }
    }

    /**
     * Reload the order and payment records after the transaction.
     */
    private function loadOrder(Order $order): Order
    {
        return $order->fresh([
            'payments',
        ]) ?? $order;
    }
}
