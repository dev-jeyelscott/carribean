<?php

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function (): void {
    config()->set('admin.seed_user.email', 'admin@example.test');

    Filament::setCurrentPanel(
        Filament::getPanel('admin'),
    );
});

it('allows the configured administrator to review a contact inquiry', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin@example.test',
    ]);

    $inquiry = ContactInquiry::query()->create([
        'customer_name' => 'Avery Carter',
        'email' => 'avery@example.test',
        'phone' => '+1 (555) 401-3400',
        'subject' => 'Menu question',
        'message' => 'Could you confirm which dishes are vegetarian?',
        'is_read' => false,
    ]);

    $this->actingAs($admin)
        ->get(ContactInquiryResource::getUrl('view', [
            'record' => $inquiry,
        ]))
        ->assertOk()
        ->assertSeeText('Avery Carter')
        ->assertSeeText('Menu question')
        ->assertSeeText('Could you confirm which dishes are vegetarian?');

    expect($inquiry->fresh()?->is_read)->toBeTrue();
});

it('blocks users without panel access from contact inquiry details', function (): void {
    $staff = User::factory()->create([
        'email' => 'staff@example.test',
    ]);

    $inquiry = ContactInquiry::query()->create([
        'customer_name' => 'Jordan Carter',
        'email' => 'jordan@example.test',
        'phone' => null,
        'subject' => 'Directions question',
        'message' => 'Could you share the easiest way to reach the restaurant?',
        'is_read' => false,
    ]);

    $this->actingAs($staff)
        ->get(ContactInquiryResource::getUrl('view', [
            'record' => $inquiry,
        ]))
        ->assertForbidden();
});
