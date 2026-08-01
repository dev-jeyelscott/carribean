<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TopSellingItemsPreview extends Widget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.top-selling-items-preview';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 3,
        'xl' => 3,
    ];

    /**
     * Provide presentation-only menu performance rows.
     *
     * @return array{
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
        return [
            'items' => [
                [
                    'rank' => '01',
                    'name' => 'Coconut Curry Snapper',
                    'orders' => '62 orders',
                    'revenue' => '$2,842.00',
                    'trend' => '↑ 18.4%',
                ],
                [
                    'rank' => '02',
                    'name' => 'Jerk Chicken',
                    'orders' => '55 orders',
                    'revenue' => '$2,365.00',
                    'trend' => '↑ 14.1%',
                ],
                [
                    'rank' => '03',
                    'name' => 'Plantain Croquettes',
                    'orders' => '47 orders',
                    'revenue' => '$1,034.00',
                    'trend' => '↑ 9.8%',
                ],
                [
                    'rank' => '04',
                    'name' => 'Dark Rum Cake',
                    'orders' => '39 orders',
                    'revenue' => '$936.00',
                    'trend' => '↑ 6.3%',
                ],
                [
                    'rank' => '05',
                    'name' => 'Sorrel Cooler',
                    'orders' => '35 orders',
                    'revenue' => '$315.00',
                    'trend' => '↑ 5.1%',
                ],
            ],
        ];
    }
}
