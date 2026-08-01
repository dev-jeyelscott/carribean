<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardPreviewPrompt extends Widget
{
    protected static ?int $sort = 8;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.dashboard-preview-prompt';

    protected int|string|array $columnSpan = 'full';
}
