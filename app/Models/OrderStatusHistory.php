<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'changed_by_user_id',
    'previous_status',
    'new_status',
    'public_note',
    'internal_note',
])]
class OrderStatusHistory extends Model
{
    /**
     * Configure order-status enum casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'previous_status' => OrderStatus::class,
            'new_status' => OrderStatus::class,
        ];
    }

    /**
     * Get the order belonging to this history entry.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the customer or administrator who changed the status.
     *
     * @return BelongsTo<User, $this>
     */
    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'changed_by_user_id',
        );
    }
}
