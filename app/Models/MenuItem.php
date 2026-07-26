<?php

namespace App\Models;

use App\Models\Concerns\HasResponsiveImages;
use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $menu_category_id
 * @property string $name
 * @property string|null $slug
 * @property string|null $description
 * @property string|null $price
 * @property int|null $price_cents
 * @property string|null $image_path
 * @property string|null $image_alt_text
 * @property int $sort_order
 * @property bool $is_visible
 * @property bool $is_featured
 * @property bool $is_available
 * @property bool $is_purchasable
 * @property array<int, string>|null $dietary_labels
 * @property string|null $allergen_information
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $image_url
 * @property-read MenuCategory $menuCategory
 */
#[Fillable([
    'menu_category_id',
    'name',
    'slug',
    'description',
    'price',
    'price_cents',
    'image_path',
    'image_alt_text',
    'sort_order',
    'is_visible',
    'is_featured',
    'is_available',
    'is_purchasable',
    'dietary_labels',
    'allergen_information',
])]
class MenuItem extends Model
{
    use HasResponsiveImages;

    /**
     * Configure model casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_cents' => 'integer',
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
            'is_available' => 'boolean',
            'is_purchasable' => 'boolean',
            'dietary_labels' => 'array',
        ];
    }

    /**
     * Keep the legacy decimal column synchronized during the safe rollout.
     */
    protected static function booted(): void
    {
        static::saving(function (MenuItem $menuItem): void {
            if ($menuItem->isDirty('price_cents')) {
                $menuItem->price = Money::centsToDecimal(
                    $menuItem->price_cents,
                );

                return;
            }

            if ($menuItem->isDirty('price')) {
                $menuItem->price_cents = Money::decimalToCents(
                    $menuItem->price,
                );
            }
        });
    }

    /**
     * Use the menu-item slug for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the category containing this menu item.
     *
     * @return BelongsTo<MenuCategory, $this>
     */
    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class);
    }

    /**
     * Get this item's option groups in display order.
     *
     * @return HasMany<MenuItemOptionGroup, $this>
     */
    public function optionGroups(): HasMany
    {
        return $this->hasMany(MenuItemOptionGroup::class)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * Format the authoritative menu price as US dollars.
     */
    public function formattedPrice(): ?string
    {
        return Money::formatUsd($this->price_cents);
    }

    /**
     * Restrict a query to publicly visible menu items.
     *
     * @param  Builder<MenuItem>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    /**
     * Restrict a query to available menu items.
     *
     * @param  Builder<MenuItem>  $query
     */
    #[Scope]
    protected function available(Builder $query): void
    {
        $query->where('is_available', true);
    }

    /**
     * Restrict a query to items that support online ordering.
     *
     * @param  Builder<MenuItem>  $query
     */
    #[Scope]
    protected function purchasable(Builder $query): void
    {
        $query->where('is_purchasable', true);
    }

    /**
     * Restrict a query to featured menu items.
     *
     * @param  Builder<MenuItem>  $query
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * Order menu items consistently.
     *
     * @param  Builder<MenuItem>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
