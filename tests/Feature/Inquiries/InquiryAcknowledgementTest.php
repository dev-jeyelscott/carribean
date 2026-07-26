<?php

use App\Jobs\SendContactInquiryAcknowledgement;
use App\Jobs\SendContactInquiryNotification;
use App\Jobs\SendReservationRequestAcknowledgement;
use App\Jobs\SendReservationRequestNotification;
use App\Mail\ContactInquiryAcknowledgement;
use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('queues both contact inquiry notifications', function (): void {
    Queue::fake();

    config()->set(
        'mail.inquiries_to',
        'restaurant@example.com',
    );

    $this->post(
        route('contact-inquiries.store'),
        [
            'customer_name' => 'Jordan Guest',
            'email' => 'jordan@example.com',
            'phone' => '555-0101',
            'subject' => 'Menu question',
            'message' => 'Can you help with an allergen question?',
            'website' => null,
        ],
    )->assertRedirect();

    Queue::assertPushed(
        SendContactInquiryNotification::class,
    );

    Queue::assertPushed(
        SendContactInquiryAcknowledgement::class,
    );
});

it('queues both reservation request notifications', function (): void {
    Queue::fake();

    config()->set(
        'mail.inquiries_to',
        'restaurant@example.com',
    );

    $this->post(
        route('reservation-requests.store'),
        [
            'customer_name' => 'Taylor Guest',
            'email' => 'taylor@example.com',
            'phone' => '555-0102',
            'preferred_date' => now()
                ->addDay()
                ->toDateString(),
            'preferred_time' => '7:00 PM',
            'guest_count' => 4,
            'special_requests' => null,
            'is_banquet_or_event' => false,
            'website' => null,
        ],
    )->assertRedirect();

    Queue::assertPushed(
        SendReservationRequestNotification::class,
    );

    Queue::assertPushed(
        SendReservationRequestAcknowledgement::class,
    );
});

it('sends a contact acknowledgement only once', function (): void {
    Mail::fake();

    $contactInquiry = ContactInquiry::create([
        'customer_name' => 'Jordan Guest',
        'email' => 'jordan@example.com',
        'phone' => null,
        'subject' => 'Hello',
        'message' => 'Testing acknowledgement delivery.',
    ]);

    $job = new SendContactInquiryAcknowledgement(
        contactInquiryId: $contactInquiry->id,
    );

    $job->handle();
    $job->handle();

    Mail::assertSent(
        ContactInquiryAcknowledgement::class,
        1,
    );

    expect(
        $contactInquiry
            ->refresh()
            ->customer_acknowledgement_sent_at,
    )->not->toBeNull();
});
