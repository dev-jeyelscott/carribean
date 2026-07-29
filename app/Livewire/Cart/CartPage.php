<?php

namespace App\Livewire\Cart;

use App\Enums\FulfillmentMethod;
use App\Models\MenuItem;
use App\Support\Cart\SessionCart;
use App\Support\Orders\OrderTotalsCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

final class CartPage extends Component
{
    /**
     * Editable quantities indexed by the stable cart-line key.
     *
     * @var array<string, int|string>
     */
    public array $quantities = [];

    public string $couponCode = '';

    public string $fulfillmentMethod =
        FulfillmentMethod::Pickup->value;

    public string $deliveryZip = '';

    public bool $deliveryZipAttempted = false;

    public ?string $warning = null;

    public ?string $statusMessage = null;

    /**
     * Remove stale cart state and initialize the customer-editable fields.
     */
    public function mount(): void
    {
        $this->synchronizeCart();
    }

    /**
     * Increase one cart line by one while respecting the server maximum.
     */
    public function incrementQuantity(string $lineKey): void
    {
        $this->changeQuantity($lineKey, 1);
    }

    /**
     * Decrease one cart line by one without allowing zero quantities.
     */
    public function decrementQuantity(string $lineKey): void
    {
        $this->changeQuantity($lineKey, -1);
    }

    /**
     * Preserve the existing direct quantity-update API for compatibility.
     */
    public function updateQuantity(string $lineKey): void
    {
        $validationKey = 'quantities.'.$lineKey;

        $validated = $this->validate(
            [
                $validationKey => [
                    'required',
                    'integer',
                    'min:1',
                    'max:'.SessionCart::MAX_QUANTITY,
                ],
            ],
            [],
            [
                $validationKey => 'quantity',
            ],
        );

        $this->persistQuantity(
            $lineKey,
            (int) $validated['quantities'][$lineKey],
        );
    }

