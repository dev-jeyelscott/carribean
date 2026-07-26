<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

final class StripeCheckoutCancelController
{
    /**
     * Display a safe retry page after Stripe Checkout is cancelled.
     */
    public function __invoke(
        Order $order,
    ): View|RedirectResponse {
        abort_unless(
            $order->payment_method
                === PaymentMethod::Stripe,
            404,
        );

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
            return redirect()->to(
                URL::temporarySignedRoute(
                    'checkout.success',
                    now()->addDay(),
                    [
                        'order' => $order,
                    ],
                ),
            );
        }

        return view(
            'pages.checkout-stripe-cancel',
            [
                'order' => $order,

                'retryUrl' => URL::temporarySignedRoute(
                    'checkout.stripe.create',
                    now()->addMinutes(30),
                    [
                        'order' => $order,
                    ],
                ),
            ],
        );
    }
}
