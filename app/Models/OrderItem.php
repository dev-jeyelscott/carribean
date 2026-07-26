<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'menu_item_id',
    'name',
    'description',
    'base_unit_price_cents',
    'quantity',
    'selected_options',
    'option_total_cents',
    'unit_price_cents',
    'line_total_cents',
])]
class OrderItem extends Model
{
    /**
     * Configure immutable order-item value casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_unit_price_cents' => 'integer',
            'quantity' => 'integer',
            'selected_options' => 'array',
            'option_total_cents' => 'integer',
            'unit_price_cents' => 'integer',
            'line_total_cents' => 'integer',
        ];
    }

    /**
     * Get the order containing this item snapshot.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the current menu item when it still exists.
     *
     * @return BelongsTo<MenuItem, $this>
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
