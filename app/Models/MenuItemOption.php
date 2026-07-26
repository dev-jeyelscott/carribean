<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $menu_item_option_group_id
 * @property string $name
 * @property int $additional_price_cents
 * @property bool $is_available
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read MenuItemOptionGroup $optionGroup
 */
#[Fillable([
    'menu_item_option_group_id',
    'name',
    'additional_price_cents',
    'is_available',
    'sort_order',
])]
class MenuItemOption extends Model
{
    /**
     * Configure model casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'additional_price_cents' => 'integer',
            'is_available' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the option group that owns this option.
     *
     * @return BelongsTo<MenuItemOptionGroup, $this>
     */
    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(
            MenuItemOptionGroup::class,
            'menu_item_option_group_id',
        );
    }

    /**
     * Format this option's price adjustment in US dollars.
     */
    public function formattedAdditionalPrice(): ?string
    {
        if ($this->additional_price_cents === 0) {
            return null;
        }

        return Money::formatUsd($this->additional_price_cents);
    }

    /**
     * Restrict a query to currently available options.
     *
     * @param  Builder<MenuItemOption>  $query
     */
    #[Scope]
    protected function available(Builder $query): void
    {
        $query->where('is_available', true);
    }

    /**
     * Order options for public and administrative display.
     *
     * @param  Builder<MenuItemOption>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
