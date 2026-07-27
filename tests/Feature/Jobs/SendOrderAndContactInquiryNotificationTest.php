<?php

use App\Jobs\SendContactInquiryNotification;
use App\Mail\ContactInquirySubmitted;
use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function createContactInquiryForNotificationJob(array $overrides = []): ContactInquiry
{
    return ContactInquiry::query()->create(array_merge([
        'customer_name' => 'Maria Santos',
        'email' => 'maria@example.test',
        'phone' => '09171234567',
        'subject' => 'Private dining inquiry',
        'message' => 'I would like to ask about available dining packages.',
    ], $overrides));
}

test('contact inquiry notification job sends email and records delivery time', function (): void {
    Mail::fake();
    $contactInquiry = createContactInquiryForNotificationJob();

    (new SendContactInquiryNotification($contactInquiry->id, 'restaurant@example.test'))->handle();

    Mail::assertSent(ContactInquirySubmitted::class, fn (ContactInquirySubmitted $mail): bool => (
        $mail->contactInquiry->is($contactInquiry)
        && $mail->hasTo('restaurant@example.test')
    ));

    expect($contactInquiry->refresh()->notification_sent_at)->not->toBeNull();
});

test('contact inquiry notification job skips an already notified inquiry', function (): void {
    Mail::fake();
    $contactInquiry = createContactInquiryForNotificationJob(['notification_sent_at' => now()]);

    (new SendContactInquiryNotification($contactInquiry->id, 'restaurant@example.test'))->handle();

    Mail::assertNothingSent();
});

test('contact inquiry notification failure leaves the inquiry stored', function (): void {
    $contactInquiry = createContactInquiryForNotificationJob();

    Mail::shouldReceive('to')
        ->once()
        ->with('restaurant@example.test')
        ->andThrow(new RuntimeException('SMTP unavailable.'));

    expect(fn () => (new SendContactInquiryNotification($contactInquiry->id, 'restaurant@example.test'))->handle())
        ->toThrow(RuntimeException::class, 'SMTP unavailable.');

    $this->assertModelExists($contactInquiry);
    expect($contactInquiry->refresh()->notification_sent_at)->toBeNull();
});
