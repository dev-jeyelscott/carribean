<?php

namespace App\Support\Orders;

use App\Enums\OrderStatus;
use App\Mail\Orders\AdminOrderAlert;
use App\Mail\Orders\CustomerOrderUpdate;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class OrderNotificationDispatcher
{
    /**
     * Queue the initial customer order-received email.
     */
    public function queueOrderReceived(Order $order): bool
    {
        return $this->queueCustomerUpdate(
            order: $order,
            subject: sprintf(
                'We received order %s',
                $this->orderNumber($order),
            ),
            headline: 'Your order has been received',
            summary: 'Our team will review your order and confirm it as soon as possible.',
        );
    }

    /**
     * Notify restaurant staff that a new order requires review.
     */
    public function queueNewOrderAlert(Order $order): bool
    {
        return $this->queueAdminAlert(
            order: $order,
            subject: sprintf(
                'New order %s requires review',
                $this->orderNumber($order),
            ),
            headline: 'New order received',
            summary: 'A new customer order is waiting for restaurant confirmation.',
        );
    }

    /**
     * Queue a customer payment-confirmation email.
     */
    public function queuePaymentConfirmed(Order $order): bool
    {
        return $this->queueCustomerUpdate(
            order: $order,
            subject: sprintf(
                'Payment confirmed for order %s',
                $this->orderNumber($order),
            ),
            headline: 'Your payment is confirmed',
            summary: 'Your online payment was received successfully. The order is awaiting restaurant confirmation.',
        );
    }

    /**
     * Notify restaurant staff that an order payment failed.
     */
    public function queuePaymentFailureAlert(Order $order): bool
    {
        return $this->queueAdminAlert(
            order: $order,
            subject: sprintf(
                'Payment requires attention for order %s',
                $this->orderNumber($order),
            ),
            headline: 'Order payment requires attention',
            summary: 'The payment provider reported that this order was not paid successfully.',
        );
    }

    /**
     * Queue the customer notification associated with a lifecycle transition.
     */
    public function queueLifecycleUpdate(Order $order): bool
    {
        $copy = $this->automaticLifecycleCopy($order->status);

        if ($copy === null) {
            return false;
        }

        return $this->queueCustomerUpdate(
            order: $order,
            subject: sprintf(
                '%s — order %s',
                $copy['subject'],
                $this->orderNumber($order),
            ),
            headline: $copy['headline'],
            summary: $copy['summary'],
        );
    }

    /**
     * Resend a useful customer update for the order's current state.
     */
    public function resendCurrentCustomerUpdate(Order $order): bool
    {
        if ($order->status === OrderStatus::PendingConfirmation) {
            return $this->queueOrderReceived($order);
        }

        $copy = $this->currentStatusCopy($order->status);

        return $this->queueCustomerUpdate(
            order: $order,
            subject: sprintf(
                '%s — order %s',
                $copy['subject'],
                $this->orderNumber($order),
            ),
            headline: $copy['headline'],
            summary: $copy['summary'],
        );
    }

    /**
     * Queue one customer-facing order email safely.
     */
    private function queueCustomerUpdate(
        Order $order,
        string $subject,
        string $headline,
        string $summary,
    ): bool {
        $recipient = trim($order->customer_email);

        if ($recipient === '') {
            Log::warning(
                'Customer order email was skipped because no recipient was available.',
                [
                    'order_id' => $order->id,
                ],
            );

            return false;
        }

        try {
            Mail::to($recipient)->queue(
                new CustomerOrderUpdate(
                    order: $this->loadOrder($order),
                    subjectLine: $subject,
                    headline: $headline,
                    summary: $summary,
                ),
            );

            return true;
        } catch (Throwable $exception) {
            Log::error(
                'Customer order email could not be queued.',
                [
                    'order_id' => $order->id,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ],
            );

            return false;
        }
    }

    /**
     * Queue one administrator-facing order email safely.
     */
    private function queueAdminAlert(
        Order $order,
        string $subject,
        string $headline,
        string $summary,
    ): bool {
        $recipient = config('mail.inquiries_to');

        if (! is_string($recipient) || blank($recipient)) {
            Log::warning(
                'Administrator order email was skipped because no recipient was configured.',
                [
                    'order_id' => $order->id,
                ],
            );

            return false;
        }

        try {
            Mail::to($recipient)->queue(
                new AdminOrderAlert(
                    order: $this->loadOrder($order),
                    subjectLine: $subject,
                    headline: $headline,
                    summary: $summary,
                ),
            );

            return true;
        } catch (Throwable $exception) {
            Log::error(
                'Administrator order email could not be queued.',
                [
                    'order_id' => $order->id,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ],
            );

            return false;
        }
    }

    /**
     * Load order relationships required by all email templates.
     */
    private function loadOrder(Order $order): Order
    {
        return $order->fresh([
            'items',
            'addresses',
            'payments',
        ]) ?? $order->loadMissing([
            'items',
            'addresses',
            'payments',
        ]);
    }

    /**
     * Return automatic email copy for customer-visible lifecycle events.
     *
     * @return array{subject: string, headline: string, summary: string}|null
     */
    private function automaticLifecycleCopy(
        OrderStatus $status,
    ): ?array {
        return match ($status) {
            OrderStatus::Confirmed => [
                'subject' => 'Order confirmed',
                'headline' => 'Your order is confirmed',
                'summary' => 'The restaurant accepted your order and will begin preparing it shortly.',
            ],

            OrderStatus::Rejected => [
                'subject' => 'Order could not be accepted',
                'headline' => 'Your order could not be accepted',
                'summary' => 'The restaurant was unable to accept this order. Please contact the restaurant if you need additional information.',
            ],

            OrderStatus::ReadyForPickup => [
                'subject' => 'Order ready for pickup',
                'headline' => 'Your order is ready for pickup',
                'summary' => 'Your food is ready. Please collect it using the pickup information shown below.',
            ],

            OrderStatus::OutForDelivery => [
                'subject' => 'Order out for delivery',
                'headline' => 'Your order is on the way',
                'summary' => 'The restaurant has marked your order as out for local delivery.',
            ],

            OrderStatus::Delivered => [
                'subject' => 'Order delivered',
                'headline' => 'Your order was delivered',
                'summary' => 'The restaurant has marked your order as delivered.',
            ],

            OrderStatus::Cancelled => [
                'subject' => 'Order cancelled',
                'headline' => 'Your order was cancelled',
                'summary' => 'This order has been cancelled. Please contact the restaurant if you need further assistance.',
            ],

            default => null,
        };
    }

    /**
     * Return copy suitable for manually resending the current status.
     *
     * @return array{subject: string, headline: string, summary: string}
     */
    private function currentStatusCopy(
        OrderStatus $status,
    ): array {
        return $this->automaticLifecycleCopy($status)
            ?? match ($status) {
                OrderStatus::Preparing => [
                    'subject' => 'Order being prepared',
                    'headline' => 'Your order is being prepared',
                    'summary' => 'The restaurant is currently preparing your food.',
                ],

                OrderStatus::PickedUp => [
                    'subject' => 'Order picked up',
                    'headline' => 'Your pickup was recorded',
                    'summary' => 'The restaurant has marked your order as collected.',
                ],

                OrderStatus::Completed => [
                    'subject' => 'Order completed',
                    'headline' => 'Thank you for your order',
                    'summary' => 'Your order is complete. We appreciate your business.',
                ],

                default => [
                    'subject' => 'Order update',
                    'headline' => 'There is an update to your order',
                    'summary' => sprintf(
                        'The current order status is %s.',
                        $status->label(),
                    ),
                ],
            };
    }

    /**
     * Return a stable display number for email subjects.
     */
    private function orderNumber(Order $order): string
    {
        return $order->order_number
            ?? $order->public_id;
    }
}
