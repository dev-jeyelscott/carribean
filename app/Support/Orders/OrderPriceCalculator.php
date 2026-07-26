<?php

namespace App\Support\Orders;

use App\Models\MenuItem;
use App\Models\MenuItemOptionGroup;
use App\Support\Cart\SessionCart;
use App\Support\Money;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Validation\ValidationException;

final class OrderPriceCalculator
{
    /**
     * Validate whether an item and its option selection can be purchased.
     *
     * @param  array<int, mixed>  $optionIds
     */
    public function validateSelection(
        int $menuItemId,
        array $optionIds,
    ): void {
        $menuItem = $this->loadPurchasableItems([
            $menuItemId,
        ])->firstWhere('id', $menuItemId);

        if (! $menuItem instanceof MenuItem) {
            throw ValidationException::withMessages([
                'cart' => 'This item is no longer available for online ordering.',
            ]);
        }

        $this->resolveSelectedOptions($menuItem, $optionIds);
    }

    /**
     * Calculate all currently valid cart lines using current database prices.
     *
     * @param  array<string, array{
     *     menu_item_id: int,
     *     option_ids: list<int>,
     *     quantity: int
     * }>  $cartItems
     * @return array{
     *     items: list<array{
     *         key: string,
     *         menu_item_id: int,
     *         name: string,
     *         slug: string,
     *         quantity: int,
     *         options: list<array{
     *             id: int,
     *             group_name: string,
     *             name: string,
     *             additional_price_cents: int,
     *             formatted_additional_price: string|null
     *         }>,
     *         unit_price_cents: int,
     *         line_total_cents: int,
     *         formatted_unit_price: string,
     *         formatted_line_total: string
     *     }>,
     *     item_count: int,
     *     subtotal_cents: int,
     *     formatted_subtotal: string,
     *     invalid_line_keys: list<string>
     * }
     */
    public function calculate(array $cartItems): array
    {
        if ($cartItems === []) {
            return $this->emptyCalculation();
        }

        $menuItemIds = [];

        foreach ($cartItems as $cartItem) {
            $menuItemIds[] = $cartItem['menu_item_id'];
        }

        $menuItemIds = array_values(array_unique($menuItemIds));

        $menuItems = $this->loadPurchasableItems($menuItemIds)
            ->keyBy('id');

        $calculatedItems = [];
        $invalidLineKeys = [];
        $itemCount = 0;
        $subtotalCents = 0;

        foreach ($cartItems as $lineKey => $cartItem) {
            $menuItem = $menuItems->get(
                $cartItem['menu_item_id'],
            );

            if (! $menuItem instanceof MenuItem) {
                $invalidLineKeys[] = $lineKey;

                continue;
            }

            try {
                $calculatedLine = $this->calculateLine(
                    $lineKey,
                    $menuItem,
                    $cartItem['option_ids'],
                    $cartItem['quantity'],
                );
            } catch (ValidationException) {
                $invalidLineKeys[] = $lineKey;

                continue;
            }

            $calculatedItems[] = $calculatedLine;
            $itemCount += $calculatedLine['quantity'];
            $subtotalCents += $calculatedLine['line_total_cents'];
        }

        return [
            'items' => $calculatedItems,
            'item_count' => $itemCount,
            'subtotal_cents' => $subtotalCents,
            'formatted_subtotal' => Money::formatUsd(
                $subtotalCents,
            ) ?? '$0.00',
            'invalid_line_keys' => $invalidLineKeys,
        ];
    }

    /**
     * Load current, visible, available, and purchasable menu items.
     *
     * @param  list<int>  $menuItemIds
     * @return EloquentCollection<int, MenuItem>
     */
    private function loadPurchasableItems(
        array $menuItemIds,
    ): EloquentCollection {
        return MenuItem::query()
            ->whereIn('id', $menuItemIds)
            ->visible()
            ->available()
            ->purchasable()
            ->whereNotNull('price_cents')
            ->whereHas(
                'menuCategory',
                fn ($query) => $query->where(
                    'is_visible',
                    true,
                ),
            )
            ->with([
                'optionGroups' => fn ($query) => $query
                    ->ordered()
                    ->with([
                        'options' => fn ($optionQuery) => $optionQuery
                            ->available()
                            ->ordered(),
                    ]),
            ])
            ->get();
    }

