<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesDashboardPeriod;
use App\Support\Dashboard\DashboardAnalytics;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class FulfillmentMixPreviewChart extends ChartWidget
{
    use UsesDashboardPeriod;

    protected static ?int $sort = 6;

    protected static bool $isLazy = false;

    protected ?string $heading =
        'Orders by fulfillment';

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '17rem';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 3,
        'xl' => 3,
    ];

    /**
     * Describe the selected reporting period.
     */
    public function getDescription(): ?string
    {
        return sprintf(
            'Pickup and delivery order mix for %s.',
            $this->dashboardPeriod()->label(),
        );
    }

    /**
     * Return live pickup and delivery counts.
     *
     * @return array{
     *     datasets: list<array<string, mixed>>,
     *     labels: list<string>
     * }
     */
    protected function getData(): array
    {
        $counts = app(
            DashboardAnalytics::class,
        )->fulfillmentCounts(
            $this->dashboardPeriod(),
        );

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => [
                        $counts['pickup'],
                        $counts['delivery'],
                    ],
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
     * Render the fulfillment mix as a doughnut chart.
     */
    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * Configure compact responsive chart presentation.
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
