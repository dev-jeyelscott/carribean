<?php

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use App\Models\User;

function inquiryDetailAdminUser(): User
{
    config(['admin.seed_user.email' => 'admin@example.com']);

    return User::factory()->create([
        'email' => 'admin@example.com',
    ]);
}

function inquiryDetailRecords(): array
{
    $contactInquiry = ContactInquiry::query()->create([
        'customer_name' => 'Cora Contact',
        'email' => 'cora.contact@example.com',
        'phone' => null,
        'subject' => null,
        'message' => 'Could you share your private dining options?',
        'is_read' => true,
        'notification_sent_at' => '2026-07-13 08:30:00',
    ]);

    return [$contactInquiry];
}

test('authorized admins can review branded inquiry detail pages', function () {
    $this->actingAs(inquiryDetailAdminUser());

    [$contactInquiry] = inquiryDetailRecords();

    $this->get(ContactInquiryResource::getUrl('view', ['record' => $contactInquiry]))
        ->assertOk()
        ->assertSeeTextInOrder([
            'Customer contact',
            'Cora Contact',
            'cora.contact@example.com',
            'No phone number was provided.',
            'Message',
            'This inquiry is awaiting manual restaurant review.',
            'No subject was provided.',
            'Could you share your private dining options?',
            'Review details',
            'Notification sent',
            'Jul 13, 2026 08:30:00',
        ]);
});

test('public and unauthorized users cannot view inquiry detail records', function () {
    [$contactInquiry] = inquiryDetailRecords();

    foreach (
        [
            ContactInquiryResource::getUrl('view', ['record' => $contactInquiry]),
        ] as $url
    ) {
        $this->get($url)->assertRedirect();
    }

    config(['admin.seed_user.email' => 'admin@example.com']);

    $this->actingAs(User::factory()->create(['email' => 'staff@example.com']));

    foreach (
        [
            ContactInquiryResource::getUrl('view', ['record' => $contactInquiry]),
        ] as $url
    ) {
        $this->get($url)->assertForbidden();
    }
});
