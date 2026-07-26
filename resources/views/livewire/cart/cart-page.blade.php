<div class="public-container">
    <div
        class="flex flex-col gap-5 border-b border-brand-palm/10
            pb-8 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="public-eyebrow">Your order</p>

            <h1
                class="mt-4 font-display text-5xl text-brand-forest
                    sm:text-6xl">
                Shopping Cart
            </h1>
        </div>

        @if ($cart['items'] !== [])
        <button
            type="button"
            wire:click="clearCart"
            wire:confirm="Remove every item from your cart?"
            class="text-sm font-semibold text-brand-coral-dark
                    underline decoration-brand-coral/40
                    underline-offset-4">
            Clear cart
        </button>
        @endif
    </div>

    @if ($warning)
    <x-public.alert type="warning" class="mt-8">
        {{ $warning }}
    </x-public.alert>
    @endif

    @error('cart')
    <x-public.alert type="error" class="mt-8">
        {{ $message }}
    </x-public.alert>
    @enderror

    @if ($cart['items'] === [])
    <div
        class="mt-12 rounded-island border border-brand-palm/10
                bg-white px-6 py-16 text-center shadow-island">
        <h2 class="font-display text-3xl text-brand-forest">
            Your cart is empty
        </h2>

        <p
            class="mx-auto mt-4 max-w-xl text-base leading-8
                    text-brand-muted">
            Explore the menu and add your favorite Caribbean
            dishes when you are ready.
        </p>

        <a
            href="{{ route('menu') }}"
            class="public-button-primary mt-8">
            Browse the Menu
        </a>
    </div>
    @else
    <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_22rem]">
        <div class="space-y-6">
            @foreach ($cart['items'] as $item)
            @php
            $quantityErrorKey =
            'quantities.'.$item['key'];
            @endphp

            <article
                wire:key="cart-line-{{ $item['key'] }}"
                class="rounded-island border
                            border-brand-palm/10 bg-white p-6
                            shadow-island">
                <div
                    class="flex flex-col gap-6 sm:flex-row
                                sm:items-start sm:justify-between">
                    <div>
                        <a
                            href="{{ route(
                                        'menu-items.show',
                                        $item['slug'],
                                    ) }}"
                            class="font-display text-3xl
                                        text-brand-forest transition
                                        hover:text-brand-coral-dark">
                            {{ $item['name'] }}
                        </a>

                        @if ($item['options'] !== [])
                        <dl class="mt-4 space-y-2">
                            @foreach (
                            $item['options']
                            as $option
                            )
                            <div
                                class="flex flex-wrap
                                                    gap-x-2 text-sm
                                                    leading-6">
                                <dt
                                    class="font-semibold
                                                        text-brand-forest">
                                    {{ $option[
                                                        'group_name'
                                                    ] }}:
                                </dt>

                                <dd
                                    class="text-brand-muted">
                                    {{ $option['name'] }}

                                    @if (
                                    $option[
                                    'formatted_additional_price'
                                    ]
                                    )
                                    <span>
                                        (+
                                        {{ $option[
                                                                'formatted_additional_price'
                                                            ] }})
                                    </span>
                                    @endif
                                </dd>
                            </div>
                            @endforeach
                        </dl>
                        @endif

                        <p
                            class="mt-5 text-sm font-semibold
                                        text-brand-palm">
                            {{ $item['formatted_unit_price'] }}
                            each
                        </p>
                    </div>

                    <p
                        class="text-xl font-semibold
                                    text-brand-forest">
                        {{ $item['formatted_line_total'] }}
                    </p>
                </div>

                <div
                    class="mt-7 flex flex-col gap-4 border-t
                                border-brand-palm/10 pt-6 sm:flex-row
                                sm:items-end sm:justify-between">
                    <div class="w-full sm:max-w-48">
                        <label
                            for="quantity-{{ $item['key'] }}"
                            class="text-sm font-semibold
                                        text-brand-forest">
                            Quantity
                        </label>

                        <div class="mt-2 flex gap-2">
                            <input
                                id="quantity-{{ $item['key'] }}"
                                type="number"
                                min="1"
                                max="{{ \App\Support\Cart\SessionCart::MAX_QUANTITY }}"
                                step="1"
                                wire:model.number="quantities.{{ $item['key'] }}"
                                class="min-h-11 min-w-0 flex-1
                                            rounded-full border
                                            border-brand-palm/20
                                            bg-brand-cream px-4">

                            <button
                                type="button"
                                wire:click="updateQuantity('{{ $item['key'] }}')"
                                wire:loading.attr="disabled"
                                class="rounded-full
                                            border border-brand-palm/20
                                            px-4 text-sm font-semibold
                                            text-brand-palm transition
                                            hover:border-brand-palm
                                            disabled:opacity-60">
                                Update
                            </button>
                        </div>

                        @error($quantityErrorKey)
                        <p
                            class="mt-2 text-sm font-medium
                                            text-brand-coral-dark"
                            role="alert">
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <button
                        type="button"
                        wire:click="removeItem('{{ $item['key'] }}')"
                        wire:confirm="Remove this item from your cart?"
                        class="text-sm font-semibold
                                    text-brand-coral-dark underline
                                    decoration-brand-coral/40
                                    underline-offset-4">
                        Remove
                    </button>
                </div>
            </article>
            @endforeach
        </div>

        <aside
            class="h-fit rounded-island border
                    border-brand-palm/10 bg-brand-sand-soft p-7
                    shadow-island lg:sticky lg:top-28"
            aria-labelledby="cart-summary-title">
            <h2
                id="cart-summary-title"
                class="font-display text-3xl text-brand-forest">
                Order Summary
            </h2>

            <div
                class="mt-7 flex items-center justify-between
                        border-b border-brand-palm/10 pb-5">
                <span class="text-brand-muted">
                    Items
                </span>

                <span class="font-semibold text-brand-forest">
                    {{ $cart['item_count'] }}
                </span>
            </div>

            <div
                class="flex items-center justify-between
                        border-b border-brand-palm/10 py-5">
                <span class="font-semibold text-brand-forest">
                    Subtotal
                </span>

                <span
                    class="text-xl font-semibold
                            text-brand-forest">
                    {{ $cart['formatted_subtotal'] }}
                </span>
            </div>

            <p
                class="mt-5 text-sm leading-6
                        text-brand-muted">
                Coupons, tax, pickup, and delivery totals will be
                calculated during checkout.
            </p>

            @if (
            \Illuminate\Support\Facades\Route::has(
            'checkout.index',
            )
            )
            <a
                href="{{ route('checkout.index') }}"
                class="public-button-primary mt-7 w-full">
                Proceed to Checkout
            </a>
            @else
            <button
                type="button"
                disabled
                class="public-button-primary mt-7 w-full
                            cursor-not-allowed opacity-60">
                Checkout Unavailable
            </button>
            @endif

            <a
                href="{{ route('menu') }}"
                class="public-button-secondary mt-3 w-full
                        text-brand-palm">
                Continue Browsing
            </a>
        </aside>
    </div>
    @endif
</div>