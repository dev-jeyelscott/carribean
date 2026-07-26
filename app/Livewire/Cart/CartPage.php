<?php

namespace App\Livewire\Cart;

use App\Support\Cart\SessionCart;
use App\Support\Orders\OrderPriceCalculator;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CartPage extends Component
{
    /**
     * Editable quantities indexed by cart line key.
     *
     * @var array<string, int|string>
     */
    public array $quantities = [];

    public ?string $warning = null;

    /**
     * Remove invalid lines and initialize editable quantities.
     */
    public function mount(): void
    {
        $this->synchronizeCart();
    }

    /**
     * Update one cart-line quantity after server-side validation.
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
     * Remove all configured lines from the cart.
     */
    public function clearCart(): void
    {
        app(SessionCart::class)->clear();

        $this->quantities = [];
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
     * Render the cart using current database prices and availability.
     */
    public function render(): View
    {
        return view('livewire.cart.cart-page', [
            'cart' => app(OrderPriceCalculator::class)->calculate(
                app(SessionCart::class)->items(),
            ),
        ]);
    }

    /**
     * Remove unavailable lines and synchronize quantity form state.
     */
    private function synchronizeCart(): void
    {
        $this->warning = null;

        $sessionCart = app(SessionCart::class);
        $calculator = app(OrderPriceCalculator::class);

        $calculation = $calculator->calculate(
            $sessionCart->items(),
        );

        if ($calculation['invalid_line_keys'] !== []) {
            $sessionCart->removeMany(
                $calculation['invalid_line_keys'],
            );

            $this->warning =
                'One or more unavailable items were removed from your cart.';

            $calculation = $calculator->calculate(
                $sessionCart->items(),
            );
        }

        $this->quantities = [];

        foreach ($calculation['items'] as $item) {
            $this->quantities[$item['key']] =
                $item['quantity'];
        }
    }
}
