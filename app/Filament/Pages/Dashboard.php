<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardOverview;
use App\Filament\Widgets\DashboardPreviewPrompt;
use App\Filament\Widgets\FulfillmentMixPreviewChart;
use App\Filament\Widgets\OperationsSnapshotPreview;
use App\Filament\Widgets\OrderStatusPreviewChart;
use App\Filament\Widgets\RecentOrdersPreview;
use App\Filament\Widgets\RevenueOverviewChart;
use App\Filament\Widgets\TopSellingItemsPreview;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    /**
     * Describe the dashboard while keeping the current Website overview
     * wording available to existing acceptance coverage.
     */
    public function getSubheading(): ?string
    {
        return 'Website overview and daily restaurant operations at a glance. Sample data is shown until analytics are connected.';
    }

    /**
     * Display non-interactive preview controls matching the approved design.
     *
     * These controls remain disabled during this UI-only implementation.
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('previewPeriod')
                ->label($this->previewPeriod())
                ->icon('heroicon-o-calendar-days')
                ->color('gray')
                ->outlined()
                ->disabled()
                ->tooltip('Reporting controls will be enabled during data integration')
                ->extraAttributes([
                    'aria-label' => 'Preview reporting period',
                ]),

            Action::make('previewFilters')
                ->label('Filters')
                ->icon('heroicon-o-funnel')
                ->color('gray')
                ->outlined()
                ->disabled()
                ->tooltip('Dashboard filters are not connected yet')
                ->extraAttributes([
                    'aria-label' => 'Preview dashboard filters',
                ]),
        ];
    }

    /**
     * Configure the responsive twelve-column dashboard grid.
     *
     * @return array<string, int>
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 6,
            'xl' => 12,
        ];
    }

    /**
     * Register the UI-preview widgets in their intended visual order.
     *
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            DashboardOverview::class,
            RevenueOverviewChart::class,
            OrderStatusPreviewChart::class,
            TopSellingItemsPreview::class,
            RecentOrdersPreview::class,
            FulfillmentMixPreviewChart::class,
            OperationsSnapshotPreview::class,
            DashboardPreviewPrompt::class,
        ];
    }

    /**
     * Build a current-week label for the disabled reporting-period control.
     */
    private function previewPeriod(): string
    {
        $today = now();

        return sprintf(
            '%s – %s',
            $today->copy()->startOfWeek()->format('M j'),
            $today->copy()->endOfWeek()->format('M j, Y'),
        );
    }
}
