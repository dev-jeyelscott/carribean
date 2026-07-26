<?php

namespace App\Support\Cart;

use App\Enums\FulfillmentMethod;
use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

final class SessionCart
{
    public const MAX_QUANTITY = 20;

    private const ITEMS_KEY = 'shopping_cart.items';

    private const COUPON_KEY = 'shopping_cart.coupon_code';

    private const FULFILLMENT_KEY =
        'shopping_cart.fulfillment_method';

    private const DELIVERY_ZIP_KEY =
        'shopping_cart.delivery_zip';

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
        $storedItems = session()->get(
            self::ITEMS_KEY,
            [],
        );

        if (! is_array($storedItems)) {
            return [];
        }

        $items = [];

        foreach ($storedItems as $lineKey => $storedItem) {
            if (
                ! is_string($lineKey)
                || ! is_array($storedItem)
            ) {
                continue;
            }

            $menuItemId = (int) (
                $storedItem['menu_item_id'] ?? 0
            );

            $quantity = (int) (
                $storedItem['quantity'] ?? 0
            );

            $storedOptionIds =
                $storedItem['option_ids'] ?? [];

            if (
                $menuItemId < 1
                || $quantity < 1
                || $quantity > self::MAX_QUANTITY
                || ! is_array($storedOptionIds)
            ) {
                continue;
            }

            try {
                $optionIds = $this->normalizeOptionIds(
                    $storedOptionIds,
                );
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
     * Add a configured item and merge identical configurations.
     *
     * @param  array<int, int|string>  $optionIds
     */
    public function add(
        int $menuItemId,
        array $optionIds,
        int $quantity,
    ): string {
        $this->assertQuantity($quantity);

        $normalizedOptionIds =
            $this->normalizeOptionIds($optionIds);

        $lineKey = $this->makeLineKey(
            $menuItemId,
            $normalizedOptionIds,
        );

        $items = $this->items();

        $existingQuantity =
            $items[$lineKey]['quantity'] ?? 0;

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
     * Update one existing cart-line quantity.
     */
    public function update(
        string $lineKey,
        int $quantity,
    ): void {
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
     * Remove one configured cart line.
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
     * Remove multiple invalid or unavailable lines.
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
     * Return the normalized applied coupon code.
     */
    public function couponCode(): ?string
    {
        $value = session()->get(self::COUPON_KEY);

        if (! is_string($value)) {
            return null;
        }

        $code = Coupon::normalizeCode($value);

        return $code === '' ? null : $code;
    }

    /**
     * Persist or remove the applied coupon code.
     */
    public function setCouponCode(?string $couponCode): void
    {
        $normalized = Coupon::normalizeCode($couponCode);

        if ($normalized === '') {
            session()->forget(self::COUPON_KEY);

            return;
        }

        session()->put(self::COUPON_KEY, $normalized);
    }

    /**
     * Return the selected fulfillment method.
     */
    public function fulfillmentMethod(): FulfillmentMethod
    {
        $stored = session()->get(
            self::FULFILLMENT_KEY,
            FulfillmentMethod::Pickup->value,
        );

        if (! is_string($stored)) {
            return FulfillmentMethod::Pickup;
        }

        return FulfillmentMethod::tryFrom($stored)
            ?? FulfillmentMethod::Pickup;
    }

    /**
     * Persist the selected fulfillment method.
     */
    public function setFulfillmentMethod(
        FulfillmentMethod $fulfillmentMethod,
    ): void {
        session()->put(
            self::FULFILLMENT_KEY,
            $fulfillmentMethod->value,
        );

        if (
            $fulfillmentMethod
            === FulfillmentMethod::Pickup
        ) {
            $this->setDeliveryZip(null);
        }
    }

    /**
     * Return the delivery ZIP stored with the cart.
     */
    public function deliveryZip(): ?string
    {
        $value = session()->get(
            self::DELIVERY_ZIP_KEY,
        );

        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * Persist or remove the cart delivery ZIP.
     */
    public function setDeliveryZip(
        ?string $deliveryZip,
    ): void {
        $normalized = trim((string) $deliveryZip);

        if ($normalized === '') {
            session()->forget(self::DELIVERY_ZIP_KEY);

            return;
        }

        session()->put(
            self::DELIVERY_ZIP_KEY,
            $normalized,
        );
    }

    /**
     * Remove the complete cart and its pricing context.
     */
    public function clear(): void
    {
        session()->forget('shopping_cart');
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
     * Normalize selected option identifiers into sorted integers.
     *
     * @param  array<int, mixed>  $optionIds
     * @return list<int>
     */
    private function normalizeOptionIds(
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

            $normalizedOptionId = (int) $optionId;

            if ($normalizedOptionId < 1) {
                throw ValidationException::withMessages([
                    'selections' => 'One or more selected options are invalid.',
                ]);
            }

            $normalizedOptionIds[] =
                $normalizedOptionId;
        }

        $normalizedOptionIds = array_values(
            array_unique($normalizedOptionIds),
        );

        sort($normalizedOptionIds);

        return $normalizedOptionIds;
    }

    /**
     * Validate the quantity allowed for a cart line.
     */
    private function assertQuantity(int $quantity): void
    {
        if (
            $quantity < 1
            || $quantity > self::MAX_QUANTITY
        ) {
            throw ValidationException::withMessages([
                'quantity' => sprintf(
                    'Quantity must be between 1 and %d.',
                    self::MAX_QUANTITY,
                ),
            ]);
        }
    }

    /**
     * Produce a stable key for an item and its option IDs.
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
     * Persist normalized lines or clear an empty cart.
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
            $this->clear();

            return;
        }

        session()->put(self::ITEMS_KEY, $items);
    }
}
