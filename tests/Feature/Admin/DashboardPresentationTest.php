<?php

use App\Filament\Widgets\DashboardOverview;
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
        'dashboard-admin@example.test',
    );

    Filament::setCurrentPanel(
        Filament::getPanel('admin'),
    );

    $this->actingAs(
        dashboardPresentationAdmin(),
    );
});

it('renders the live branded analytics dashboard', function (): void {
    $this->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Website overview')
        ->assertSee('Filters')
        ->assertSee('Live data')
        ->assertDontSee('Sample data')
        ->assertDontSee('UI preview');
});

it('renders live operational metric cards', function (): void {
    Livewire::test(DashboardOverview::class)
        ->assertSee('Revenue in period')
        ->assertSee('Orders in period')
        ->assertSee('Pending confirmation')
        ->assertSee('Preparing orders')
        ->assertSee('Average order value')
        ->assertSee('$0.00')
        ->assertSee('Live data');
});

it('renders the database-backed chart widgets', function (): void {
    Livewire::test(RevenueOverviewChart::class)
        ->assertSee('Revenue overview')
        ->assertSee('Paid revenue');

    Livewire::test(OrderStatusPreviewChart::class)
        ->assertSee('Orders by status')
        ->assertSee('Order lifecycle distribution');

    Livewire::test(FulfillmentMixPreviewChart::class)
        ->assertSee('Orders by fulfillment')
        ->assertSee('Pickup and delivery order mix');
});

it('renders live empty states and operational summaries', function (): void {
    Livewire::test(TopSellingItemsPreview::class)
        ->assertSee('Top-selling items')
        ->assertSee('No paid item sales');

    Livewire::test(RecentOrdersPreview::class)
        ->assertSee('Recent orders')
        ->assertSee(
            'No orders were placed during this period.',
        );

    Livewire::test(OperationsSnapshotPreview::class)
        ->assertSee('Operations snapshot')
        ->assertSee('Needs confirmation')
        ->assertSee('Pickup share')
        ->assertSee('0%');
});

it('loads the dedicated scoped dashboard stylesheet', function (): void {
    $adminEntryCss = file_get_contents(
        resource_path(
            'css/filament/admin/app.css',
        ),
    );

    $dashboardCss = file_get_contents(
        resource_path(
            'css/filament/admin/dashboard.css',
        ),
    );

    expect($adminEntryCss)
        ->toContain(
            '@import "./dashboard.css";',
        )
        ->and($dashboardCss)
        ->toContain('.cc-dashboard-preview')
        ->toContain(
            ':has(.cc-dashboard-preview)',
        );
});

/**
 * Create the configured administrator for dashboard tests.
 */
function dashboardPresentationAdmin(): User
{
    return User::factory()->create([
        'email' => 'dashboard-admin@example.test',
    ]);
}
