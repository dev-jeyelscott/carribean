<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesDashboardPeriod;
use App\Support\Dashboard\DashboardAnalytics;
use Filament\Widgets\Widget;

class TopSellingItemsPreview extends Widget
{
    use UsesDashboardPeriod;

    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected string $view =
        'filament.widgets.top-selling-items-preview';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 3,
        'xl' => 3,
    ];

    /**
     * Return top-selling immutable order-item snapshots.
     *
     * @return array{
     *     periodLabel: string,
     *     items: list<array{
     *         rank: string,
     *         name: string,
     *         orders: string,
     *         revenue: string,
     *         trend: string
     *     }>
     * }
     */
    protected function getViewData(): array
    {
        $period = $this->dashboardPeriod();

        $items = app(
            DashboardAnalytics::class,
        )->topSellingItems($period);

        /**
         * Build the presentation rows through sequential appends so the
         * resulting array remains an explicit PHPStan list.
         *
         * @var list<array{
         *     rank: string,
         *     name: string,
         *     orders: string,
         *     revenue: string,
         *     trend: string
         * }> $rankedItems
         */
        $rankedItems = [];

        foreach ($items as $index => $item) {
            $rankedItems[] = [
                'rank' => str_pad(
                    (string) ($index + 1),
                    2,
                    '0',
                    STR_PAD_LEFT,
                ),

                'name' => $item['name'],

                'orders' => sprintf(
                    '%s sold',
                    number_format(
                        $item['quantity_sold'],
                    ),
                ),

                'revenue' => $this->formatUsd(
                    $item['revenue_cents'],
                ),

                'trend' => $this->trendLabel(
                    $item['change_percent'],
                ),
            ];
        }

        return [
            'periodLabel' => $period->label(),
            'items' => $rankedItems,
        ];
    }

    /**
     * Format cents for ranking presentation.
     */
    private function formatUsd(int $cents): string
    {
        return '$'.number_format(
            $cents / 100,
            2,
        );
    }

    /**
     * Format the quantity change against the previous period.
     */
    private function trendLabel(
        ?float $percentage,
    ): string {
        if ($percentage === null) {
            return 'New';
        }

        if ($percentage > 0) {
            return sprintf(
                '↑ %s%%',
                number_format($percentage, 1),
            );
        }

        if ($percentage < 0) {
            return sprintf(
                '↓ %s%%',
                number_format(
                    abs($percentage),
                    1,
                ),
            );
        }

        return 'No change';
    }
}
