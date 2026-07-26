<?php

namespace App\Mail\Orders;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOrderAlert extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    /**
     * Create a queued restaurant order alert.
     */
    public function __construct(
        public Order $order,
        public string $subjectLine,
        public string $headline,
        public string $summary,
    ) {
        $this->afterCommit();
    }

    /**
     * Define the restaurant-facing email subject.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    /**
     * Define the Markdown template used for this email.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.admin-order-alert',
        );
    }

    /**
     * Return attachments included with the email.
     *
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }
}
