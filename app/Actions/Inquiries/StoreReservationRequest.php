<?php

namespace App\Actions\Inquiries;

use App\Jobs\SendReservationRequestAcknowledgement;
use App\Jobs\SendReservationRequestNotification;
use App\Models\ReservationRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreReservationRequest
{
    /**
     * Store the request and queue both restaurant and customer messages.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): ReservationRequest
    {
        $reservationRequest = ReservationRequest::create(
            Arr::except(
                $data,
                ['website'],
            ),
        );

        $this->queueRestaurantNotification(
            $reservationRequest,
        );

        $this->queueCustomerAcknowledgement(
            $reservationRequest,
        );

        return $reservationRequest;
    }

    /**
     * Queue the existing internal restaurant notification.
     */
    private function queueRestaurantNotification(
        ReservationRequest $reservationRequest,
    ): void {
        $recipient = config('mail.inquiries_to');

        if (! is_string($recipient) || blank($recipient)) {
            Log::warning(
                'Reservation request notification recipient is not configured.',
                [
                    'reservation_request_id' => $reservationRequest->id,
                ],
            );

            return;
        }

        try {
            SendReservationRequestNotification::dispatch(
                reservationRequestId: $reservationRequest->id,
                recipient: $recipient,
            );
        } catch (Throwable $exception) {
            Log::error(
                'Reservation request notification could not be queued.',
                [
                    'reservation_request_id' => $reservationRequest->id,
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
        ReservationRequest $reservationRequest,
    ): void {
        try {
            SendReservationRequestAcknowledgement::dispatch(
                reservationRequestId: $reservationRequest->id,
            );
        } catch (Throwable $exception) {
            Log::error(
                'Reservation acknowledgement could not be queued.',
                [
                    'reservation_request_id' => $reservationRequest->id,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ],
            );
        }
    }
}
