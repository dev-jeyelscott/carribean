<?php

namespace App\Filament\Widgets;

use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class RevenueOverviewChart extends ChartWidget
{
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
     * Explain the temporary chart data in accessible text.
     */
    public function getDescription(): ?string
    {
        return 'Preview data comparing this week with the previous week.';
    }

    /**
     * Supply sample Chart.js line datasets without querying the database.
     *
     * @return array{
     *     datasets: list<array<string, mixed>>,
     *     labels: list<string>
     * }
     */
    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'This week',
                    'data' => [680, 995, 910, 1420, 1160, 1585, 1640],
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
                    'label' => 'Previous week',
                    'data' => [510, 690, 460, 925, 610, 720, 1320],
                    'borderColor' => '#b99b5b',
                    'backgroundColor' => 'transparent',
                    'pointRadius' => 0,
                    'borderWidth' => 2,
                    'borderDash' => [7, 7],
                    'fill' => false,
                    'tension' => 0.38,
                ],
            ],
            'labels' => [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ],
        ];
    }

    /**
     * Render the dataset as a Chart.js line chart.
     */
    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Configure responsive Chart.js presentation and currency labels.
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

                                return `${context.dataset.label}: $${value.toLocaleString()}`;
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
                            callback: (value) => `$${Number(value).toLocaleString()}`,
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
