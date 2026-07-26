<?php

namespace App\Models;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $public_id
 * @property string|null $order_number
 * @property int|null $user_id
 * @property string $customer_name
 * @property string $customer_email
 * @property string $customer_phone
 * @property OrderStatus $status
 * @property PaymentStatus $payment_status
 * @property FulfillmentMethod $fulfillment_method
 * @property PaymentMethod $payment_method
 * @property string $currency
 * @property int $subtotal_cents
 * @property int $discount_cents
 * @property int $tax_cents
 * @property int $delivery_cents
 * @property int $grand_total_cents
 * @property int $tax_rate_basis_points
 * @property string|null $coupon_code
 * @property array<string, mixed>|null $coupon_snapshot
 * @property string|null $customer_note
 * @property string|null $internal_note
 * @property string $checkout_idempotency_token
 * @property Carbon $placed_at
 * @property Carbon|null $paid_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $rejected_at
 * @property Carbon|null $picked_up_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $completed_at
 */
#[Fillable([
    'public_id',
    'order_number',
    'user_id',
    'customer_name',
    'customer_email',
    'customer_phone',
    'status',
    'payment_status',
    'fulfillment_method',
    'payment_method',
    'currency',
    'subtotal_cents',
    'discount_cents',
    'tax_cents',
    'delivery_cents',
    'grand_total_cents',
    'tax_rate_basis_points',
    'coupon_code',
    'coupon_snapshot',
    'customer_note',
    'internal_note',
    'checkout_idempotency_token',
    'placed_at',
    'paid_at',
    'cancelled_at',
    'rejected_at',
    'picked_up_at',
    'delivered_at',
    'completed_at',
])]
class Order extends Model
{
    /**
     * Configure enum, integer, array, and date casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'fulfillment_method' => FulfillmentMethod::class,
            'payment_method' => PaymentMethod::class,
            'subtotal_cents' => 'integer',
            'discount_cents' => 'integer',
            'tax_cents' => 'integer',
            'delivery_cents' => 'integer',
            'grand_total_cents' => 'integer',
            'tax_rate_basis_points' => 'integer',
            'coupon_snapshot' => 'array',
            'placed_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'rejected_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Use the non-sequential public ID for route binding.
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Get the registered customer who placed the order.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get immutable item snapshots for the order.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)
            ->orderBy('id');
    }

    /**
     * Get immutable address snapshots for the order.
     *
     * @return HasMany<OrderAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class)
            ->orderBy('id');
    }

    /**
     * Get the append-only status history.
     *
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Get payment attempts associated with the order.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)
            ->orderBy('id');
    }

    /**
     * Get the coupon usage recorded for the order.
     *
     * @return HasOne<CouponUsage, $this>
     */
    public function couponUsage(): HasOne
    {
        return $this->hasOne(CouponUsage::class);
    }

    /**
     * Format the snapshotted order total as US dollars.
     */
    public function formattedGrandTotal(): string
    {
        return Money::formatUsd(
            $this->grand_total_cents,
        ) ?? '$0.00';
    }
}
