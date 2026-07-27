<?php

use App\Filament\Resources\GalleryImages\GalleryImageResource;
use App\Filament\Resources\MenuItems\MenuItemResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Filament\Widgets\ContentQuickActions;
use App\Filament\Widgets\InquiryOverview;
use App\Filament\Widgets\RecentInquiries;
use App\Models\ContactInquiry;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set('admin.seed_user.email', 'admin@example.test');

    Filament::setCurrentPanel(
        Filament::getPanel('admin'),
    );
});

it('protects dashboard data from users without panel access', function (): void {
    $user = User::factory()->create([
        'email' => 'staff@example.test',
    ]);

    $this->actingAs($user);

    $this->get('/admin')->assertForbidden();

    expect(InquiryOverview::canView())->toBeFalse()
        ->and(RecentInquiries::canView())->toBeFalse()
        ->and(ContentQuickActions::canView())->toBeFalse();
});

it('renders the branded dashboard for the configured admin', function (): void {
    $this->actingAs(dashboardTestAdmin());

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Website overview');
});

it('shows accurate unread inquiry counts', function (): void {
    $this->actingAs(dashboardTestAdmin());

    dashboardTestContactInquiry();
    dashboardTestContactInquiry();
    dashboardTestContactInquiry();
    dashboardTestContactInquiry();

    Livewire::test(InquiryOverview::class)
        ->assertSee('Unread Contact Inquiries')
        ->assertSee('4')
        ->assertSee('Awaiting manual review');
});

it('shows recent inquiries without exposing unnecessary customer data', function (): void {
    $this->actingAs(dashboardTestAdmin());

    dashboardTestContactInquiry([
        'customer_name' => 'Maria Santos',
        'email' => 'private-customer@example.test',
        'phone' => '09171234567',
        'subject' => 'Private dining question',
        'message' => 'Sensitive event information should not appear on the dashboard.',
    ]);

    Livewire::test(RecentInquiries::class)
        ->assertSee('Recent inquiries')
        ->assertSee('Maria Santos')
        ->assertSee('Private dining question')
        ->assertSee('New')
        ->assertDontSee('private-customer@example.test')
        ->assertDontSee('09171234567')
        ->assertDontSee('Sensitive event information');
});

it('shows the globally newest inquiries when one type has more than four records', function (): void {
    $this->actingAs(dashboardTestAdmin());

    $widget = Livewire::test(RecentInquiries::class);

    foreach (range(1, 8) as $index) {
        $widget->assertSee("Newest Reservation {$index}");
    }

    $widget->assertDontSee('Older Contact');
});

it('renders a polished empty inquiry state', function (): void {
    $this->actingAs(dashboardTestAdmin());

    Livewire::test(RecentInquiries::class)
        ->assertSee('No inquiries yet')
        ->assertSee('Reservation Requests')
        ->assertSee('Order Inquiries')
        ->assertSee('Contact Inquiries');
});

it('links quick actions to approved protected resources', function (): void {
    $this->actingAs(dashboardTestAdmin());

    Livewire::test(ContentQuickActions::class)
        ->assertSee('Add menu item')
        ->assertSee('Upload gallery image')
        ->assertSee('Manage page content')
        ->assertSee('Update site settings')
        ->assertSeeHtml(
            'href="'.MenuItemResource::getUrl('create').'"',
        )
        ->assertSeeHtml(
            'href="'.GalleryImageResource::getUrl('create').'"',
        )
        ->assertSeeHtml(
            'href="'.PageResource::getUrl('index').'"',
        )
        ->assertSeeHtml(
            'href="'.SiteSettingResource::getUrl('index').'"',
        );
});

it('loads recent inquiries within a fixed query budget', function (): void {
    $this->actingAs(dashboardTestAdmin());

    dashboardTestContactInquiry();

    DB::flushQueryLog();
    DB::enableQueryLog();

    Livewire::test(RecentInquiries::class)
        ->assertSee('Recent inquiries');

    $selectQueryCount = collect(DB::getQueryLog())
        ->filter(
            fn (array $query): bool => str_starts_with(
                strtolower(ltrim($query['query'])),
                'select',
            ),
        )
        ->count();

    DB::disableQueryLog();

    expect($selectQueryCount)->toBeLessThanOrEqual(4);
});

function dashboardTestAdmin(): User
{
    return User::factory()->create([
        'email' => 'admin@example.test',
    ]);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function dashboardTestContactInquiry(
    array $overrides = [],
): ContactInquiry {
    return ContactInquiry::query()->create(array_merge([
        'customer_name' => 'Contact Guest',
        'email' => 'contact@example.test',
        'phone' => null,
        'subject' => 'General inquiry',
        'message' => 'Please contact me about the restaurant.',
        'is_read' => false,
    ], $overrides));
}
