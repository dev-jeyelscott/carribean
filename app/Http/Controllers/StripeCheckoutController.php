<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use LogicException;
use RuntimeException;
use Stripe\Exception\ApiErrorException;

final class StripeCheckoutController
{
    /**
     * Restart or resume hosted Stripe Checkout for a pending order.
     */
    public function __invoke(
        Order $order,
        StripeCheckoutService $stripeCheckout,
    ): RedirectResponse {
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
                $this->successUrl(
                    $order,
                ),
            );
        }

        try {
            $checkoutSession =
                $stripeCheckout
                    ->createCheckoutSession(
                        $order,
                    );

            if (
                $checkoutSession->status
                    === 'complete'
            ) {
                return redirect()->to(
                    $this->successUrl(
                        $order,
                    ),
                );
            }

            $checkoutUrl =
                $checkoutSession->url;

            if (
                ! is_string($checkoutUrl)
                || $checkoutUrl === ''
            ) {
                throw new RuntimeException(
                    'Stripe did not return a Checkout URL.',
                );
            }

            return redirect()->away(
                $checkoutUrl,
                303,
            );
        } catch (
            ApiErrorException
            |LogicException
            |RuntimeException
                $exception
        ) {
            report($exception);

            return redirect()
                ->to(
                    $this->cancelUrl(
                        $order,
                    ),
                )
                ->with(
                    'stripe_error',
                    'Online payment is temporarily unavailable. Please retry.',
                );
        }
    }

    /**
     * Build the signed order-success URL.
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
     * Build the signed payment-cancellation URL.
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
