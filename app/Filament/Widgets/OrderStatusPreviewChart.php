<?php

namespace App\Filament\Widgets;

use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class OrderStatusPreviewChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected ?string $heading = 'Orders by status';

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '17rem';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 3,
        'xl' => 3,
    ];

    /**
     * Describe the chart values for users who cannot inspect the canvas.
     */
    public function getDescription(): ?string
    {
        return 'Sample totals: 178 completed, 45 processing, 18 pending, and 15 cancelled.';
    }

    /**
     * Supply static status totals for the UI preview.
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
                    'label' => 'Orders',
                    'data' => [178, 45, 18, 15],
                    'backgroundColor' => [
                        '#2a8a62',
                        '#f2c76b',
                        '#e58a3b',
                        '#e66e50',
                    ],
                    'borderColor' => [
                        '#2a8a62',
                        '#f2c76b',
                        '#e58a3b',
                        '#e66e50',
                    ],
                    'borderWidth' => 0,
                    'hoverOffset' => 5,
                ],
            ],
            'labels' => [
                'Completed',
                'Processing',
                'Pending',
                'Cancelled',
            ],
        ];
    }

    /**
     * Render the dataset as a Chart.js doughnut chart.
     */
    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * Configure compact responsive doughnut-chart presentation.
     */
    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#d8ded9',
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 14,
                            boxWidth: 8,
                            boxHeight: 8,
                            font: {
                                size: 10,
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
                    },
                },
            }
        JS);
    }
}
