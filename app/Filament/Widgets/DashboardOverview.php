<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesDashboardPeriod;
use App\Support\Dashboard\DashboardAnalytics;
use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\Widget;

class DashboardOverview extends Widget
{
    use UsesDashboardPeriod;

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected string $view =
        'filament.widgets.dashboard-overview';

    protected int|string|array $columnSpan = 'full';

    /**
     * Return live KPI values from existing transactional order data.
     *
     * @return array{
     *     periodLabel: string,
     *     metrics: list<array{
     *         label: string,
     *         value: string,
     *         change: string,
     *         change_tone: string,
     *         comparison: string,
     *         icon: string
     *     }>
     * }
     */
    protected function getViewData(): array
    {
        $period = $this->dashboardPeriod();

        $overview = app(
            DashboardAnalytics::class,
        )->overview($period);

        $current = $overview['current'];
        $previous = $overview['previous'];

        $revenueTrend = $this->trend(
            $current['revenue_cents'],
            $previous['revenue_cents'],
        );

        $orderTrend = $this->trend(
            $current['orders_count'],
            $previous['orders_count'],
        );

        $averageOrderValueTrend = $this->trend(
            $current['average_order_value_cents'],
            $previous['average_order_value_cents'],
        );

        $comparison = sprintf(
            'Compared with %s',
            $period->previous()->label(),
        );

        return [
            'periodLabel' => $period->label(),

            'metrics' => [
                [
                    'label' => $this->periodLabel(
                        'Revenue',
                        $period,
                    ),
                    'value' => $this->formatUsd(
                        $current['revenue_cents'],
                    ),
                    'change' => $revenueTrend['label'],
                    'change_tone' => $revenueTrend['tone'],
                    'comparison' => $comparison,
                    'icon' => 'heroicon-o-currency-dollar',
                ],
                [
                    'label' => $this->periodLabel(
                        'Orders',
                        $period,
                    ),
                    'value' => number_format(
                        $current['orders_count'],
                    ),
                    'change' => $orderTrend['label'],
                    'change_tone' => $orderTrend['tone'],
                    'comparison' => $comparison,
                    'icon' => 'heroicon-o-shopping-bag',
                ],
                [
                    'label' => 'Pending confirmation',
                    'value' => number_format(
                        $current['pending_count'],
                    ),
                    'change' => $current['pending_count'] > 0
                            ? 'Needs review'
                            : 'Clear',
                    'change_tone' => $current['pending_count'] > 0
                            ? 'attention'
                            : 'positive',
                    'comparison' => sprintf(
                        'Orders placed %s',
                        $period->label(),
                    ),
                    'icon' => 'heroicon-o-clock',
                ],
                [
                    'label' => 'Preparing orders',
                    'value' => number_format(
                        $current['preparing_count'],
                    ),
                    'change' => $current['preparing_count'] > 0
                            ? 'In progress'
                            : 'None active',
                    'change_tone' => $current['preparing_count'] > 0
                            ? 'neutral'
                            : 'positive',
                    'comparison' => sprintf(
                        'Orders placed %s',
                        $period->label(),
                    ),
                    'icon' => 'heroicon-o-fire',
                ],
                [
                    'label' => 'Average order value',
                    'value' => $this->formatUsd(
                        $current[
                            'average_order_value_cents'
                        ],
                    ),
                    'change' => $averageOrderValueTrend['label'],
                    'change_tone' => $averageOrderValueTrend['tone'],
                    'comparison' => $comparison,
                    'icon' => 'heroicon-o-receipt-percent',
                ],
            ],
        ];
    }

    /**
     * Return a date-aware metric label.
     */
    private function periodLabel(
        string $label,
        DashboardPeriod $period,
    ): string {
        return $period->isToday()
            ? $label.' today'
            : $label.' in period';
    }

    /**
     * Format integer cents for dashboard presentation only.
     */
    private function formatUsd(int $cents): string
    {
        return '$'.number_format(
            $cents / 100,
            2,
        );
    }

    /**
     * Build the label and presentation tone for a period comparison.
     *
     * @return array{
     *     label: string,
     *     tone: string
     * }
     */
    private function trend(
        int $current,
        int $previous,
    ): array {
        if ($previous === 0) {
            return $current > 0
                ? [
                    'label' => 'New',
                    'tone' => 'positive',
                ]
                : [
                    'label' => 'No change',
                    'tone' => 'neutral',
                ];
        }

        $percentage = round(
            (($current - $previous) / $previous)
                * 100,
            1,
        );

        if ($percentage > 0) {
            return [
                'label' => sprintf(
                    '↑ %s%%',
                    number_format($percentage, 1),
                ),
                'tone' => 'positive',
            ];
        }

        if ($percentage < 0) {
            return [
                'label' => sprintf(
                    '↓ %s%%',
                    number_format(
                        abs($percentage),
                        1,
                    ),
                ),
                'tone' => 'attention',
            ];
        }

        return [
            'label' => 'No change',
            'tone' => 'neutral',
        ];
    }
}
