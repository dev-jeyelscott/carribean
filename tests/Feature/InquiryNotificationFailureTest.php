<?php

use App\Jobs\SendContactInquiryNotification;
use App\Models\ContactInquiry;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

test('contact inquiry notification failure leaves the stored inquiry available for retry', function (): void {
    $inquiry = ContactInquiry::query()->create([
        'customer_name' => 'Noah Carter',
        'email' => 'noah@example.com',
        'phone' => '+1 (555) 401-3300',
        'subject' => 'Online order question',
        'message' => 'Please help me understand the pickup instructions.',
        'is_read' => false,
    ]);

    Mail::shouldReceive('to')
        ->once()
        ->with('restaurant@example.test')
        ->andThrow(new RuntimeException('SMTP unavailable'));

    expect(fn () => (new SendContactInquiryNotification(
        contactInquiryId: $inquiry->id,
        recipient: 'restaurant@example.test',
    ))->handle())->toThrow(RuntimeException::class, 'SMTP unavailable');

    expect($inquiry->fresh())
        ->not->toBeNull()
        ->notification_sent_at
        ->toBeNull();
});
