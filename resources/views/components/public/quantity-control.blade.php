@props([
    'quantity',
    'decrementAction',
    'incrementAction',
    'model' => 'quantity',
    'minimum' => 1,
    'maximum' => 20,
    'label' => 'Quantity',
])

<div
    data-product-quantity
    class="inline-flex items-center overflow-hidden rounded-full
        border border-primary/12 bg-canvas shadow-sm"
    role="group"
    aria-label="{{ $label }}">
    <button
        type="button"
        wire:click="{{ $decrementAction }}"
        wire:loading.attr="disabled"
        wire:target="{{ $decrementAction }}"
        data-product-quantity-decrement
        @disabled((int) $quantity <= $minimum)
        class="inline-flex size-12 items-center justify-center
            text-primary transition hover:bg-surface-soft
            disabled:cursor-not-allowed disabled:opacity-35"
        aria-label="Decrease {{ strtolower($label) }}">
        <svg
            class="size-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true">
            <path
                stroke-linecap="round"
                d="M6 12h12" />
        </svg>
    </button>

    <input
        type="number"
        min="{{ $minimum }}"
        max="{{ $maximum }}"
        step="1"
        inputmode="numeric"
        wire:model.live.debounce.250ms="{{ $model }}"
        data-product-quantity-input
        class="h-12 w-14 border-0 bg-transparent p-0 text-center
            text-sm font-semibold tabular-nums text-ink outline-none
            [appearance:textfield]
            [&::-webkit-inner-spin-button]:appearance-none
            [&::-webkit-outer-spin-button]:appearance-none"
        aria-label="{{ $label }}">

    <button
        type="button"
        wire:click="{{ $incrementAction }}"
        wire:loading.attr="disabled"
        wire:target="{{ $incrementAction }}"
        data-product-quantity-increment
        @disabled((int) $quantity >= $maximum)
        class="inline-flex size-12 items-center justify-center
            text-primary transition hover:bg-surface-soft
            disabled:cursor-not-allowed disabled:opacity-35"
        aria-label="Increase {{ strtolower($label) }}">
        <svg
            class="size-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            aria-hidden="true">
            <path
                stroke-linecap="round"
                d="M12 6v12M6 12h12" />
        </svg>
    </button>
</div>
