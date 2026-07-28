@props([
    'item',
    'quantity' => 1,
])

@php
    $formattedPrice = $item->formattedPrice();
    $imageAlt = $item->image_alt_text ?: $item->name;

    $canOrder = $item->is_available
        && $item->is_purchasable;

    $requiresConfiguration = (int) $item->getAttribute(
        'option_groups_count',
    ) > 0;

    $itemUrl = route('menu-items.show', [
        'menuItem' => $item,
        'quantity' => $quantity,
    ]);
@endphp

<article
    {{ $attributes->class([
        'group flex h-full min-w-0 flex-col overflow-hidden rounded-card',
        'border border-primary/10 bg-surface shadow-card transition',
        'duration-300 hover:-translate-y-1 hover:border-primary/20',
        'hover:shadow-panel motion-reduce:transform-none',
        'motion-reduce:transition-none',
        'opacity-65' => ! $item->is_available,
    ]) }}
    data-menu-card
    data-menu-item-id="{{ $item->id }}">
    <a
        href="{{ route('menu-items.show', $item) }}"
        class="relative block aspect-[4/3] overflow-hidden bg-surface-soft
            focus-visible:outline-none focus-visible:ring-2
            focus-visible:ring-primary focus-visible:ring-inset"
        aria-label="View {{ $item->name }}">
        @if ($item->image_url)
            <x-public.responsive-image
                :image="$item"
                :alt="$imageAlt"
                variant="card"
                sizes="(min-width: 1536px) 21vw, (min-width: 1280px) 27vw, (min-width: 640px) 46vw, 100vw"
                width="640"
                height="480"
                img-class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.035] motion-reduce:transform-none motion-reduce:transition-none" />
        @else
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_28%_22%,rgb(242_199_107_/_26%),transparent_34%),linear-gradient(145deg,#206f7c,#0c342b)]">
            </div>

            <span
                class="absolute inset-x-4 bottom-4 text-xs font-semibold
                    uppercase tracking-[0.16em] text-white/75">
                Image coming soon
            </span>
        @endif

        @unless ($item->is_available)
            <span
                class="absolute left-3 top-3 rounded-full bg-canvas/95 px-3 py-1
                    text-[0.62rem] font-semibold uppercase tracking-[0.12em]
                    text-ink shadow-card backdrop-blur">
                Unavailable
            </span>
        @endunless
    </a>

    <div class="flex flex-1 flex-col px-3.5 pb-3.5 pt-3">
        <div class="flex min-w-0 items-center justify-between gap-3">
            <a
                href="{{ route('menu-items.show', $item) }}"
                class="min-w-0 flex-1 rounded-sm focus-visible:outline-none
                    focus-visible:ring-2 focus-visible:ring-primary">
                <h3
                    class="truncate text-sm font-semibold leading-6 text-ink"
                    title="{{ $item->name }}">
                    {{ $item->name }}
                </h3>
            </a>

            <p
                class="shrink-0 text-sm font-semibold tabular-nums text-primary">
                {{ $formattedPrice ?? 'Price pending' }}
            </p>
        </div>

        <div
            class="mt-auto flex items-center justify-between gap-3 border-t
                border-line/80 pt-3">
            <div
                class="inline-flex h-11 items-center overflow-hidden rounded-pill
                    border border-primary/10 bg-canvas"
                role="group"
                aria-label="Quantity for {{ $item->name }}"
                data-menu-quantity>
                <button
                    type="button"
                    wire:click="decrementQuantity({{ $item->id }})"
                    @disabled(! $canOrder)
                    class="inline-flex size-11 items-center justify-center
                        text-primary transition hover:bg-surface-soft
                        disabled:cursor-not-allowed disabled:opacity-35"
                    aria-label="Decrease quantity for {{ $item->name }}"
                    data-menu-quantity-decrease>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true">
                        <path stroke-linecap="round" d="M6 12h12" />
                    </svg>
                </button>

                <span
                    class="min-w-7 text-center text-sm font-semibold
                        tabular-nums text-ink"
                    aria-live="polite"
                    data-menu-quantity-value>
                    {{ $quantity }}
                </span>

                <button
                    type="button"
                    wire:click="incrementQuantity({{ $item->id }})"
                    @disabled(! $canOrder)
                    class="inline-flex size-11 items-center justify-center
                        text-primary transition hover:bg-surface-soft
                        disabled:cursor-not-allowed disabled:opacity-35"
                    aria-label="Increase quantity for {{ $item->name }}"
                    data-menu-quantity-increase>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true">
                        <path stroke-linecap="round" d="M12 6v12M6 12h12" />
                    </svg>
                </button>
            </div>

            @if ($canOrder && $requiresConfiguration)
                <a
                    href="{{ $itemUrl }}"
                    class="inline-flex size-11 shrink-0 items-center justify-center
                        rounded-full bg-primary text-canvas shadow-card
                        transition duration-200 hover:-translate-y-0.5
                        hover:bg-primary-deep hover:shadow-panel
                        active:translate-y-0 active:scale-95
                        motion-reduce:transform-none"
                    aria-label="Choose options and add {{ $item->name }} to cart"
                    title="Choose options for {{ $item->name }}"
                    data-menu-configure>
                    <svg
                        class="size-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5.5 8.5h13l-1 11h-11l-1-11Z" />

                        <path
                            stroke-linecap="round"
                            d="M9 9V6.75a3 3 0 0 1 6 0V9" />
                    </svg>
                </a>
            @elseif ($canOrder)
                <button
                    type="button"
                    wire:click="addToCart({{ $item->id }})"
                    class="inline-flex size-11 shrink-0 items-center justify-center
                        rounded-full bg-primary text-canvas shadow-card
                        transition duration-200 hover:-translate-y-0.5
                        hover:bg-primary-deep hover:shadow-panel
                        active:translate-y-0 active:scale-95
                        disabled:cursor-wait disabled:opacity-60
                        data-loading:pointer-events-none
                        data-loading:opacity-60 motion-reduce:transform-none"
                    aria-label="Add {{ $item->name }} to cart"
                    title="Add {{ $item->name }} to cart"
                    data-menu-direct-add>
                    <svg
                        class="size-5 data-loading:hidden"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5.5 8.5h13l-1 11h-11l-1-11Z" />

                        <path
                            stroke-linecap="round"
                            d="M9 9V6.75a3 3 0 0 1 6 0V9" />
                    </svg>

                    <svg
                        class="hidden size-5 animate-spin data-loading:block
                            motion-reduce:animate-none"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true">
                        <circle
                            class="opacity-30"
                            cx="12"
                            cy="12"
                            r="9"
                            stroke="currentColor"
                            stroke-width="2" />

                        <path
                            d="M21 12a9 9 0 0 0-9-9"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round" />
                    </svg>
                </button>
            @else
                <button
                    type="button"
                    disabled
                    class="inline-flex size-11 shrink-0 cursor-not-allowed
                        items-center justify-center rounded-full bg-line
                        text-muted opacity-70"
                    aria-label="{{ $item->name }} is unavailable for online ordering"
                    title="Unavailable">
                    <svg
                        class="size-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5.5 8.5h13l-1 11h-11l-1-11Z" />

                        <path
                            stroke-linecap="round"
                            d="M9 9V6.75a3 3 0 0 1 6 0V9" />
                    </svg>
                </button>
            @endif
        </div>
    </div>
</article>
