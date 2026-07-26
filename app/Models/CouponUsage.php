<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'coupon_id',
    'order_id',
    'user_id',
    'code',
    'discount_cents',
    'used_at',
])]
class CouponUsage extends Model
{
    /**
     * Configure coupon-usage value casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_cents' => 'integer',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Get the original coupon when it still exists.
     *
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the order that consumed the coupon.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the registered customer when applicable.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
