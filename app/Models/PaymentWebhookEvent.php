<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider',
    'provider_event_id',
    'event_type',
    'payload',
    'received_at',
    'processed_at',
    'failed_at',
    'failure_message',
])]
class PaymentWebhookEvent extends Model
{
    /**
     * Configure webhook payload and timestamp casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }
}
