<?php

namespace App\Livewire\Menu;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Support\Cart\SessionCart;
use App\Support\Orders\OrderPriceCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class Catalog extends Component
{
    private const ITEMS_PER_BATCH = 8;

    /**
     * Number of visible items currently rendered for each category.
     *
     * @var array<int, int>
     */
    public array $visibleCounts = [];

    /**
     * Selected card quantity indexed by menu-item ID.
     *
     * @var array<int, int|string>
     */
    public array $quantities = [];

    /**
     * Initialize each visible category with its first item batch.
     */
    public function mount(): void
    {
        $categoryIds = MenuCategory::query()
            ->visible()
            ->ordered()
            ->pluck('id');

        foreach ($categoryIds as $categoryId) {
            $this->visibleCounts[(int) $categoryId] =
                self::ITEMS_PER_BATCH;
        }
    }

    /**
     * Increase one card quantity without exceeding the cart limit.
     */
    public function incrementQuantity(int $menuItemId): void
    {
        $this->quantities[$menuItemId] = min(
            SessionCart::MAX_QUANTITY,
            $this->quantityFor($menuItemId) + 1,
        );
    }

    /**
     * Decrease one card quantity while retaining the minimum of one.
     */
    public function decrementQuantity(int $menuItemId): void
    {
        $this->quantities[$menuItemId] = max(
            1,
            $this->quantityFor($menuItemId) - 1,
        );
    }

    /**
     * Add a simple, non-configurable menu item to the session cart.
     */
    public function addToCart(int $menuItemId): void
    {
        $menuItem = $this->findPublicMenuItem($menuItemId);

        if (! $menuItem->is_available) {
            $this->notify(
                type: 'error',
                title: 'Item unavailable',
                message: $menuItem->name.' is currently unavailable.',
            );

            return;
        }

        if (! $menuItem->is_purchasable) {
            $this->notify(
                type: 'error',
                title: 'Online ordering unavailable',
                message: $menuItem->name.' cannot currently be ordered online.',
            );

            return;
        }

        if ($menuItem->optionGroups()->exists()) {
            $this->notify(
                type: 'info',
                title: 'Choose your options',
                message: 'Open the item to select its required options.',
            );

            return;
        }

        $quantity = $this->quantityFor($menuItemId);

        try {
            app(OrderPriceCalculator::class)->validateSelection(
                $menuItem->id,
                [],
            );

            app(SessionCart::class)->add(
                menuItemId: $menuItem->id,
                optionIds: [],
                quantity: $quantity,
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())
                ->flatten()
                ->first();

            $this->notify(
                type: 'error',
                title: 'Unable to add item',
                message: is_string($message)
                    ? $message
                    : 'The item could not be added to your cart.',
            );

            return;
        }

        $this->quantities[$menuItemId] = 1;

        $this->dispatch('cart-updated');

        $this->notify(
            type: 'success',
            title: 'Added to cart',
            message: $menuItem->name.' was added to your cart.',
        );
    }

    /**
     * Append another item batch for one public category.
     */
    public function loadMore(int $categoryId): void
    {
        $category = MenuCategory::query()
            ->visible()
            ->findOrFail($categoryId);

        $totalItemCount = $category->menuItems()
            ->visible()
            ->count();

        $currentCount = $this->visibleCounts[$categoryId]
            ?? self::ITEMS_PER_BATCH;

        $this->visibleCounts[$categoryId] = min(
            $totalItemCount,
            $currentCount + self::ITEMS_PER_BATCH,
        );
    }

    /**
     * Render visible categories and their current item batches.
     */
    public function render(): View
    {
        $categories = MenuCategory::query()
            ->visible()
            ->withCount([
                'menuItems as visible_menu_items_count' => fn (Builder $query) => $query->visible(),
            ])
            ->ordered()
            ->get();

        foreach ($categories as $category) {
            $totalItemCount = (int) $category->getAttribute(
                'visible_menu_items_count',
            );

            $requestedCount = $this->visibleCounts[$category->id]
                ?? self::ITEMS_PER_BATCH;

            $visibleCount = min(
                $totalItemCount,
                max(self::ITEMS_PER_BATCH, $requestedCount),
            );

            $menuItems = $category->menuItems()
                ->reorder()
                ->visible()
                ->ordered()
                ->withCount('optionGroups')
                ->limit($visibleCount)
                ->get();

            $category->setRelation('menuItems', $menuItems);
        }

        return view('components.public.menu-catalog', [
            'categories' => $categories,
        ]);
    }

    /**
     * Retrieve a menu item that is valid for the public catalogue.
     */
    private function findPublicMenuItem(int $menuItemId): MenuItem
    {
        return MenuItem::query()
            ->visible()
            ->whereHas(
                'menuCategory',
                fn (Builder $query) => $query->where(
                    'is_visible',
                    true,
                ),
            )
            ->findOrFail($menuItemId);
    }

    /**
     * Return a normalized card quantity within the allowed cart range.
     */
    private function quantityFor(int $menuItemId): int
    {
        $value = $this->quantities[$menuItemId] ?? 1;

        if (
            ! is_int($value)
            && ! (
                is_string($value)
                && ctype_digit($value)
            )
        ) {
            return 1;
        }

        return max(
            1,
            min(SessionCart::MAX_QUANTITY, (int) $value),
        );
    }

    /**
     * Dispatch a public cart notification using the existing notification UI.
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
