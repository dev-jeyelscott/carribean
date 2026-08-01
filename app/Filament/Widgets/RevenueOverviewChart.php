<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesDashboardPeriod;
use App\Support\Dashboard\DashboardAnalytics;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class RevenueOverviewChart extends ChartWidget
{
    use UsesDashboardPeriod;

    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected ?string $heading = 'Revenue overview';

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '17rem';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 6,
        'xl' => 6,
    ];

    /**
     * Describe the selected and comparison periods.
     */
    public function getDescription(): ?string
    {
        $period = $this->dashboardPeriod();

        return sprintf(
            'Paid revenue for %s compared with %s.',
            $period->label(),
            $period->previous()->label(),
        );
    }

    /**
     * Return paid revenue grouped by day.
     *
     * @return array{
     *     datasets: list<array<string, mixed>>,
     *     labels: list<string>
     * }
     */
    protected function getData(): array
    {
        $period = $this->dashboardPeriod();

        $series = app(
            DashboardAnalytics::class,
        )->revenueSeries($period);

        return [
            'datasets' => [
                [
                    'label' => 'Selected period',
                    'data' => array_map(
                        static fn (int $cents): float => round($cents / 100, 2),
                        $series['current'],
                    ),
                    'borderColor' => '#5fc08a',
                    'backgroundColor' => 'rgba(95, 192, 138, 0.14)',
                    'pointBackgroundColor' => '#fff9f0',
                    'pointBorderColor' => '#5fc08a',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.38,
                ],
                [
                    'label' => 'Previous period',
                    'data' => array_map(
                        static fn (int $cents): float => round($cents / 100, 2),
                        $series['previous'],
                    ),
                    'borderColor' => '#b99b5b',
                    'backgroundColor' => 'transparent',
                    'pointRadius' => 0,
                    'borderWidth' => 2,
                    'borderDash' => [7, 7],
                    'fill' => false,
                    'tension' => 0.38,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    /**
     * Render the revenue dataset as a line chart.
     */
    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Configure responsive presentation and USD formatting.
     */
    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'start',
                        labels: {
                            color: '#d8ded9',
                            usePointStyle: true,
                            pointStyle: 'line',
                            padding: 18,
                            font: {
                                size: 11,
                            },
                        },
                    },
                    tooltip: {
                        backgroundColor: '#092a23',
                        titleColor: '#fff9f0',
                        bodyColor: '#d8ded9',
                        borderColor: 'rgba(230, 110, 80, 0.34)',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: (context) => {
                                const value = Number(context.raw ?? 0);
                                const formatted = new Intl.NumberFormat(
                                    'en-US',
                                    {
                                        style: 'currency',
                                        currency: 'USD',
                                    },
                                ).format(value);

                                return `${context.dataset.label}: ${formatted}`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        border: {
                            display: false,
                        },
                        ticks: {
                            color: '#9fb3aa',
                            maxRotation: 0,
                            autoSkip: true,
                            font: {
                                size: 10,
                            },
                        },
                    },
                    y: {
                        beginAtZero: true,
                        border: {
                            display: false,
                        },
                        grid: {
                            color: 'rgba(255, 249, 240, 0.08)',
                        },
                        ticks: {
                            color: '#9fb3aa',
                            padding: 8,
                            callback: (value) => (
                                new Intl.NumberFormat(
                                    'en-US',
                                    {
                                        style: 'currency',
                                        currency: 'USD',
                                        maximumFractionDigits: 0,
                                    },
                                ).format(Number(value))
                            ),
                            font: {
                                size: 10,
                            },
                        },
                    },
                },
            }
        JS);
    }
}
