<?php

namespace App\Livewire\Cart;

use App\Support\Cart\SessionCart;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class CartCount extends Component
{
    /**
     * Trigger a rerender when another component changes the cart.
     */
    #[On('cart-updated')]
    public function refreshCount(): void
    {
        // The empty action intentionally triggers a component rerender.
    }

    /**
     * Render the current number of units in the session cart.
     */
    public function render(): View
    {
        return view('livewire.cart.cart-count', [
            'count' => app(SessionCart::class)->count(),
        ]);
    }
}
