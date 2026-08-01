<?php

namespace App\Filament\Widgets;

use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class FulfillmentMixPreviewChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected static bool $isLazy = false;

    protected ?string $heading = 'Orders by fulfillment';

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '17rem';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 3,
        'xl' => 3,
    ];

    /**
     * Describe the fulfillment preview for non-visual consumption.
     */
    public function getDescription(): ?string
    {
        return 'Sample order mix: 22 pickup orders and 16 delivery orders.';
    }

    /**
     * Supply static pickup and delivery values.
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
                    'data' => [22, 16],
                    'backgroundColor' => [
                        '#2a8a62',
                        '#e66e50',
                    ],
                    'borderColor' => [
                        '#2a8a62',
                        '#e66e50',
                    ],
                    'borderWidth' => 0,
                    'hoverOffset' => 5,
                ],
            ],
            'labels' => [
                'Pickup',
                'Delivery',
            ],
        ];
    }

    /**
     * Render the fulfillment mix as a Chart.js doughnut chart.
     */
    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * Configure accessible, compact chart presentation.
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
                            padding: 18,
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