    /**
     * Apply a validated single coupon to the session cart.
     */
    public function applyCoupon(): void
    {
        $validated = $this->validate([
            'couponCode' => [
                'required',
                'string',
                'max:64',
            ],
        ]);

        $sessionCart = app(SessionCart::class);
        $calculator = app(OrderTotalsCalculator::class);

        $calculation = $calculator->calculate(
            $sessionCart->items(),
            null,
            $sessionCart->fulfillmentMethod(),
            $sessionCart->deliveryZip(),
        );

        $coupon = $calculator->validateCouponCode(
            $validated['couponCode'],
            $calculation['subtotal_cents'],
        );

        $sessionCart->setCouponCode($coupon->code);

        $this->resetValidation('couponCode');
        $this->synchronizeCart();

        $this->statusMessage =
            $coupon->code.' was applied to the cart.';

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Coupon applied',
            message: $this->statusMessage,
        );
    }

    /**
     * Remove the currently applied coupon without affecting cart lines.
     */
    public function removeCoupon(): void
    {
        app(SessionCart::class)->setCouponCode(null);

        $this->resetValidation('couponCode');
        $this->synchronizeCart();

        $this->statusMessage = 'The coupon was removed from the cart.';

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Coupon removed',
            message: $this->statusMessage,
        );
    }

    /**
     * Persist pickup or local delivery immediately when it is selected.
     */
    public function selectFulfillment(string $fulfillmentMethod): void
    {
        $validated = validator(
            [
                'fulfillmentMethod' => $fulfillmentMethod,
            ],
            [
                'fulfillmentMethod' => [
                    'required',
                    Rule::enum(FulfillmentMethod::class),
                ],
            ],
        )->validate();

        $method = FulfillmentMethod::from(
            (string) $validated['fulfillmentMethod'],
        );

        $sessionCart = app(SessionCart::class);
        $sessionCart->setFulfillmentMethod($method);

        $this->fulfillmentMethod = $method->value;
        $this->deliveryZip = $sessionCart->deliveryZip() ?? '';
        $this->deliveryZipAttempted = false;

        $this->resetValidation([
            'fulfillmentMethod',
            'deliveryZip',
        ]);

        $this->synchronizeCart();

        $this->statusMessage = $method === FulfillmentMethod::Pickup
            ? 'Pickup selected.'
            : 'Local delivery selected. Enter a ZIP code to confirm eligibility.';
    }

    /**
     * Validate and persist the delivery ZIP and resulting fulfillment totals.
     */
    public function saveFulfillment(): void
    {
        $this->deliveryZipAttempted = true;

        $validated = $this->validate([
            'fulfillmentMethod' => [
                'required',
                Rule::enum(FulfillmentMethod::class),
            ],
            'deliveryZip' => [
                Rule::requiredIf(
                    $this->fulfillmentMethod
                        === FulfillmentMethod::Delivery->value,
                ),
                'nullable',
                'regex:/^\d{5}$/',
            ],
        ]);

        $method = FulfillmentMethod::from(
            $validated['fulfillmentMethod'],
        );

        $deliveryZip = $method === FulfillmentMethod::Delivery
            ? $validated['deliveryZip']
            : null;

        $sessionCart = app(SessionCart::class);

        $calculation = app(
            OrderTotalsCalculator::class,
        )->calculate(
            $sessionCart->items(),
            $sessionCart->couponCode(),
            $method,
            $deliveryZip,
        );

        if ($calculation['fulfillment_error'] !== null) {
            $this->addError(
                'deliveryZip',
                $calculation['fulfillment_error'],
            );

            $this->statusMessage =
                'The delivery details need attention.';

            return;
        }

        $sessionCart->setFulfillmentMethod($method);
        $sessionCart->setDeliveryZip($deliveryZip);

        $this->resetValidation([
            'fulfillmentMethod',
            'deliveryZip',
        ]);

        $this->synchronizeCart();

        $this->deliveryZipAttempted =
            $method === FulfillmentMethod::Delivery;

        $this->statusMessage = $method === FulfillmentMethod::Delivery
            ? 'Local delivery is available for '.$this->deliveryZip.'.'
            : 'Pickup selected.';

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Fulfillment updated',
            message: $this->statusMessage,
        );
    }

    /**
     * Remove one configured cart line and preserve all unaffected state.
     */
    public function removeItem(string $lineKey): void
    {
        app(SessionCart::class)->remove($lineKey);

        $this->resetValidation();
        $this->synchronizeCart();

        $this->dispatch('cart-updated');

        $this->statusMessage = 'The item was removed from the cart.';

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Item removed',
            message: $this->statusMessage,
        );
    }

    /**
     * Clear every line and all cart-level pricing selections.
     */
    public function clearCart(): void
    {
        app(SessionCart::class)->clear();

        $this->quantities = [];
        $this->couponCode = '';
        $this->fulfillmentMethod =
            FulfillmentMethod::Pickup->value;
        $this->deliveryZip = '';
        $this->deliveryZipAttempted = false;
        $this->warning = null;

        $this->resetValidation();
        $this->dispatch('cart-updated');

        $this->statusMessage = 'The shopping cart is now empty.';

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Cart cleared',
            message: $this->statusMessage,
        );
    }

    /**
     * Render the server-authoritative cart with presentation-only image data.
     */
    public function render(): View
    {
        return view('livewire.cart.cart-page', [
            'cart' => $this->calculateCart(
                includePresentation: true,
            ),
            'fulfillmentOptions' => FulfillmentMethod::options(),
        ]);
    }

    /**
     * Remove stale items and invalid saved coupons, then synchronize form state.
     */
    private function synchronizeCart(): void
    {
        $this->warning = null;

        $sessionCart = app(SessionCart::class);
        $calculation = $this->calculateCart();

        if ($calculation['invalid_line_keys'] !== []) {
            $sessionCart->removeMany(
                $calculation['invalid_line_keys'],
            );

            $this->warning =
                'One or more unavailable items or stale options were removed from your cart.';

            $calculation = $this->calculateCart();
        }

        if (
            $sessionCart->couponCode() !== null
            && $calculation['coupon_error'] !== null
        ) {
            $couponWarning = $calculation['coupon_error'];

            $sessionCart->setCouponCode(null);

            $this->warning = $this->warning === null
                ? $couponWarning
                : $this->warning.' '.$couponWarning;

            $calculation = $this->calculateCart();
        }

        $this->quantities = [];

        foreach ($calculation['items'] as $item) {
            $this->quantities[$item['key']] =
                $item['quantity'];
        }

        $this->couponCode =
            $sessionCart->couponCode() ?? '';

        $this->fulfillmentMethod =
            $sessionCart->fulfillmentMethod()->value;

        $this->deliveryZip =
            $sessionCart->deliveryZip() ?? '';

        $this->deliveryZipAttempted =
            $sessionCart->fulfillmentMethod()
                === FulfillmentMethod::Delivery
            && $sessionCart->deliveryZip() !== null;
    }

    /**
     * Calculate current totals and optionally enrich lines for UI rendering.
     *
     * @return array<string, mixed>
     */
    private function calculateCart(
        bool $includePresentation = false,
    ): array {
        $sessionCart = app(SessionCart::class);

        $calculation = app(
            OrderTotalsCalculator::class,
        )->calculate(
            $sessionCart->items(),
            $sessionCart->couponCode(),
            $sessionCart->fulfillmentMethod(),
            $sessionCart->deliveryZip(),
        );

        if (
            ! $includePresentation
            || $calculation['items'] === []
        ) {
            return $calculation;
        }

        return $this->addItemPresentation($calculation);
    }

    /**
     * Add responsive food-image metadata without changing authoritative prices.
     *
     * @param  array<string, mixed>  $calculation
     * @return array<string, mixed>
     */
    private function addItemPresentation(
        array $calculation,
    ): array {
        $menuItemIds = [];

        foreach ($calculation['items'] as $item) {
            $menuItemIds[] = (int) $item['menu_item_id'];
        }

        $menuItems = MenuItem::query()
            ->whereKey($menuItemIds)
            ->get()
            ->keyBy('id');

        foreach ($calculation['items'] as $index => $item) {
            $menuItem = $menuItems->get(
                (int) $item['menu_item_id'],
            );

            $calculation['items'][$index]['image_url'] =
                $menuItem instanceof MenuItem
                ? $menuItem->responsiveImageUrl()
                : null;

            $calculation['items'][$index]['image_srcset'] =
                $menuItem instanceof MenuItem
                ? $menuItem->responsiveImageSrcset()
                : null;

            $calculation['items'][$index]['image_alt_text'] =
                $menuItem instanceof MenuItem
                ? ($menuItem->image_alt_text ?: $menuItem->name)
                : $item['name'];
        }

        return $calculation;
    }

    /**
     * Apply a one-step quantity change using the latest server session value.
     */
    private function changeQuantity(
        string $lineKey,
        int $change,
    ): void {
        $sessionCart = app(SessionCart::class);
        $items = $sessionCart->items();
        $validationKey = 'quantities.'.$lineKey;

        if (! isset($items[$lineKey])) {
            $this->addError(
                'cart',
                'The selected cart item no longer exists.',
            );

            $this->synchronizeCart();

            return;
        }

        $currentQuantity = $items[$lineKey]['quantity'];
        $nextQuantity = $currentQuantity + $change;

        if (
            $nextQuantity < 1
            || $nextQuantity > SessionCart::MAX_QUANTITY
        ) {
            $this->quantities[$lineKey] = $currentQuantity;

            $this->addError(
                $validationKey,
                sprintf(
                    'Quantity must be between 1 and %d.',
                    SessionCart::MAX_QUANTITY,
                ),
            );

            $this->statusMessage =
                'The requested quantity is not available.';

            return;
        }

        $this->persistQuantity(
            $lineKey,
            $nextQuantity,
        );
    }

    /**
     * Persist one valid quantity and refresh totals and the header cart count.
     */
    private function persistQuantity(
        string $lineKey,
        int $quantity,
    ): void {
        app(SessionCart::class)->update(
            $lineKey,
            $quantity,
        );

        $this->resetValidation(
            'quantities.'.$lineKey,
        );

        $this->synchronizeCart();
        $this->dispatch('cart-updated');

        $this->statusMessage = sprintf(
            'Quantity updated to %d.',
            $quantity,
        );
    }
}
