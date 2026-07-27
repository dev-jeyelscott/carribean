<?php

use App\Jobs\SendContactInquiryNotification;
use App\Mail\ContactInquirySubmitted;
use App\Models\ContactInquiry;
use Illuminate\Support\Facades\Mail;

test('contact inquiry notification job sends the active contact mailable', function (): void {
    Mail::fake();

    $inquiry = ContactInquiry::query()->create([
        'customer_name' => 'Sophia Williams',
        'email' => 'sophia@example.com',
        'phone' => '+1 (555) 401-3001',
        'subject' => 'Online order question',
        'message' => 'Please help me with a pickup order question.',
        'is_read' => false,
    ]);

    (new SendContactInquiryNotification(
        contactInquiryId: $inquiry->id,
        recipient: 'restaurant@example.test',
    ))->handle();

    Mail::assertSent(
        ContactInquirySubmitted::class,
        fn (ContactInquirySubmitted $mail): bool => (
            $mail->hasTo('restaurant@example.test')
            && $mail->contactInquiry->is($inquiry)
        ),
    );

    expect($inquiry->fresh()?->notification_sent_at)->not->toBeNull();
});
