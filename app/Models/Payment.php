<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Represent one payment attempt associated with an order.
 *
 * @property int $id
 * @property int $order_id
 * @property string $provider
 * @property string|null $provider_payment_id
 * @property string|null $provider_checkout_session_id
 * @property PaymentMethod $payment_method
 * @property PaymentStatus $status
 * @property int $amount_cents
 * @property string $currency
 * @property Carbon|null $paid_at
 * @property Carbon|null $failed_at
 * @property Carbon|null $refunded_at
 * @property string|null $failure_message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'order_id',
    'provider',
    'provider_payment_id',
    'provider_checkout_session_id',
    'payment_method',
    'status',
    'amount_cents',
    'currency',
    'paid_at',
    'failed_at',
    'refunded_at',
    'failure_message',
])]
class Payment extends Model
{
    /**
     * Configure payment enum, integer, and date casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'amount_cents' => 'integer',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * Get the order associated with the payment.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
