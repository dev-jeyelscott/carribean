<?php

namespace App\Filament\Widgets\Concerns;

use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

trait UsesDashboardPeriod
{
    use InteractsWithPageFilters;

    /**
     * Normalize the dashboard page filters for this widget.
     */
    protected function dashboardPeriod(): DashboardPeriod
    {
        $filters = is_array(
            $this->pageFilters ?? null,
        )
            ? $this->pageFilters
            : [];

        return DashboardPeriod::fromFilters(
            $filters,
        );
    }
}
