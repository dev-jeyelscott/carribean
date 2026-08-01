<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardOverview extends Widget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.dashboard-overview';

    protected int|string|array $columnSpan = 'full';

    /**
     * Provide presentation-only KPI values for the approved dashboard layout.
     *
     * @return array{
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
        return [
            'metrics' => [
                [
                    'label' => 'Revenue today',
                    'value' => '$1,842.50',
                    'change' => '↑ 12.4%',
                    'change_tone' => 'positive',
                    'comparison' => 'Sample comparison vs yesterday',
                    'icon' => 'heroicon-o-currency-dollar',
                ],
                [
                    'label' => 'Orders today',
                    'value' => '38',
                    'change' => '↑ 8.6%',
                    'change_tone' => 'positive',
                    'comparison' => 'Sample comparison vs yesterday',
                    'icon' => 'heroicon-o-shopping-bag',
                ],
                [
                    'label' => 'Pending confirmation',
                    'value' => '7',
                    'change' => 'Needs review',
                    'change_tone' => 'attention',
                    'comparison' => 'Sample operational status',
                    'icon' => 'heroicon-o-clock',
                ],
                [
                    'label' => 'Preparing orders',
                    'value' => '11',
                    'change' => 'In progress',
                    'change_tone' => 'neutral',
                    'comparison' => 'Sample kitchen workload',
                    'icon' => 'heroicon-o-fire',
                ],
                [
                    'label' => 'Average order value',
                    'value' => '$48.49',
                    'change' => '↑ 3.2%',
                    'change_tone' => 'positive',
                    'comparison' => 'Sample comparison vs yesterday',
                    'icon' => 'heroicon-o-receipt-percent',
                ],
            ],
        ];
    }
}
