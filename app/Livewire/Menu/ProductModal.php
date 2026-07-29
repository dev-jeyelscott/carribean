<?php

namespace App\Livewire\Menu;

use App\Models\MenuItem;
use App\Support\Cart\SessionCart;
use App\Support\Money;
use App\Support\Orders\OrderPriceCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

final class ProductModal extends Component
{
    public ?int $menuItemId = null;

    public int|string $quantity = 1;

    /**
     * Selected user-controlled values indexed by option-group ID.
     *
     * The nested values remain mixed until normalized and validated because
     * Livewire state may contain malformed or stale browser input.
     *
     * @var array<int, mixed>
     */
    public array $selections = [];

    /**
     * Load one public item and prepare its modal selection state.
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
     * Request an animated browser-side close without discarding the last item.
     *
     * Retaining the rendered item allows the closing animation to complete
     * before the dialog leaves the browser top layer.
     */
    public function close(): void
    {
        $this->resetValidation();

        $this->dispatch('product-modal-close');
    }

    /**
     * Increase the configured quantity by one up to the cart maximum.
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
     * Decrease the configured quantity while retaining the minimum of one.
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
     * Validate the latest item state and add its configuration to the cart.
     */
    public function addToCart(): void
    {
        $this->resetValidation([
            'cart',
            'selections',
        ]);

        try {
            $validated = $this->validate([
                'quantity' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:'.SessionCart::MAX_QUANTITY,
                ],
            ]);
        } catch (ValidationException $exception) {
            $this->handleValidationFailure($exception);

            return;
        }

        $menuItem = $this->menuItemId === null
            ? null
            : $this->loadPublicMenuItem($this->menuItemId);

        if (! $menuItem instanceof MenuItem) {
            $this->handleValidationFailure(
                ValidationException::withMessages([
                    'cart' => 'This menu item is no longer available.',
                ]),
            );

            return;
        }

        if (! $menuItem->is_available) {
            $this->handleValidationFailure(
                ValidationException::withMessages([
                    'cart' => $menuItem->name
                        .' is currently unavailable.',
                ]),
            );

            return;
        }

        if (
            ! $menuItem->is_purchasable
            || $menuItem->price_cents === null
        ) {
            $this->handleValidationFailure(
                ValidationException::withMessages([
                    'cart' => $menuItem->name
                        .' cannot currently be ordered online.',
                ]),
            );

            return;
        }

        try {
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
     * Render the selected item and its presentation-only calculated total.
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
     * Load one visible menu item with its public category and available options.
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
                    Relation $relation,
                ): void {
                    $relation
                        ->getQuery()
                        ->reorder()
                        ->orderBy('menu_item_option_groups.sort_order')
                        ->orderBy('menu_item_option_groups.name')
                        ->with([
                            'options' => function (
                                Relation $optionRelation,
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
     * Flatten the current option-group state into strict option identifiers.
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
     * Return safe numeric selections for presentation-only total updates.
     *
     * Invalid or stale values are ignored here and rejected by the
     * authoritative server-side validator during Add to Cart.
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
     * Calculate the modal's immediate display total from loaded options.
     *
     * This value is never trusted for cart or checkout pricing.
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
     * Normalize the current quantity for presentation calculations.
     */
    private function normalizedQuantity(): int
    {
        $quantity = $this->quantity;

        if (is_int($quantity)) {
            return max(
                1,
                min(
                    SessionCart::MAX_QUANTITY,
                    $quantity,
                ),
            );
        }

        if (! ctype_digit($quantity)) {
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
     * Copy domain-validation messages into Livewire's error bag.
     */
    private function handleValidationFailure(
        ValidationException $exception,
    ): void {
        $firstMessage = null;

        foreach ($exception->errors() as $key => $messages) {
            foreach ($messages as $message) {
                if (! $this->getErrorBag()->has($key)) {
                    $this->addError($key, $message);
                }

                $firstMessage ??= $message;
            }
        }

        $message = $firstMessage
            ?? 'The selected item could not be added to your cart.';

        $this->dispatch('product-modal-error');

        $this->notify(
            type: 'error',
            title: 'Unable to add item',
            message: $message,
        );
    }

    /**
     * Dispatch one reusable public notification.
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
