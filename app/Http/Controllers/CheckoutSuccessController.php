<?php

namespace App\Http\Controllers;

use App\Actions\Payments\ReconcileStripeCheckoutPayment;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;
use Throwable;

final class CheckoutSuccessController
{
    /**
     * Display the newly placed order and reconcile a successful Stripe payment
     * when the webhook has not updated the local records yet.
     */
    public function __invoke(
        Order $order,
        ReconcileStripeCheckoutPayment $reconcileStripePayment,
    ): View {
        $this->reconcileOnlinePayment(
            $order,
            $reconcileStripePayment,
        );

        $order->refresh();

        $order->load([
            'items',
            'addresses',
            'payments',
        ]);

        return view(
            'pages.checkout-success',
            [
                'order' => $order,

                'trackingUrl' => $this->trackingUrl(
                    $order,
                ),
            ],
        );
    }

    /**
     * Safely reconcile a pending Stripe payment without preventing the customer
     * from opening the success page when Stripe is temporarily unavailable.
     */
    private function reconcileOnlinePayment(
        Order $order,
        ReconcileStripeCheckoutPayment $reconcileStripePayment,
    ): void {
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

        try {
            $reconcileStripePayment->execute(
                $order,
            );
        } catch (Throwable $exception) {
            /*
             * The verified Stripe webhook remains the primary payment update
             * mechanism. Report reconciliation failures without blocking the
             * customer's order confirmation page.
             */
            report($exception);
        }
    }

    /**
     * Return the correct authenticated or guest tracking destination.
     */
    private function trackingUrl(
        Order $order,
    ): string {
        if ($order->user_id !== null) {
            return route(
                'account.orders.show',
                [
                    'order' => $order,
                ],
            );
        }

        return URL::temporarySignedRoute(
            'guest.orders.show',
            now()->addDays(30),
            [
                'order' => $order,
            ],
        );
    }
}
