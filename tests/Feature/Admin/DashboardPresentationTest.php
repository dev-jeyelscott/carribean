<?php

use App\Filament\Widgets\DashboardOverview;
use App\Filament\Widgets\DashboardPreviewPrompt;
use App\Filament\Widgets\FulfillmentMixPreviewChart;
use App\Filament\Widgets\OperationsSnapshotPreview;
use App\Filament\Widgets\OrderStatusPreviewChart;
use App\Filament\Widgets\RecentOrdersPreview;
use App\Filament\Widgets\RevenueOverviewChart;
use App\Filament\Widgets\TopSellingItemsPreview;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set(
        'admin.seed_user.email',
        'dashboard-preview-admin@example.test',
    );

    Filament::setCurrentPanel(
        Filament::getPanel('admin'),
    );

    $this->actingAs(dashboardPresentationAdmin());
});

it('renders the branded analytics dashboard preview', function (): void {
    $this->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Website overview')
        ->assertSee('UI preview')
        ->assertSee('Sample data');
});

it('renders the approved operational metric cards', function (): void {
    Livewire::test(DashboardOverview::class)
        ->assertSee('Revenue today')
        ->assertSee('Orders today')
        ->assertSee('Pending confirmation')
        ->assertSee('Preparing orders')
        ->assertSee('Average order value')
        ->assertSee('UI preview');
});

it('renders the Chart.js preview widgets', function (): void {
    Livewire::test(RevenueOverviewChart::class)
        ->assertSee('Revenue overview')
        ->assertSee('Preview data');

    Livewire::test(OrderStatusPreviewChart::class)
        ->assertSee('Orders by status')
        ->assertSee('178 completed');

    Livewire::test(FulfillmentMixPreviewChart::class)
        ->assertSee('Orders by fulfillment')
        ->assertSee('22 pickup orders');
});

it('renders the preview tables and operational summaries', function (): void {
    Livewire::test(TopSellingItemsPreview::class)
        ->assertSee('Top-selling items')
        ->assertSee('Coconut Curry Snapper');

    Livewire::test(RecentOrdersPreview::class)
        ->assertSee('Recent orders')
        ->assertSee('#ORD-10256')
        ->assertSee('Maya Thompson');

    Livewire::test(OperationsSnapshotPreview::class)
        ->assertSee('Operations snapshot')
        ->assertSee('Needs confirmation')
        ->assertSee('Pickup share');

    Livewire::test(DashboardPreviewPrompt::class)
        ->assertSee('Analytics interface ready for integration')
        ->assertSee('Data wiring next');
});

it('loads the dedicated scoped dashboard stylesheet', function (): void {
    $adminEntryCss = file_get_contents(
        resource_path('css/filament/admin/app.css'),
    );

    $dashboardCss = file_get_contents(
        resource_path('css/filament/admin/dashboard.css'),
    );

    expect($adminEntryCss)
        ->toContain('@import "./dashboard.css";')
        ->and($dashboardCss)
        ->toContain('.cc-dashboard-preview')
        ->toContain(':has(.cc-dashboard-preview)');
});

/**
 * Create the configured administrator for dashboard presentation tests.
 */
function dashboardPresentationAdmin(): User
{
    return User::factory()->create([
        'email' => 'dashboard-preview-admin@example.test',
    ]);
}
