<?php

namespace App\Observers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Support\Orders\OrderNotificationDispatcher;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final readonly class OrderObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Receive the focused order-notification dispatcher.
     */
    public function __construct(
        private OrderNotificationDispatcher $notifications,
    ) {}

    /**
     * Queue initial customer and restaurant emails after order creation.
     */
    public function created(Order $order): void
    {
        $currentOrder = $order->refresh();

        $this->notifications->queueOrderReceived(
            $currentOrder,
        );

        $this->notifications->queueNewOrderAlert(
            $currentOrder,
        );
    }

    /**
     * Queue payment or lifecycle messages after meaningful updates.
     */
    public function updated(Order $order): void
    {
        $paymentStatusChanged =
            $order->wasChanged('payment_status');

        $orderStatusChanged =
            $order->wasChanged('status');

        $paymentStatus =
            $order->payment_status;

        $currentOrder = $order->refresh();

        if (
            $paymentStatusChanged
            && $paymentStatus === PaymentStatus::Paid
        ) {
            $this->notifications->queuePaymentConfirmed(
                $currentOrder,
            );
        }

        if (
            $paymentStatusChanged
            && $paymentStatus === PaymentStatus::Failed
        ) {
            $this->notifications->queuePaymentFailureAlert(
                $currentOrder,
            );
        }

        if ($orderStatusChanged) {
            $this->notifications->queueLifecycleUpdate(
                $currentOrder,
            );
        }
    }
}
