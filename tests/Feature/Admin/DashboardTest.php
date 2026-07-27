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

it('shows accurate unread contact inquiry counts', function (): void {
    $this->actingAs(dashboardTestAdmin());

    foreach (range(1, 4) as $index) {
        dashboardTestContactInquiry([
            'email' => "guest-{$index}@example.test",
        ]);
    }

    Livewire::test(InquiryOverview::class)
        ->assertSee('Unread Contact Inquiries')
        ->assertSee('4')
        ->assertSee('Awaiting manual review');
});

it('shows recent contact inquiries without exposing unnecessary customer data', function (): void {
    $this->actingAs(dashboardTestAdmin());

    dashboardTestContactInquiry([
        'customer_name' => 'Maria Carter',
        'email' => 'private-customer@example.test',
        'phone' => '+1 (555) 401-3100',
        'subject' => 'Online order question',
        'message' => 'Sensitive order information should not appear on the dashboard.',
    ]);

    Livewire::test(RecentInquiries::class)
        ->assertSee('Recent contact inquiries')
        ->assertSee('Maria Carter')
        ->assertSee('Online order question')
        ->assertSee('New')
        ->assertDontSee('private-customer@example.test')
        ->assertDontSee('+1 (555) 401-3100')
        ->assertDontSee('Sensitive order information');
});

it('limits recent contact inquiries to the newest eight records', function (): void {
    $this->actingAs(dashboardTestAdmin());

    foreach (range(1, 9) as $index) {
        dashboardTestContactInquiry([
            'customer_name' => "Contact Guest {$index}",
            'email' => "contact-{$index}@example.test",
            'created_at' => now()->subMinutes(10 - $index),
            'updated_at' => now()->subMinutes(10 - $index),
        ]);
    }

    Livewire::test(RecentInquiries::class)
        ->assertSee('Contact Guest 9')
        ->assertSee('Contact Guest 2')
        ->assertDontSee('Contact Guest 1');
});

it('renders a contact-only empty inquiry state', function (): void {
    $this->actingAs(dashboardTestAdmin());

    Livewire::test(RecentInquiries::class)
        ->assertSee('No contact inquiries yet')
        ->assertSee('public contact form')
        ->assertDontSee('Reservation Requests')
        ->assertDontSee('Order Inquiries');
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

it('loads recent contact inquiries within a fixed query budget', function (): void {
    $this->actingAs(dashboardTestAdmin());

    dashboardTestContactInquiry();

    DB::flushQueryLog();
    DB::enableQueryLog();

    Livewire::test(RecentInquiries::class)
        ->assertSee('Recent contact inquiries');

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
    $timestamps = array_filter(
        [
            'created_at' => $overrides['created_at'] ?? null,
            'updated_at' => $overrides['updated_at'] ?? null,
        ],
        static fn (mixed $value): bool => $value !== null,
    );

    unset(
        $overrides['created_at'],
        $overrides['updated_at'],
    );

    $inquiry = ContactInquiry::query()->create(array_merge([
        'customer_name' => 'Contact Guest',
        'email' => 'contact@example.test',
        'phone' => null,
        'subject' => 'General restaurant question',
        'message' => 'Please contact me about the restaurant.',
        'is_read' => false,
    ], $overrides));

    if ($timestamps !== []) {
        $inquiry->forceFill($timestamps)->saveQuietly();
    }

    return $inquiry->refresh();
}
