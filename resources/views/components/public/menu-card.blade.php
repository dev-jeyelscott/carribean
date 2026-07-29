@props([
    'item',
    'eager' => false,
    'priority' => false,
    'showCategory' => true,
])

@php
    /*
     * Resolve all presentation state without changing the product's ordering
     * or availability state.
     */
    $formattedPrice = $item->formattedPrice();
    $imageAlt = $item->image_alt_text ?: $item->name;

    $menuCategory = $item->relationLoaded('menuCategory')
        ? $item->menuCategory
        : null;

    $itemUrl = route('menu-items.show', $item);

    $canOrder = $item->is_available
        && $item->is_purchasable
        && $item->price_cents !== null;
@endphp

<a
    href="{{ $itemUrl }}"
    wire:click.prevent="$dispatchTo(
        'menu.product-modal',
        'open-product-modal',
        { menuItemId: {{ $item->id }} }
    )"
    data-menu-card
    data-menu-card-link
    data-menu-item-id="{{ $item->id }}"
    data-product-modal-trigger
    aria-label="View details and customize {{ $item->name }}"
    class="group block h-full w-full min-w-0 rounded-card
        focus-visible:outline-none focus-visible:ring-2
        focus-visible:ring-primary focus-visible:ring-offset-4
        focus-visible:ring-offset-canvas">
    <article
        @class([
            'menu-card flex h-full w-full min-w-0 flex-col overflow-hidden',
            'rounded-card border border-primary/10 bg-surface shadow-card',
            'transition duration-300 hover:-translate-y-1',
            'hover:border-primary/20 hover:shadow-panel',
            'motion-reduce:transform-none motion-reduce:transition-none',
            'opacity-70' => ! $item->is_available,
        ])>
        <div
            class="relative aspect-[4/3] w-full shrink-0 overflow-hidden
                bg-surface-soft"
            data-menu-card-image>
            @if ($item->image_url)
                <x-public.responsive-image
                    :image="$item"
                    :alt="$imageAlt"
                    variant="card"
                    sizes="(min-width: 1280px) 19vw, (min-width: 1024px) 27vw, (min-width: 640px) 46vw, 84vw"
                    width="720"
                    height="540"
                    :loading="$eager ? 'eager' : 'lazy'"
                    :fetchpriority="$priority ? 'high' : null"
                    img-class="h-full w-full object-cover transition
                        duration-500 ease-island group-hover:scale-[1.045]
                        motion-reduce:transform-none
                        motion-reduce:transition-none" />
            @else
                <div
                    class="absolute inset-0
                        bg-[radial-gradient(circle_at_28%_22%,rgb(242_199_107_/_30%),transparent_34%),linear-gradient(145deg,#206f7c,#0c342b)]"
                    aria-hidden="true">
                </div>

                <span
                    class="absolute inset-x-5 bottom-5 text-xs font-semibold
                        uppercase tracking-[0.16em] text-white/75">
                    Image coming soon
                </span>
            @endif

            @unless ($item->is_available)
                <span
                    class="absolute left-3 top-3 rounded-full bg-canvas/95 px-3
                        py-1 text-[0.62rem] font-semibold uppercase
                        tracking-[0.12em] text-ink shadow-card backdrop-blur">
                    Unavailable
                </span>
            @endunless

            @if ($item->is_available && ! $item->is_purchasable)
                <span
                    class="absolute left-3 top-3 rounded-full bg-canvas/95 px-3
                        py-1 text-[0.62rem] font-semibold uppercase
                        tracking-[0.12em] text-ink shadow-card backdrop-blur">
                    Dine-in menu
                </span>
            @endif
        </div>

        <div class="flex min-h-52 min-w-0 flex-1 flex-col p-5">
            @if ($showCategory && $menuCategory)
                <p
                    class="truncate text-[0.64rem] font-semibold uppercase
                        tracking-[0.18em] text-coral"
                    title="{{ $menuCategory->name }}">
                    {{ $menuCategory->name }}
                </p>
            @endif

            <div class="mt-2 flex min-w-0 items-start justify-between gap-4">
                <h3
                    class="min-w-0 flex-1 truncate font-display text-xl
                        leading-tight text-ink"
                    title="{{ $item->name }}">
                    {{ $item->name }}
                </h3>

                <p
                    class="shrink-0 text-sm font-semibold tabular-nums
                        text-primary">
                    {{ $formattedPrice ?? 'Price pending' }}
                </p>
            </div>

            <p
                class="mt-3 line-clamp-2 min-h-12 overflow-hidden text-sm
                    leading-6 text-muted">
                {{ $item->description
                    ?: 'Discover this Caribbean-inspired Coast & Cay favorite.' }}
            </p>

            <div
                class="mt-auto flex items-center justify-between border-t
                    border-line/80 pt-4">
                <span
                    class="truncate pr-3 text-[0.65rem] font-semibold uppercase
                        tracking-[0.14em] text-muted">
                    {{ $canOrder ? 'Customize order' : 'View details' }}
                </span>

                <span
                    class="inline-flex size-11 shrink-0 items-center
                        justify-center rounded-full bg-primary text-canvas
                        shadow-card transition duration-200
                        group-hover:-translate-y-0.5
                        group-hover:bg-primary-deep
                        group-hover:shadow-panel
                        group-active:translate-y-0 group-active:scale-95
                        motion-reduce:transform-none"
                    aria-hidden="true">
                    <svg
                        class="size-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5.5 8.5h13l-1 11h-11l-1-11Z" />

                        <path
                            stroke-linecap="round"
                            d="M9 9V6.75a3 3 0 0 1 6 0V9" />
                    </svg>
                </span>
            </div>
        </div>
    </article>
</a>
