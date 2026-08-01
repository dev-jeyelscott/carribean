<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class OperationsSnapshotPreview extends Widget
{
    protected static ?int $sort = 7;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.operations-snapshot-preview';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 3,
        'xl' => 3,
    ];

    /**
     * Provide sample operational insights supported by the restaurant scope.
     *
     * @return array{
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
        return [
            'insights' => [
                [
                    'label' => 'Needs confirmation',
                    'value' => '7',
                    'detail' => 'Sample orders awaiting staff review',
                    'icon' => 'heroicon-o-exclamation-circle',
                    'tone' => 'attention',
                ],
                [
                    'label' => 'Preparing now',
                    'value' => '11',
                    'detail' => 'Sample active kitchen workload',
                    'icon' => 'heroicon-o-fire',
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Pickup share',
                    'value' => '58%',
                    'detail' => 'Sample fulfillment distribution',
                    'icon' => 'heroicon-o-building-storefront',
                    'tone' => 'positive',
                ],
                [
                    'label' => 'Delivery share',
                    'value' => '42%',
                    'detail' => 'Sample fulfillment distribution',
                    'icon' => 'heroicon-o-truck',
                    'tone' => 'neutral',
                ],
            ],
        ];
    }
}
