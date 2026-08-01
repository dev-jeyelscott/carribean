<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class RecentOrdersPreview extends Widget
{
    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.recent-orders-preview';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 6,
        'xl' => 6,
    ];

    /**
     * Provide sample recent-order rows without querying operational models.
     *
     * @return array{
     *     orders: list<array{
     *         number: string,
     *         customer: string,
     *         fulfillment: string,
     *         total: string,
     *         status: string,
     *         status_tone: string,
     *         time: string
     *     }>
     * }
     */
    protected function getViewData(): array
    {
        return [
            'orders' => [
                [
                    'number' => '#ORD-10256',
                    'customer' => 'Maya Thompson',
                    'fulfillment' => 'Delivery',
                    'total' => '$78.90',
                    'status' => 'Completed',
                    'status_tone' => 'success',
                    'time' => '10:24 AM',
                ],
                [
                    'number' => '#ORD-10255',
                    'customer' => 'Daniel Ruiz',
                    'fulfillment' => 'Pickup',
                    'total' => '$45.50',
                    'status' => 'Preparing',
                    'status_tone' => 'warning',
                    'time' => '9:15 AM',
                ],
                [
                    'number' => '#ORD-10254',
                    'customer' => 'Sophia Williams',
                    'fulfillment' => 'Delivery',
                    'total' => '$92.30',
                    'status' => 'Completed',
                    'status_tone' => 'success',
                    'time' => '8:47 AM',
                ],
                [
                    'number' => '#ORD-10253',
                    'customer' => 'Liam Carter',
                    'fulfillment' => 'Pickup',
                    'total' => '$37.80',
                    'status' => 'Pending',
                    'status_tone' => 'attention',
                    'time' => '8:22 AM',
                ],
                [
                    'number' => '#ORD-10252',
                    'customer' => 'Isabella Martinez',
                    'fulfillment' => 'Delivery',
                    'total' => '$65.40',
                    'status' => 'Completed',
                    'status_tone' => 'success',
                    'time' => 'Yesterday',
                ],
            ],
        ];
    }
}
