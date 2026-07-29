<?php

namespace App\Livewire\Menu;

use App\Models\MenuItem;
use App\Support\Cart\SessionCart;
use App\Support\Money;
use App\Support\Orders\OrderPriceCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

final class ProductModal extends Component
{
    public ?int $menuItemId = null;

    public int|string $quantity = 1;

    /**
     * Store selected option identifiers by option-group ID.
     *
     * @var array<int, int|string|array<int, int|string>|null>
     */
    public array $selections = [];

    /**
     * Load a public menu item and initialize its modal state.
     */
    #[On('open-product-modal')]
    public function open(int $menuItemId): void
    {
        $this->resetValidation();

        $menuItem = $this->loadPublicMenuItem($menuItemId);

        if (! $menuItem instanceof MenuItem) {
            $this->notify(
                type: 'error',
                title: 'Item unavailable',
                message: 'This menu item is no longer publicly available.',
            );

            return;
        }

        $this->menuItemId = $menuItem->id;
        $this->quantity = 1;
        $this->selections = [];

        foreach ($menuItem->optionGroups as $optionGroup) {
            $this->selections[$optionGroup->id] =
                $optionGroup->maximum_selections === 1
                    ? null
                    : [];
        }

        $this->dispatch(
            'product-modal-open',
            menuItemId: $menuItem->id,
        );
    }

    /**
     * Dispatch the browser event that performs the animated modal close.
     */
    public function close(): void
    {
        $this->resetValidation();

        $this->dispatch('product-modal-close');
    }

    /**
     * Increase the requested quantity without exceeding the cart maximum.
     */
    public function incrementQuantity(): void
    {
        $this->quantity = min(
            SessionCart::MAX_QUANTITY,
            $this->normalizedQuantity() + 1,
        );

        $this->resetValidation('quantity');
    }

    /**
     * Decrease the requested quantity while retaining a minimum of one.
     */
    public function decrementQuantity(): void
    {
        $this->quantity = max(
            1,
            $this->normalizedQuantity() - 1,
        );

        $this->resetValidation('quantity');
    }

    /**
     * Validate the current configuration and add it to the session cart.
     */
    public function addToCart(): void
    {
        $this->resetValidation();

        try {
            $validated = $this->validate([
                'quantity' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:'.SessionCart::MAX_QUANTITY,
                ],
            ]);

            $menuItem = $this->menuItemId === null
                ? null
                : $this->loadPublicMenuItem($this->menuItemId);

            if (! $menuItem instanceof MenuItem) {
                throw ValidationException::withMessages([
                    'cart' => 'This menu item is no longer available.',
                ]);
            }

            if (! $menuItem->is_available) {
                throw ValidationException::withMessages([
                    'cart' => $menuItem->name
                        .' is currently unavailable.',
                ]);
            }

            if (
                ! $menuItem->is_purchasable
                || $menuItem->price_cents === null
            ) {
                throw ValidationException::withMessages([
                    'cart' => $menuItem->name
                        .' cannot currently be ordered online.',
                ]);
            }

            $optionIds = $this->selectedOptionIds();

            app(OrderPriceCalculator::class)->validateSelection(
                $menuItem->id,
                $optionIds,
            );

            app(SessionCart::class)->add(
                menuItemId: $menuItem->id,
                optionIds: $optionIds,
                quantity: (int) $validated['quantity'],
            );
        } catch (ValidationException $exception) {
            $this->handleValidationFailure($exception);

            return;
        }

        $this->dispatch('cart-updated');
        $this->dispatch('product-modal-close');

