<?php

namespace App\Support\Cart;

use Illuminate\Validation\ValidationException;

final class SessionCart
{
    public const MAX_QUANTITY = 20;

    private const SESSION_KEY = 'shopping_cart.items';

    /**
     * Return the normalized cart lines currently stored in the session.
     *
     * @return array<string, array{
     *     menu_item_id: int,
     *     option_ids: list<int>,
     *     quantity: int
     * }>
     */
    public function items(): array
    {
        $storedItems = session()->get(self::SESSION_KEY, []);

        if (! is_array($storedItems)) {
            return [];
        }

        $items = [];

        foreach ($storedItems as $lineKey => $storedItem) {
            if (! is_string($lineKey) || ! is_array($storedItem)) {
                continue;
            }

            $menuItemId = (int) ($storedItem['menu_item_id'] ?? 0);
            $quantity = (int) ($storedItem['quantity'] ?? 0);
            $storedOptionIds = $storedItem['option_ids'] ?? [];

            if (
                $menuItemId < 1
                || $quantity < 1
                || $quantity > self::MAX_QUANTITY
                || ! is_array($storedOptionIds)
            ) {
                continue;
            }

            try {
                $optionIds = $this->normalizeOptionIds($storedOptionIds);
            } catch (ValidationException) {
                continue;
            }

            $items[$lineKey] = [
                'menu_item_id' => $menuItemId,
                'option_ids' => $optionIds,
                'quantity' => $quantity,
            ];
        }

        return $items;
    }

    /**
     * Add a configured item to the cart and merge identical configurations.
     *
     * @param  array<int, int|string>  $optionIds
     */
    public function add(
        int $menuItemId,
        array $optionIds,
        int $quantity,
    ): string {
        $this->assertQuantity($quantity);

        $normalizedOptionIds = $this->normalizeOptionIds($optionIds);
        $lineKey = $this->makeLineKey(
            $menuItemId,
            $normalizedOptionIds,
        );

        $items = $this->items();
        $existingQuantity = $items[$lineKey]['quantity'] ?? 0;
        $newQuantity = $existingQuantity + $quantity;

        $this->assertQuantity($newQuantity);

        $items[$lineKey] = [
            'menu_item_id' => $menuItemId,
            'option_ids' => $normalizedOptionIds,
            'quantity' => $newQuantity,
        ];

        $this->store($items);

        return $lineKey;
    }

    /**
     * Update the quantity of an existing configured cart line.
     */
    public function update(string $lineKey, int $quantity): void
    {
        $this->assertQuantity($quantity);

        $items = $this->items();

        if (! isset($items[$lineKey])) {
            throw ValidationException::withMessages([
                'cart' => 'The selected cart item no longer exists.',
            ]);
        }

        $items[$lineKey]['quantity'] = $quantity;

        $this->store($items);
    }

    /**
     * Remove one configured line from the cart.
     */
    public function remove(string $lineKey): void
    {
        $items = $this->items();

        if (! isset($items[$lineKey])) {
            throw ValidationException::withMessages([
                'cart' => 'The selected cart item no longer exists.',
            ]);
        }

        unset($items[$lineKey]);

        $this->store($items);
    }

    /**
     * Remove multiple invalid or unavailable lines from the cart.
     *
     * @param  list<string>  $lineKeys
     */
    public function removeMany(array $lineKeys): void
    {
        $items = $this->items();

        foreach ($lineKeys as $lineKey) {
            unset($items[$lineKey]);
        }

        $this->store($items);
    }

    /**
     * Remove every line from the shopping cart.
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Return the total number of units across all cart lines.
     */
    public function count(): int
    {
        $count = 0;

        foreach ($this->items() as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

    /**
     * Normalize selected option identifiers into stable sorted integers.
     *
     * @param  array<int, mixed>  $optionIds
     * @return list<int>
     */
    private function normalizeOptionIds(array $optionIds): array
    {
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

            $normalizedOptionId = (int) $optionId;

            if ($normalizedOptionId < 1) {
                throw ValidationException::withMessages([
                    'selections' => 'One or more selected options are invalid.',
                ]);
            }

            $normalizedOptionIds[] = $normalizedOptionId;
        }

        $normalizedOptionIds = array_values(
            array_unique($normalizedOptionIds),
        );

        sort($normalizedOptionIds);

        return $normalizedOptionIds;
    }

    /**
     * Validate the allowed quantity for a configured cart line.
     */
    private function assertQuantity(int $quantity): void
    {
        if ($quantity < 1 || $quantity > self::MAX_QUANTITY) {
            throw ValidationException::withMessages([
                'quantity' => sprintf(
                    'Quantity must be between 1 and %d.',
                    self::MAX_QUANTITY,
                ),
            ]);
        }
    }

    /**
     * Produce a stable key for a menu item and its selected option IDs.
     *
     * @param  list<int>  $optionIds
     */
    private function makeLineKey(
        int $menuItemId,
        array $optionIds,
    ): string {
        return hash(
            'sha256',
            $menuItemId.':'.implode(',', $optionIds),
        );
    }

    /**
     * Persist normalized lines or remove the session key when empty.
     *
     * @param  array<string, array{
     *     menu_item_id: int,
     *     option_ids: list<int>,
     *     quantity: int
     * }>  $items
     */
    private function store(array $items): void
    {
        if ($items === []) {
            session()->forget(self::SESSION_KEY);

            return;
        }

        session()->put(self::SESSION_KEY, $items);
    }
}
