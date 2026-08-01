<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Widgets\Concerns\UsesDashboardPeriod;
use App\Models\Order;
use App\Support\Dashboard\DashboardAnalytics;
use Filament\Widgets\Widget;

class RecentOrdersPreview extends Widget
{
    use UsesDashboardPeriod;

    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected string $view =
        'filament.widgets.recent-orders-preview';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 6,
        'xl' => 6,
    ];

    /**
     * Return the five latest matching orders.
     *
     * @return array{
     *     periodLabel: string,
     *     orders: list<array{
     *         number: string,
     *         customer: string,
     *         fulfillment: string,
     *         total: string,
     *         status: string,
     *         status_tone: string,
     *         time: string,
     *         url: string
     *     }>
     * }
     */
    protected function getViewData(): array
    {
        $period = $this->dashboardPeriod();

        $orders = app(
            DashboardAnalytics::class,
        )->recentOrders($period);

        return [
            'periodLabel' => $period->label(),

            'orders' => $orders
                ->map(
                    fn (Order $order): array => [
                        'number' => $order->order_number
                            ?? 'Order #'.$order->id,
                        'customer' => $order->customer_name,
                        'fulfillment' => $order
                            ->fulfillment_method
                            ->label(),
                        'total' => $this->formatUsd(
                            $order->grand_total_cents,
                        ),
                        'status' => $order->status->label(),
                        'status_tone' => $this->statusTone(
                            $order->status,
                        ),
                        'time' => $order->placed_at->isToday()
                                ? $order
                                    ->placed_at
                                    ->format('g:i A')
                                : $order
                                    ->placed_at
                                    ->format('M j, g:i A'),
                        'url' => OrderResource::getUrl(
                            'view',
                            [
                                'record' => $order,
                            ],
                        ),
                    ],
                )
                ->values()
                ->all(),
        ];
    }

    /**
     * Format cents for the order table.
     */
    private function formatUsd(int $cents): string
    {
        return '$'.number_format(
            $cents / 100,
            2,
        );
    }

    /**
     * Map domain statuses to the existing dashboard status treatments.
     */
    private function statusTone(
        OrderStatus $status,
    ): string {
        return match ($status) {
            OrderStatus::Completed,
            OrderStatus::Delivered,
            OrderStatus::PickedUp => 'success',

            OrderStatus::Confirmed,
            OrderStatus::Preparing,
            OrderStatus::ReadyForPickup,
            OrderStatus::OutForDelivery => 'warning',

            OrderStatus::PendingConfirmation,
            OrderStatus::Rejected,
            OrderStatus::Cancelled => 'attention',
        };
    }
}
