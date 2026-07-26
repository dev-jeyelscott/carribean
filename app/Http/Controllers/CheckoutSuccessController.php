<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;

final class CheckoutSuccessController
{
    /**
     * Display the newly placed order through a signed URL.
     */
    public function __invoke(
        Order $order,
    ): View {
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
     * Return the correct authenticated or guest tracking destination.
     */
    private function trackingUrl(Order $order): string
    {
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