        $this->notify(
            type: 'success',
            title: 'Added to cart',
            message: $menuItem->name.' was added to your cart.',
        );
    }

    /**
     * Render the currently selected product and its preview total.
     */
    public function render(): View
    {
        $menuItem = $this->menuItemId === null
            ? null
            : $this->loadPublicMenuItem($this->menuItemId);

        $displayTotalCents = $menuItem instanceof MenuItem
            ? $this->displayTotalCents($menuItem)
            : null;

        return view('livewire.menu.product-modal', [
            'item' => $menuItem,
            'displayTotal' => Money::formatUsd(
                $displayTotalCents,
            ),
        ]);
    }

    /**
     * Load a visible item, category, option groups, and available options.
     */
    private function loadPublicMenuItem(
        int $menuItemId,
    ): ?MenuItem {
        return MenuItem::query()
            ->whereKey($menuItemId)
            ->visible()
            ->whereHas(
                'menuCategory',
                fn (Builder $query) => $query->where(
                    'is_visible',
                    true,
                ),
            )
            ->with([
                'menuCategory',
                'optionGroups' => function (
                    HasMany $relation,
                ): void {
                    $relation
                        ->getQuery()
                        ->reorder()
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->with([
                            'options' => function (
                                HasMany $optionRelation,
                            ): void {
                                $optionRelation
                                    ->getQuery()
                                    ->reorder()
                                    ->where(
                                        'menu_item_options.is_available',
                                        true,
                                    )
                                    ->orderBy(
                                        'menu_item_options.sort_order',
                                    )
                                    ->orderBy(
                                        'menu_item_options.name',
                                    );
                            },
                        ]);
                },
            ])
            ->first();
    }

    /**
     * Flatten Livewire option-group state into validated integer IDs.
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

                if (
                    ! is_int($value)
                    && ! (
                        is_string($value)
                        && ctype_digit($value)
                    )
                ) {
                    throw ValidationException::withMessages([
                        'selections' => 'One or more selected options are invalid.',
                    ]);
                }

                $optionIds[] = (int) $value;
            }
        }

        return $optionIds;
    }

    /**
     * Return valid numeric option IDs for preview calculations.
     *
     * Invalid values are ignored here and rejected during cart validation.
     *
     * @return list<int>
     */
    private function displayOptionIds(): array
    {
        $optionIds = [];

        foreach ($this->selections as $selection) {
            $values = is_array($selection)
                ? $selection
                : [$selection];

            foreach ($values as $value) {
                if (is_int($value) && $value > 0) {
                    $optionIds[] = $value;

                    continue;
                }

                if (
                    is_string($value)
                    && ctype_digit($value)
                    && (int) $value > 0
                ) {
                    $optionIds[] = (int) $value;
                }
            }
        }

        return array_values(array_unique($optionIds));
    }

    /**
     * Calculate the current presentation-only product total.
     */
    private function displayTotalCents(
        MenuItem $menuItem,
    ): ?int {
        if ($menuItem->price_cents === null) {
            return null;
        }

        $selectedOptionIds = $this->displayOptionIds();
        $optionTotalCents = 0;

        foreach ($menuItem->optionGroups as $optionGroup) {
            foreach ($optionGroup->options as $option) {
                if (
                    in_array(
                        $option->id,
                        $selectedOptionIds,
                        true,
                    )
                ) {
                    $optionTotalCents +=
                        $option->additional_price_cents;
                }
            }
        }

        return (
            (int) $menuItem->price_cents
            + $optionTotalCents
        ) * $this->normalizedQuantity();
    }

    /**
     * Normalize quantity into the supported session-cart range.
     */
    private function normalizedQuantity(): int
    {
        $quantity = $this->quantity;

        if (
            ! is_int($quantity)
            && ! (
                is_string($quantity)
                && ctype_digit($quantity)
            )
        ) {
            return 1;
        }

        return max(
            1,
            min(
                SessionCart::MAX_QUANTITY,
                (int) $quantity,
            ),
        );
    }

    /**
     * Add domain validation messages to Livewire's error bag.
     */
    private function handleValidationFailure(
        ValidationException $exception,
    ): void {
        $firstMessage = null;

        foreach ($exception->errors() as $key => $messages) {
            foreach ($messages as $message) {
                if (! is_string($message)) {
                    continue;
                }

                if (! $this->getErrorBag()->has($key)) {
                    $this->addError($key, $message);
                }

                $firstMessage ??= $message;
            }
        }

        $this->dispatch('product-modal-error');

        $this->notify(
            type: 'error',
            title: 'Unable to add item',
            message: $firstMessage
                ?? 'The selected item could not be added to your cart.',
        );
    }

    /**
     * Dispatch a reusable public cart notification.
     */
    private function notify(
        string $type,
        string $title,
        string $message,
    ): void {
        $this->dispatch(
            'cart-notification',
            type: $type,
            title: $title,
            message: $message,
        );
    }
}
