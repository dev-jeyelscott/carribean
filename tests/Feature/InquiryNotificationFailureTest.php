<?php

use App\Actions\Inquiries\StoreContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function simulateInquiryQueueFailure(): void
{
    config()->set('mail.inquiries_to', 'restaurant@example.test');

    Queue::shouldReceive('connection')
        ->once()
        ->andThrow(new RuntimeException('Simulated queue failure.'));
}

test('contact inquiry remains stored when notification queue dispatch fails', function (): void {
    Log::spy();
    simulateInquiryQueueFailure();

    $contactInquiry = app(StoreContactInquiry::class)->handle([
        'customer_name' => 'Maria Santos',
        'email' => 'maria@example.test',
        'phone' => '09171234567',
        'subject' => 'Private dining inquiry',
        'message' => 'I would like to ask about your available dining packages.',
    ]);

    expect($contactInquiry->exists)->toBeTrue();

    $this->assertDatabaseHas('contact_inquiries', [
        'id' => $contactInquiry->getKey(),
    ]);

    expect($contactInquiry->fresh()->notification_sent_at)->toBeNull();

    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(function (string $message, array $context) use ($contactInquiry): bool {
            return $message === 'Contact inquiry notification could not be queued.'
                && $context['contact_inquiry_id'] === $contactInquiry->id
                && $context['exception'] === RuntimeException::class
                && $context['message'] === 'Simulated queue failure.';
        });
});
