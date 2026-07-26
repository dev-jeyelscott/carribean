<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;

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
            ],
        );
    }
}
