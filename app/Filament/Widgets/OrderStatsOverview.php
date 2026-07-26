<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 10;

    protected ?string $pollingInterval = null;

    /**
     * Return today's operational order metrics.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $ordersToday = Order::query()
            ->whereDate(
                'placed_at',
                today(),
            )
            ->count();

        $revenueTodayCents = (int) Order::query()
            ->whereDate(
                'placed_at',
                today(),
            )
            ->where(
                'payment_status',
                PaymentStatus::Paid,
            )
            ->sum('grand_total_cents');

        $pendingConfirmation = Order::query()
            ->where(
                'status',
                OrderStatus::PendingConfirmation,
            )
            ->count();

        $preparing = Order::query()
            ->where(
                'status',
                OrderStatus::Preparing,
            )
            ->count();

        return [
            Stat::make(
                'Orders today',
                (string) $ordersToday,
            )
                ->description('Orders placed since midnight')
                ->descriptionIcon('heroicon-m-receipt-percent'),

            Stat::make(
                'Paid revenue today',
                Money::formatUsd(
                    $revenueTodayCents,
                ) ?? '$0.00',
            )
                ->description('Paid orders placed today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(
                'Pending confirmation',
                (string) $pendingConfirmation,
            )
                ->description('Orders requiring staff review')
                ->descriptionIcon('heroicon-m-clock')
                ->color(
                    $pendingConfirmation > 0
                        ? 'warning'
                        : 'success',
                ),

            Stat::make(
                'Preparing',
                (string) $preparing,
            )
                ->description('Orders currently in preparation')
                ->descriptionIcon('heroicon-m-fire')
                ->color('primary'),
        ];
    }
}
