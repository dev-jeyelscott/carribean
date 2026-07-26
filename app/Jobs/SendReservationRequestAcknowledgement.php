<?php

namespace App\Jobs;

use App\Mail\ReservationRequestAcknowledgement;
use App\Models\ReservationRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendReservationRequestAcknowledgement implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 20;

    public bool $failOnTimeout = true;

    /**
     * Create the queued acknowledgement job.
     */
    public function __construct(
        public readonly int $reservationRequestId,
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
        $reservationRequest = ReservationRequest::query()
            ->find($this->reservationRequestId);

        if (! $reservationRequest instanceof ReservationRequest) {
            Log::warning(
                'Reservation acknowledgement was skipped because the request no longer exists.',
                [
                    'reservation_request_id' => $this->reservationRequestId,
                ],
            );

            return;
        }

        if (
            $reservationRequest->customer_acknowledgement_sent_at
            !== null
        ) {
            return;
        }

        Mail::to($reservationRequest->email)
            ->send(
                new ReservationRequestAcknowledgement(
                    $reservationRequest,
                ),
            );

        $reservationRequest->forceFill([
            'customer_acknowledgement_sent_at' => now(),
        ])->save();
    }

    /**
     * Record a permanently failed acknowledgement.
     */
    public function failed(Throwable $exception): void
    {
        Log::error(
            'Reservation-request acknowledgement permanently failed.',
            [
                'reservation_request_id' => $this->reservationRequestId,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ],
        );
    }
}
