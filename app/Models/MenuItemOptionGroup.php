<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property int $menu_item_id
 * @property string $name
 * @property bool $is_required
 * @property int $minimum_selections
 * @property int $maximum_selections
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read MenuItem $menuItem
 */
#[Fillable([
    'menu_item_id',
    'name',
    'is_required',
    'minimum_selections',
    'maximum_selections',
    'sort_order',
])]
class MenuItemOptionGroup extends Model
{
    /**
     * Configure model casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'minimum_selections' => 'integer',
            'maximum_selections' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Enforce option-group selection rules for every write path.
     */
    protected static function booted(): void
    {
        static::saving(
            function (MenuItemOptionGroup $optionGroup): void {
                $errors = [];

                if (
                    $optionGroup->is_required
                    && $optionGroup->minimum_selections < 1
                ) {
                    $errors['minimum_selections'] =
                        'A required option group must require at least one selection.';
                }

                if (
                    $optionGroup->maximum_selections
                    < $optionGroup->minimum_selections
                ) {
                    $errors['maximum_selections'] =
                        'Maximum selections must be greater than or equal to minimum selections.';
                }

                if ($errors !== []) {
                    throw ValidationException::withMessages($errors);
                }
            },
        );
    }

    /**
     * Get the menu item that owns this option group.
     *
     * @return BelongsTo<MenuItem, $this>
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * Get the selectable options in display order.
     *
     * @return HasMany<MenuItemOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(MenuItemOption::class)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * Order option groups for public and administrative display.
     *
     * @param  Builder<MenuItemOptionGroup>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
