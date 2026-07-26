<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'type',
    'recipient_name',
    'street_address',
    'apartment_or_unit',
    'city',
    'state',
    'postal_code',
    'phone',
    'delivery_instructions',
])]
class OrderAddress extends Model
{
    /**
     * Get the order containing this address snapshot.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
