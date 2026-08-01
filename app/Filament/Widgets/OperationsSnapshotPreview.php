<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesDashboardPeriod;
use App\Support\Dashboard\DashboardAnalytics;
use Filament\Widgets\Widget;

class OperationsSnapshotPreview extends Widget
{
    use UsesDashboardPeriod;

    protected static ?int $sort = 7;

    protected static bool $isLazy = false;

    protected string $view =
        'filament.widgets.operations-snapshot-preview';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 3,
        'xl' => 3,
    ];

    /**
     * Return live workload and fulfillment information.
     *
     * @return array{
     *     periodLabel: string,
     *     insights: list<array{
     *         label: string,
     *         value: string,
     *         detail: string,
     *         icon: string,
     *         tone: string
     *     }>
     * }
     */
    protected function getViewData(): array
    {
        $period = $this->dashboardPeriod();

        $analytics = app(
            DashboardAnalytics::class,
        );

        $summary = $analytics->summary($period);

        $fulfillment = $analytics
            ->fulfillmentCounts($period);

        $pickupShare = $this->percentage(
            $fulfillment['pickup'],
            $fulfillment['total'],
        );

        $deliveryShare = $this->percentage(
            $fulfillment['delivery'],
            $fulfillment['total'],
        );

        return [
            'periodLabel' => $period->label(),

            'insights' => [
                [
                    'label' => 'Needs confirmation',
                    'value' => number_format(
                        $summary['pending_count'],
                    ),
                    'detail' => 'Orders awaiting staff review',
                    'icon' => 'heroicon-o-exclamation-circle',
                    'tone' => 'attention',
                ],
                [
                    'label' => 'Preparing now',
                    'value' => number_format(
                        $summary['preparing_count'],
                    ),
                    'detail' => 'Orders currently being prepared',
                    'icon' => 'heroicon-o-fire',
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Pickup share',
                    'value' => $pickupShare.'%',
                    'detail' => 'Selected-period fulfillment mix',
                    'icon' => 'heroicon-o-building-storefront',
                    'tone' => 'positive',
                ],
                [
                    'label' => 'Delivery share',
                    'value' => $deliveryShare.'%',
                    'detail' => 'Selected-period fulfillment mix',
                    'icon' => 'heroicon-o-truck',
                    'tone' => 'neutral',
                ],
            ],
        ];
    }

    /**
     * Safely calculate an integer percentage.
     */
    private function percentage(
        int $value,
        int $total,
    ): int {
        if ($total === 0) {
            return 0;
        }

        return (int) round(
            ($value / $total) * 100,
        );
    }
}
