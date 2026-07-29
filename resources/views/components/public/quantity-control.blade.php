@props([
    'quantity',
    'decrementAction',
    'incrementAction',
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

    <span
        class="min-w-10 text-center text-sm font-semibold
            tabular-nums text-ink"
        aria-live="polite">
        {{ $quantity }}
    </span>

    <button
        type="button"
        wire:click="{{ $incrementAction }}"
        wire:loading.attr="disabled"
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
