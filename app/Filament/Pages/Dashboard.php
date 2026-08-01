<?php

namespace App\Filament\Pages;

use App\Enums\FulfillmentMethod;
use App\Filament\Widgets\DashboardOverview;
use App\Filament\Widgets\FulfillmentMixPreviewChart;
use App\Filament\Widgets\OperationsSnapshotPreview;
use App\Filament\Widgets\OrderStatusPreviewChart;
use App\Filament\Widgets\RecentOrdersPreview;
use App\Filament\Widgets\RevenueOverviewChart;
use App\Filament\Widgets\TopSellingItemsPreview;
use App\Support\Dashboard\DashboardPeriod;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    /**
     * Display the concise page heading.
     */
    protected static ?string $title = 'Dashboard';

    /**
     * Preserve the established sidebar navigation wording.
     */
    protected static ?string $navigationLabel =
        'Website overview';

    /**
     * Describe the live transactional information displayed by the page.
     */
    public function getSubheading(): ?string
    {
        return 'Website overview and daily restaurant operations from live order data.';
    }

    /**
     * Display the active date range and validated dashboard filters.
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('reportingPeriod')
                ->label(
                    fn (): string => $this->reportingPeriodLabel(),
                )
                ->icon('heroicon-o-calendar-days')
                ->color('gray')
                ->outlined()
                ->disabled()
                ->extraAttributes([
                    'aria-label' => 'Current dashboard reporting period',
                ]),

            FilterAction::make()
                ->label('Filters')
                ->icon('heroicon-o-funnel')
                ->modalHeading('Dashboard filters')
                ->schema([
                    DatePicker::make('startDate')
                        ->label('Start date')
                        ->default(
                            fn (): string => now()
                                ->subDays(6)
                                ->toDateString(),
                        )
                        ->minDate(
                            fn (): string => now()
                                ->subYears(5)
                                ->toDateString(),
                        )
                        ->maxDate(
                            fn (): string => now()->toDateString(),
                        )
                        ->displayFormat('M j, Y')
                        ->required(),

                    DatePicker::make('endDate')
                        ->label('End date')
                        ->default(
                            fn (): string => now()->toDateString(),
                        )
                        ->minDate(
                            fn (): string => now()
                                ->subYears(5)
                                ->toDateString(),
                        )
                        ->maxDate(
                            fn (): string => now()->toDateString(),
                        )
                        ->displayFormat('M j, Y')
                        ->required(),

                    Select::make('fulfillmentMethod')
                        ->label('Fulfillment method')
                        ->options(
                            FulfillmentMethod::options(),
                        )
                        ->placeholder(
                            'All fulfillment methods',
                        )
                        ->native(false),
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
     * Register all live operational dashboard widgets.
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
        ];
    }

    /**
     * Build the visible reporting-period header label.
     */
    private function reportingPeriodLabel(): string
    {
        $filters = is_array(
            $this->filters ?? null,
        )
            ? $this->filters
            : [];

        return DashboardPeriod::fromFilters(
            $filters,
        )->label();
    }
}
