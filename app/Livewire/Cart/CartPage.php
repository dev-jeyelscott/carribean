<?php

namespace App\Livewire\Cart;

use App\Enums\FulfillmentMethod;
use App\Support\Cart\SessionCart;
use App\Support\Orders\OrderTotalsCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class CartPage extends Component
{
    /**
     * Editable quantities indexed by cart line key.
     *
     * @var array<string, int|string>
     */
    public array $quantities = [];

    public string $couponCode = '';

    public string $fulfillmentMethod =
        FulfillmentMethod::Pickup->value;

    public string $deliveryZip = '';

    public ?string $warning = null;

    /**
     * Remove stale lines and load persisted pricing selections.
     */
    public function mount(): void
    {
        $this->synchronizeCart();
    }

    /**
     * Update one cart-line quantity after validation.
     */
    public function updateQuantity(
        string $lineKey,
    ): void {
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

        app(SessionCart::class)->update(
            $lineKey,
            (int) $validated['quantities'][$lineKey],
        );

        $this->synchronizeCart();

        $this->dispatch('cart-updated');

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Cart updated',
            message: 'The item quantity was updated.',
        );
    }

    /**
     * Apply a validated coupon to the session cart.
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
        $calculator = app(
            OrderTotalsCalculator::class,
        );

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

        $this->couponCode = $coupon->code;

        $this->resetValidation('couponCode');

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Coupon applied',
            message: $coupon->code.' was applied to your cart.',
        );
    }

    /**
     * Remove the currently applied coupon.
     */
    public function removeCoupon(): void
    {
        app(SessionCart::class)->setCouponCode(null);

        $this->couponCode = '';

        $this->resetValidation('couponCode');

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Coupon removed',
            message: 'The coupon was removed from your cart.',
        );
    }

    /**
     * Validate and persist pickup or local-delivery selection.
     */
    public function saveFulfillment(): void
    {
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

        $fulfillmentMethod =
            FulfillmentMethod::from(
                $validated['fulfillmentMethod'],
            );

        $deliveryZip =
            $fulfillmentMethod
            === FulfillmentMethod::Delivery
            ? $validated['deliveryZip']
            : null;

        $sessionCart = app(SessionCart::class);

        $calculation = app(
            OrderTotalsCalculator::class,
        )->calculate(
            $sessionCart->items(),
            $sessionCart->couponCode(),
            $fulfillmentMethod,
            $deliveryZip,
        );

        if (
            $calculation['fulfillment_error']
            !== null
        ) {
            throw ValidationException::withMessages([
                'deliveryZip' => $calculation['fulfillment_error'],
            ]);
        }

        $sessionCart->setFulfillmentMethod(
            $fulfillmentMethod,
        );

        $sessionCart->setDeliveryZip($deliveryZip);

        $this->deliveryZip =
            $sessionCart->deliveryZip() ?? '';

        $this->resetValidation([
            'fulfillmentMethod',
            'deliveryZip',
        ]);

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Fulfillment updated',
            message: $fulfillmentMethod->label()
                .' was selected.',
        );
    }

    /**
     * Remove one configured line from the cart.
     */
    public function removeItem(string $lineKey): void
    {
        app(SessionCart::class)->remove($lineKey);

        $this->resetValidation();
        $this->synchronizeCart();

        $this->dispatch('cart-updated');

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Item removed',
            message: 'The item was removed from your cart.',
        );
    }

    /**
     * Remove the cart and all of its pricing selections.
     */
    public function clearCart(): void
    {
        app(SessionCart::class)->clear();

        $this->quantities = [];
        $this->couponCode = '';
        $this->fulfillmentMethod =
            FulfillmentMethod::Pickup->value;
        $this->deliveryZip = '';
        $this->warning = null;

        $this->resetValidation();

        $this->dispatch('cart-updated');

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Cart cleared',
            message: 'Your shopping cart is now empty.',
        );
    }

    /**
     * Render current server-authoritative cart totals.
     */
    public function render(): View
    {
        return view('livewire.cart.cart-page', [
            'cart' => $this->calculateCart(),
            'fulfillmentOptions' => FulfillmentMethod::options(),
        ]);
    }

    /**
     * Remove stale items and expired or invalid saved coupons.
     */
    private function synchronizeCart(): void
    {
        $this->warning = null;

        $sessionCart = app(SessionCart::class);

        $calculation = $this->calculateCart();

        if (
            $calculation['invalid_line_keys'] !== []
        ) {
            $sessionCart->removeMany(
                $calculation['invalid_line_keys'],
            );

            $this->warning =
                'One or more unavailable items were removed from your cart.';

            $calculation = $this->calculateCart();
        }

        if (
            $sessionCart->couponCode() !== null
            && $calculation['coupon_error'] !== null
        ) {
            $couponWarning =
                $calculation['coupon_error'];

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
            $sessionCart
                ->fulfillmentMethod()
                ->value;

        $this->deliveryZip =
            $sessionCart->deliveryZip() ?? '';
    }

    /**
     * Calculate totals using persisted session-cart context.
     *
     * @return array<string, mixed>
     */
    private function calculateCart(): array
    {
        $sessionCart = app(SessionCart::class);

        return app(
            OrderTotalsCalculator::class,
        )->calculate(
            $sessionCart->items(),
            $sessionCart->couponCode(),
            $sessionCart->fulfillmentMethod(),
            $sessionCart->deliveryZip(),
        );
    }
}
