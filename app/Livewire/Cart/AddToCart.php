<?php

namespace App\Livewire\Cart;

use App\Models\MenuItem;
use App\Support\Cart\SessionCart;
use App\Support\Orders\OrderPriceCalculator;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class AddToCart extends Component
{
    public MenuItem $menuItem;

    public int|string $quantity = 1;

    /**
     * Selected option values indexed by option-group ID.
     *
     * @var array<int, int|string|array<int, int|string>|null>
     */
    public array $selections = [];

    /**
     * Initialize the component with stable option and quantity state.
     */
    public function mount(MenuItem $menuItem): void
    {
        $this->menuItem = $menuItem;

        $this->quantity = max(
            1,
            min(
                SessionCart::MAX_QUANTITY,
                request()->integer('quantity', 1),
            ),
        );

        $freshMenuItem = $this->loadMenuItem();

        foreach ($freshMenuItem->optionGroups as $group) {
            $this->selections[$group->id] =
                $group->maximum_selections === 1
                    ? null
                    : [];
        }
    }

    /**
     * Validate and add the configured item to the session cart.
     */
    public function addToCart(): void
    {
        $validated = $this->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:'.SessionCart::MAX_QUANTITY,
            ],
        ]);

        $freshMenuItem = $this->loadMenuItem();
        $optionIds = $this->selectedOptionIds();

        app(OrderPriceCalculator::class)->validateSelection(
            $freshMenuItem->id,
            $optionIds,
        );

        app(SessionCart::class)->add(
            $freshMenuItem->id,
            $optionIds,
            (int) $validated['quantity'],
        );

        $this->quantity = 1;

        $this->dispatch('cart-updated');

        $this->dispatch(
            'cart-notification',
            type: 'success',
            title: 'Added to cart',
            message: $freshMenuItem->name.' was added to your cart.',
        );
    }

    /**
     * Render the current item and its available option definitions.
     */
    public function render(): View
    {
        return view('livewire.cart.add-to-cart', [
            'item' => $this->loadMenuItem(),
        ]);
    }

    /**
     * Reload the current public item and available options from the database.
     */
    private function loadMenuItem(): MenuItem
    {
        return MenuItem::query()
            ->whereKey($this->menuItem->getKey())
            ->visible()
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
            ->firstOrFail();
    }

    /**
     * Flatten single-choice and multiple-choice group state into option IDs.
     *
     * @return list<int>
     */
    private function selectedOptionIds(): array
    {
        $optionIds = [];

        foreach ($this->selections as $selection) {
            $values = is_array($selection)
                ? $selection
                : [$selection];

            foreach ($values as $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                $optionIds[] = (int) $value;
            }
        }

        return $optionIds;
    }
}
