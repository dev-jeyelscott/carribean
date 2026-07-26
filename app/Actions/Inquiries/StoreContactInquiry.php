<?php

namespace App\Actions\Inquiries;

use App\Jobs\SendContactInquiryAcknowledgement;
use App\Jobs\SendContactInquiryNotification;
use App\Models\ContactInquiry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreContactInquiry
{
    /**
     * Store the inquiry and queue both restaurant and customer messages.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): ContactInquiry
    {
        $contactInquiry = ContactInquiry::create(
            Arr::except(
                $data,
                ['website'],
            ),
        );

        $this->queueRestaurantNotification(
            $contactInquiry,
        );

        $this->queueCustomerAcknowledgement(
            $contactInquiry,
        );

        return $contactInquiry;
    }

    /**
     * Queue the existing internal restaurant notification.
     */
    private function queueRestaurantNotification(
        ContactInquiry $contactInquiry,
    ): void {
        $recipient = config('mail.inquiries_to');

        if (! is_string($recipient) || blank($recipient)) {
            Log::warning(
                'Contact inquiry notification recipient is not configured.',
                [
                    'contact_inquiry_id' => $contactInquiry->id,
                ],
            );

            return;
        }

        try {
            SendContactInquiryNotification::dispatch(
                contactInquiryId: $contactInquiry->id,
                recipient: $recipient,
            );
        } catch (Throwable $exception) {
            Log::error(
                'Contact inquiry notification could not be queued.',
                [
                    'contact_inquiry_id' => $contactInquiry->id,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ],
            );
        }
    }

    /**
     * Queue the customer receipt acknowledgement.
     */
    private function queueCustomerAcknowledgement(
        ContactInquiry $contactInquiry,
    ): void {
        try {
            SendContactInquiryAcknowledgement::dispatch(
                contactInquiryId: $contactInquiry->id,
            );
        } catch (Throwable $exception) {
            Log::error(
                'Contact acknowledgement could not be queued.',
                [
                    'contact_inquiry_id' => $contactInquiry->id,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ],
            );
        }
    }
}