    /**
     * Calculate one configured cart line from current database values.
     *
     * @param  array<int, mixed>  $optionIds
     * @return array{
     *     key: string,
     *     menu_item_id: int,
     *     name: string,
     *     slug: string,
     *     quantity: int,
     *     options: list<array{
     *         id: int,
     *         group_name: string,
     *         name: string,
     *         additional_price_cents: int,
     *         formatted_additional_price: string|null
     *     }>,
     *     unit_price_cents: int,
     *     line_total_cents: int,
     *     formatted_unit_price: string,
     *     formatted_line_total: string
     * }
     */
    private function calculateLine(
        string $lineKey,
        MenuItem $menuItem,
        array $optionIds,
        int $quantity,
    ): array {
        if (
            $quantity < 1
            || $quantity > SessionCart::MAX_QUANTITY
        ) {
            throw ValidationException::withMessages([
                'quantity' => 'The cart quantity is invalid.',
            ]);
        }

        $selectedOptions = $this->resolveSelectedOptions(
            $menuItem,
            $optionIds,
        );

        $optionTotalCents = 0;

        foreach ($selectedOptions as $selectedOption) {
            $optionTotalCents +=
                $selectedOption['additional_price_cents'];
        }

        $unitPriceCents =
            (int) $menuItem->price_cents
            + $optionTotalCents;

        $lineTotalCents = $unitPriceCents * $quantity;

        return [
            'key' => $lineKey,
            'menu_item_id' => $menuItem->id,
            'name' => $menuItem->name,
            'slug' => (string) $menuItem->slug,
            'quantity' => $quantity,
            'options' => $selectedOptions,
            'unit_price_cents' => $unitPriceCents,
            'line_total_cents' => $lineTotalCents,
            'formatted_unit_price' => Money::formatUsd(
                $unitPriceCents,
            ) ?? '$0.00',
            'formatted_line_total' => Money::formatUsd(
                $lineTotalCents,
            ) ?? '$0.00',
        ];
    }

    /**
     * Validate option ownership, availability, and group selection limits.
     *
     * @param  array<int, mixed>  $optionIds
     * @return list<array{
     *     id: int,
     *     group_name: string,
     *     name: string,
     *     additional_price_cents: int,
     *     formatted_additional_price: string|null
     * }>
     */
    private function resolveSelectedOptions(
        MenuItem $menuItem,
        array $optionIds,
    ): array {
        $normalizedOptionIds = [];

        foreach ($optionIds as $optionId) {
            if (
                ! is_int($optionId)
                && ! (
                    is_string($optionId)
                    && ctype_digit($optionId)
                )
            ) {
                throw ValidationException::withMessages([
                    'selections' => 'One or more selected options are invalid.',
                ]);
            }

            $normalizedOptionIds[] = (int) $optionId;
        }

        if (
            count($normalizedOptionIds)
            !== count(array_unique($normalizedOptionIds))
        ) {
            throw ValidationException::withMessages([
                'selections' => 'The same option cannot be selected more than once.',
            ]);
        }

        $availableOptionIds = $menuItem->optionGroups
            ->flatMap(
                fn (MenuItemOptionGroup $group) => $group->options
                    ->pluck('id'),
            )
            ->map(fn ($optionId): int => (int) $optionId)
            ->all();

        foreach ($normalizedOptionIds as $optionId) {
            if (! in_array($optionId, $availableOptionIds, true)) {
                throw ValidationException::withMessages([
                    'selections' => 'One or more selected options are unavailable.',
                ]);
            }
        }

        $resolvedOptions = [];

        foreach ($menuItem->optionGroups as $group) {
            $selectedGroupOptions = $group->options
                ->filter(
                    fn ($option): bool => in_array(
                        $option->id,
                        $normalizedOptionIds,
                        true,
                    ),
                )
                ->values();

            $selectionCount = $selectedGroupOptions->count();

            if (
                $selectionCount < $group->minimum_selections
                || $selectionCount > $group->maximum_selections
            ) {
                $message = $group->minimum_selections
                    === $group->maximum_selections
                    ? sprintf(
                        'Select exactly %d option(s) for %s.',
                        $group->minimum_selections,
                        $group->name,
                    )
                    : sprintf(
                        'Select between %d and %d option(s) for %s.',
                        $group->minimum_selections,
                        $group->maximum_selections,
                        $group->name,
                    );

                throw ValidationException::withMessages([
                    'selections.'.$group->id => $message,
                ]);
            }

            foreach ($selectedGroupOptions as $option) {
                $resolvedOptions[] = [
                    'id' => $option->id,
                    'group_name' => $group->name,
                    'name' => $option->name,
                    'additional_price_cents' => $option->additional_price_cents,
                    'formatted_additional_price' => $option->formattedAdditionalPrice(),
                ];
            }
        }

        return $resolvedOptions;
    }

    /**
     * Return the zero-value cart calculation used by empty carts.
     *
     * @return array{
     *     items: list<never>,
     *     item_count: int,
     *     subtotal_cents: int,
     *     formatted_subtotal: string,
     *     invalid_line_keys: list<never>
     * }
     */
    private function emptyCalculation(): array
    {
        return [
            'items' => [],
            'item_count' => 0,
            'subtotal_cents' => 0,
            'formatted_subtotal' => '$0.00',
            'invalid_line_keys' => [],
        ];
    }
}
