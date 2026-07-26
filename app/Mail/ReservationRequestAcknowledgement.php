<?php

namespace App\Mail;

use App\Models\ReservationRequest;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationRequestAcknowledgement extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create the reservation-request acknowledgement message.
     */
    public function __construct(
        public readonly ReservationRequest $reservationRequest,
    ) {}

    /**
     * Define the customer-facing subject.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We Received Your Reservation Request',
        );
    }

    /**
     * Define the Markdown email content.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.inquiries.reservation-request-acknowledgement',
            with: [
                'reservationRequest' => $this->reservationRequest,
                'restaurantName' => SiteSetting::value(
                    'restaurant_name',
                    config('app.name'),
                ) ?? config('app.name'),
            ],
        );
    }

    /**
     * Return the message attachments.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
