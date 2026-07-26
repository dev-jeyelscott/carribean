<?php

namespace App\Jobs;

use App\Mail\ContactInquiryAcknowledgement;
use App\Models\ContactInquiry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendContactInquiryAcknowledgement implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 20;

    public bool $failOnTimeout = true;

    /**
     * Create the queued acknowledgement job.
     */
    public function __construct(
        public readonly int $contactInquiryId,
    ) {
        $this->afterCommit();
    }

    /**
     * Return retry delays in seconds.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }

    /**
     * Send one idempotent customer acknowledgement.
     */
    public function handle(): void
    {
        $contactInquiry = ContactInquiry::query()
            ->find($this->contactInquiryId);

        if (! $contactInquiry instanceof ContactInquiry) {
            Log::warning(
                'Contact acknowledgement was skipped because the inquiry no longer exists.',
                [
                    'contact_inquiry_id' => $this->contactInquiryId,
                ],
            );

            return;
        }

        if (
            $contactInquiry->customer_acknowledgement_sent_at
            !== null
        ) {
            return;
        }

        Mail::to($contactInquiry->email)
            ->send(
                new ContactInquiryAcknowledgement(
                    $contactInquiry,
                ),
            );

        $contactInquiry->forceFill([
            'customer_acknowledgement_sent_at' => now(),
        ])->save();
    }

    /**
     * Record a permanently failed acknowledgement.
     */
    public function failed(Throwable $exception): void
    {
        Log::error(
            'Contact inquiry acknowledgement permanently failed.',
            [
                'contact_inquiry_id' => $this->contactInquiryId,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ],
        );
    }
}
