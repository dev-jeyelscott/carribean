<?php

namespace App\Mail;

use App\Models\ContactInquiry;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactInquiryAcknowledgement extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create the contact acknowledgement message.
     */
    public function __construct(
        public readonly ContactInquiry $contactInquiry,
    ) {}

    /**
     * Define the customer-facing subject.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We Received Your Message',
        );
    }

    /**
     * Define the Markdown email content.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.inquiries.contact-inquiry-acknowledgement',
            with: [
                'contactInquiry' => $this->contactInquiry,
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
