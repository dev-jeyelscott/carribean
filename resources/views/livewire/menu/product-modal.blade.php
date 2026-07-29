<div
    data-product-modal-root
    wire:key="product-modal-root">
    <dialog
        wire:ignore.self
        data-product-modal
        class="menu-product-dialog"
        aria-labelledby="product-modal-title"
        aria-describedby="product-modal-description">
        @if ($item)
            @php
                /*
                 * Resolve modal presentation state independently from the cart.
                 */
                $formattedPrice = $item->formattedPrice();
                $imageAlt = $item->image_alt_text ?: $item->name;

                $canOrder = $item->is_available
                    && $item->is_purchasable
                    && $item->price_cents !== null;
            @endphp

            <div
                data-product-modal-panel
                class="menu-product-modal-panel">
                <form
                    wire:submit="addToCart"
                    class="grid min-h-0 lg:grid-cols-[0.92fr_1.08fr]">
                    <div
                        data-product-modal-image
                        class="relative min-h-64 overflow-hidden
                            bg-primary-deep sm:min-h-80 lg:min-h-full">
                        @if ($item->image_url)
                            <x-public.responsive-image
                                :image="$item"
                                :alt="$imageAlt"
                                variant="hero"
                                sizes="(min-width: 1024px) 45vw, 100vw"
                                width="1100"
                                height="1200"
                                loading="eager"
                                fetchpriority="high"
                                img-class="absolute inset-0 size-full
                                    object-cover" />
                        @else
                            <div
                                class="absolute inset-0
                                    bg-[radial-gradient(circle_at_28%_20%,rgb(242_199_107_/_30%),transparent_34%),linear-gradient(145deg,#206f7c,#0c342b)]"
                                aria-hidden="true">
                            </div>

                            <p
                                class="absolute inset-x-8 bottom-8 border-t
                                    border-white/20 pt-5 text-xs font-semibold
                                    uppercase tracking-[0.2em] text-white/70">
                                Image coming soon
                            </p>
                        @endif

                        <div
                            class="absolute inset-0 bg-gradient-to-t
                                from-primary-deep/50 via-transparent
                                to-transparent"
                            aria-hidden="true">
                        </div>

                        <div
                            class="absolute inset-x-6 bottom-6 flex
                                flex-wrap gap-2">
                            @unless ($item->is_available)
                                <span
                                    class="rounded-full bg-canvas/95 px-4 py-2
                                        text-[0.65rem] font-semibold uppercase
                                        tracking-[0.12em] text-ink shadow-card">
                                    Currently unavailable
                                </span>
                            @endunless

                            @if (
                                $item->is_available
                                && ! $item->is_purchasable
                            )
                                <span
                                    class="rounded-full bg-canvas/95 px-4 py-2
                                        text-[0.65rem] font-semibold uppercase
                                        tracking-[0.12em] text-ink shadow-card">
                                    Dine-in menu only
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="menu-product-modal-scroll">
                        <header
                            data-product-modal-copy
                            class="border-b border-line px-6 pb-7 pt-6
                                sm:px-8 sm:pt-8">
                            <div
                                class="flex items-start
                                    justify-between gap-5">
                                <div class="min-w-0">
                                    <p class="public-eyebrow">
                                        {{ $item->menuCategory->name }}
                                    </p>

                                    <h2
                                        id="product-modal-title"
                                        class="mt-3 font-display text-4xl
                                            leading-tight text-primary-deep
                                            sm:text-5xl">
                                        {{ $item->name }}
                                    </h2>
                                </div>

                                <button
                                    type="button"
                                    wire:click="close"
                                    wire:loading.attr="disabled"
                                    data-product-modal-close-action
                                    autofocus
                                    class="inline-flex size-11 shrink-0
                                        items-center justify-center
                                        rounded-full border border-primary/15
                                        bg-surface text-primary shadow-card
                                        transition hover:bg-surface-soft
                                        disabled:cursor-wait
                                        disabled:opacity-50"
                                    aria-label="Close product details">
                                    <svg
                                        class="size-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true">
                                        <path
                                            stroke-linecap="round"
                                            d="m6 6 12 12M18 6 6 18" />
                                    </svg>
                                </button>
                            </div>

                            <div
                                class="mt-5 flex flex-wrap items-center
                                    gap-3">
                                <p
                                    class="text-xl font-semibold
                                        tabular-nums text-primary">
                                    {{ $formattedPrice ?? 'Price pending' }}
                                </p>

                                @if ($item->dietary_labels)
                                    @foreach ($item->dietary_labels as $label)
                                        <span
                                            class="rounded-full
                                                bg-surface-soft px-3 py-1.5
                                                text-[0.62rem] font-semibold
                                                uppercase tracking-[0.1em]
                                                text-primary">
                                            {{ $label }}
                                        </span>
                                    @endforeach
                                @endif
                            </div>

                            <p
                                id="product-modal-description"
                                class="mt-5 text-sm leading-7 text-muted
                                    sm:text-base">
                                {{ $item->description
                                    ?: 'A thoughtfully prepared Caribbean-inspired Coast & Cay favorite.' }}
                            </p>
                        </header>

                        <div
                            data-product-modal-options
                            class="space-y-7 px-6 py-7 sm:px-8">
                            @error('cart')
                                <x-public.alert type="error">
                                    {{ $message }}
                                </x-public.alert>
                            @enderror

                            @error('selections')
                                <x-public.alert type="error">
                                    {{ $message }}
                                </x-public.alert>
                            @enderror

                            @if ($item->optionGroups->isNotEmpty())
                                @foreach ($item->optionGroups as $group)
                                    <x-public.menu-option-group
                                        :group="$group" />
                                @endforeach
                            @else
                                <div
                                    class="rounded-card border
                                        border-primary/10 bg-surface-soft
                                        px-5 py-4">
                                    <p
                                        class="text-sm leading-7 text-muted">
                                        This item does not require additional
                                        selections.
                                    </p>
                                </div>
                            @endif

                            @if ($item->allergen_information)
                                <section
                                    class="rounded-card border
                                        border-coral/15 bg-coral/5 p-5"
                                    aria-labelledby="modal-allergen-heading">
                                    <h3
                                        id="modal-allergen-heading"
                                        class="text-xs font-semibold uppercase
                                            tracking-[0.16em]
                                            text-coral-deep">
                                        Allergen information
                                    </h3>

                                    <p
                                        class="mt-3 text-sm leading-7
                                            text-muted">
                                        {{ $item->allergen_information }}
                                    </p>
                                </section>
                            @endif

                            <div
                                wire:offline
                                class="rounded-card border border-coral/20
                                    bg-surface px-5 py-4 text-sm text-ink"
                                role="status">
                                Reconnect before adding this item to the cart.
                            </div>
                        </div>

                        <footer
                            data-product-modal-footer
                            class="menu-product-modal-footer">
                            <div
                                class="flex flex-col gap-5
                                    sm:flex-row sm:items-center">
                                <x-public.quantity-control
                                    :quantity="$quantity"
                                    decrement-action="decrementQuantity"
                                    increment-action="incrementQuantity"
                                    :maximum="\App\Support\Cart\SessionCart::MAX_QUANTITY"
                                    label="Product quantity" />

                                <div
                                    class="flex min-w-0 flex-1
                                        items-center justify-between gap-5">
                                    <div>
                                        <p
                                            class="text-[0.62rem]
                                                font-semibold uppercase
                                                tracking-[0.14em]
                                                text-muted">
                                            Current total
                                        </p>

                                        <p
                                            data-product-modal-total
                                            class="mt-1 text-xl font-semibold
                                                tabular-nums text-primary">
                                            {{ $displayTotal
                                                ?? 'Price pending' }}
                                        </p>
                                    </div>

                                    <button
                                        type="submit"
                                        wire:loading.attr="disabled"
                                        wire:target="addToCart"
                                        @disabled(! $canOrder)
                                        data-product-add
                                        class="inline-flex min-h-12
                                            min-w-40 items-center
                                            justify-center rounded-xl
                                            bg-coral px-6 text-xs
                                            font-semibold uppercase
                                            tracking-[0.13em] text-white
                                            shadow-card transition
                                            duration-300
                                            hover:-translate-y-0.5
                                            hover:bg-coral-deep
                                            hover:shadow-panel
                                            disabled:cursor-not-allowed
                                            disabled:opacity-50
                                            motion-reduce:transform-none">
                                        <span
                                            wire:loading.remove
                                            wire:target="addToCart">
                                            {{ $canOrder
                                                ? 'Add to Cart'
                                                : 'Unavailable' }}
                                        </span>

                                        <span
                                            wire:loading
                                            wire:target="addToCart"
                                            class="inline-flex items-center
                                                gap-2">
                                            <svg
                                                class="size-4 animate-spin
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

                                            Adding
                                        </span>
                                    </button>
                                </div>
                            </div>

                            @error('quantity')
                                <p
                                    class="mt-3 text-sm font-medium
                                        text-coral-deep"
                                    role="alert">
                                    {{ $message }}
                                </p>
                            @enderror
                        </footer>
                    </div>
                </form>
            </div>
        @endif
    </dialog>
</div>
