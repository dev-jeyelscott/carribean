@php
$checkoutDisabledReason = null;

if (! $cart['accepting_online_orders']) {
$checkoutDisabledReason =
$cart['online_orders_closed_message'];
} elseif ($cart['invalid_line_keys'] !== []) {
$checkoutDisabledReason =
'One or more cart items need to be reviewed.';
} elseif ($cart['coupon_error'] !== null) {
$checkoutDisabledReason = $cart['coupon_error'];
} elseif ($cart['fulfillment_error'] !== null) {
$checkoutDisabledReason = $cart['fulfillment_error'];
} elseif (! $cart['is_checkout_ready']) {
$checkoutDisabledReason =
'Complete the required cart details before checkout.';
}
@endphp

<div class="public-container" data-cart-page>
    <p
        class="sr-only"
        role="status"
        aria-live="polite"
        aria-atomic="true">
        {{ $statusMessage }}
    </p>

    <div
        wire:offline
        class="mb-6"
        role="alert"
        aria-live="assertive">
        <x-public.alert type="error" surface="light" title="Connection interrupted">
            Cart changes cannot be saved while you are offline. Your current
            selections remain visible; reconnect and try again.
        </x-public.alert>
    </div>

    <header
        class="border-b border-line pb-7 sm:flex sm:items-end
            sm:justify-between sm:gap-8 sm:pb-9">
        <div class="max-w-3xl">
            <p class="public-eyebrow">Your order</p>

            <h1
                class="mt-3 font-display text-4xl leading-[0.95]
                    text-ink sm:text-5xl lg:text-6xl">
                Review Your Cart
            </h1>

            <p class="mt-4 max-w-2xl text-sm leading-7 text-muted sm:text-base">
                Review your selections, adjust quantities, and choose pickup
                or local delivery before continuing.
            </p>

            @if ($cart['items'] !== [])
            <p class="mt-3 text-sm font-semibold text-primary">
                {{ $cart['item_count'] }}
                {{ $cart['item_count'] === 1 ? 'item' : 'items' }}
                in your order
            </p>
            @endif
        </div>

        @if ($cart['items'] !== [])
        <button
            type="button"
            wire:click="clearCart"
            wire:confirm="Remove every item and cart selection?"
            wire:loading.attr="disabled"
            class="mt-5 inline-flex min-h-11 items-center gap-2
                    rounded-xl px-3 text-sm font-semibold text-coral-deep
                    transition hover:bg-coral/10
                    disabled:cursor-not-allowed disabled:opacity-50
                    sm:mt-0">
            <svg
                class="size-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.75"
                aria-hidden="true">
                <path
                    stroke-linecap="round"
                    d="M4 7h16M9 3h6l1 4H8l1-4Z" />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m7 7 1 14h8l1-14M10 11v6M14 11v6" />
            </svg>

            Clear cart
        </button>
        @endif
    </header>

    @if ($warning)
    <x-public.alert
        type="warning"
        surface="light"
        title="Your cart was refreshed"
        class="mt-6">
        {{ $warning }}
    </x-public.alert>
    @endif

    @error('cart')
    <x-public.alert
        type="error"
        surface="light"
        title="Cart update failed"
        class="mt-6">
        {{ $message }}
    </x-public.alert>
    @enderror

    @if ($cart['items'] === [])
    <section
        class="relative mt-10 overflow-hidden rounded-panel border
                border-line bg-surface px-6 py-14 text-center shadow-card
                sm:px-10 sm:py-18"
        aria-labelledby="empty-cart-title">
        <div
            class="pointer-events-none absolute inset-0
                    bg-[radial-gradient(circle_at_20%_20%,rgb(32_111_124_/_10%),transparent_28%),radial-gradient(circle_at_85%_80%,rgb(230_110_80_/_10%),transparent_30%)]"
            aria-hidden="true"></div>

        <div class="relative mx-auto max-w-xl">
            <span
                class="mx-auto flex size-14 items-center justify-center
                        rounded-full border border-primary/15 bg-surface-soft
                        text-primary shadow-card">
                <svg
                    class="size-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5.5 8.5h13l-1 11h-11l-1-11Z" />
                    <path
                        stroke-linecap="round"
                        d="M9 9V6.75a3 3 0 0 1 6 0V9" />
                </svg>
            </span>

            <h2
                id="empty-cart-title"
                class="mt-6 font-display text-3xl text-ink sm:text-4xl">
                Your cart is empty
            </h2>

            <p class="mt-3 text-sm leading-7 text-muted sm:text-base">
                Explore the menu and add a few Coast & Cay favorites when
                you are ready.
            </p>

            <div
                class="mt-7 flex flex-col justify-center gap-3
                        sm:flex-row">
                <a href="{{ route('menu') }}" class="public-button-primary">
                    Explore the Menu
                </a>

                <a
                    href="{{ route('home') }}"
                    class="public-button-secondary text-primary">
                    Return Home
                </a>
            </div>
        </div>
    </section>
    @else
    <div
        class="mt-8 grid items-start gap-8
                xl:grid-cols-[minmax(0,1fr)_23rem] xl:gap-10">
        <section aria-labelledby="cart-items-title">
            <div class="flex items-center justify-between gap-4">
                <h2
                    id="cart-items-title"
                    class="font-display text-2xl text-ink sm:text-3xl">
                    Selected dishes
                </h2>

                <a
                    href="{{ route('menu') }}"
                    class="text-sm font-semibold text-primary underline
                            decoration-coral/45 underline-offset-4
                            transition hover:text-coral-deep">
                    Add more items
                </a>
            </div>

            <div class="mt-5 space-y-4">
                @foreach ($cart['items'] as $item)
                @php
                $quantityErrorKey =
                'quantities.'.$item['key'];
                $quantityErrorId =
                'quantity-error-'.$item['key'];
                $itemUrl = route(
                'menu-items.show',
                $item['slug'],
                );
                @endphp

                <article
                    wire:key="cart-line-{{ $item['key'] }}"
                    data-cart-line
                    class="group rounded-panel border border-line
                                bg-surface p-4 shadow-card transition
                                duration-300 hover:border-primary/20
                                hover:shadow-panel motion-reduce:transition-none
                                sm:p-5">
                    <div
                        class="grid grid-cols-[5.5rem_minmax(0,1fr)]
                                    gap-4 sm:grid-cols-[8rem_minmax(0,1fr)]
                                    sm:gap-5">
                        <a
                            href="{{ $itemUrl }}"
                            class="relative aspect-square overflow-hidden
                                        rounded-card bg-surface-soft
                                        focus-visible:outline-none
                                        focus-visible:ring-2
                                        focus-visible:ring-ocean">
                            @if ($item['image_url'])
                            <img
                                src="{{ $item['image_url'] }}"
                                @if ($item['image_srcset'])
                                srcset="{{ $item['image_srcset'] }}"
                                @endif
                                sizes="(min-width: 640px) 128px, 88px"
                                alt="{{ $item['image_alt_text'] }}"
                                width="320"
                                height="320"
                                loading="lazy"
                                decoding="async"
                                class="h-full w-full object-cover
                                                transition duration-300
                                                group-hover:scale-[1.02]
                                                motion-reduce:transform-none
                                                motion-reduce:transition-none">
                            @else
                            <span
                                class="absolute inset-0 flex
                                                items-center justify-center
                                                bg-[radial-gradient(circle_at_30%_20%,rgb(242_199_107_/_22%),transparent_34%),linear-gradient(145deg,#206f7c,#0c342b)]
                                                text-canvas"
                                aria-label="Image unavailable">
                                <svg
                                    class="size-8"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        d="M12 21V9M12 9C10 5 6 4 3 6c4 0 6 2 8 5M12 9c2-4 6-5 9-3-4 0-7 2-8 5" />
                                </svg>
                            </span>
                            @endif
                        </a>

                        <div class="min-w-0">
                            <div
                                class="flex items-start justify-between
                                            gap-3">
                                <div class="min-w-0">
                                    <a
                                        href="{{ $itemUrl }}"
                                        title="{{ $item['name'] }}"
                                        class="line-clamp-2 font-display
                                                    text-xl leading-tight text-ink
                                                    transition hover:text-coral-deep
                                                    sm:text-2xl">
                                        {{ $item['name'] }}
                                    </a>

                                    <p
                                        class="mt-1 text-xs font-semibold
                                                    uppercase tracking-[0.12em]
                                                    text-primary">
                                        {{ $item['formatted_unit_price'] }}
                                        each
                                    </p>
                                </div>

                                <p
                                    class="shrink-0 text-base
                                                font-semibold text-ink sm:text-lg">
                                    {{ $item['formatted_line_total'] }}
                                </p>
                            </div>

                            @if ($item['options'] !== [])
                            <dl class="mt-3 space-y-1.5">
                                @foreach ($item['options'] as $option)
                                <div
                                    class="flex flex-wrap gap-x-1.5
                                                        text-xs leading-5
                                                        sm:text-sm sm:leading-6">
                                    <dt
                                        class="font-semibold
                                                            text-ink">
                                        {{ $option['group_name'] }}:
                                    </dt>

                                    <dd class="min-w-0 text-muted">
                                        {{ $option['name'] }}

                                        @if ($option['formatted_additional_price'])
                                        <span
                                            class="whitespace-nowrap
                                                                    font-medium
                                                                    text-primary">
                                            +{{ $option['formatted_additional_price'] }}
                                        </span>
                                        @endif
                                    </dd>
                                </div>
                                @endforeach
                            </dl>
                            @endif

                            <div
                                class="mt-4 flex flex-wrap items-center
                                            justify-between gap-3 border-t
                                            border-line pt-4">
                                <div>
                                    <span
                                        class="sr-only"
                                        id="quantity-label-{{ $item['key'] }}">
                                        Quantity for {{ $item['name'] }}
                                    </span>

                                    <div
                                        class="inline-flex min-h-11
                                                    items-center rounded-full
                                                    border border-primary/15
                                                    bg-canvas p-1 shadow-sm"
                                        role="group"
                                        aria-labelledby="quantity-label-{{ $item['key'] }}">
                                        <button
                                            type="button"
                                            wire:click="decrementQuantity('{{ $item['key'] }}')"
                                            wire:loading.attr="disabled"
                                            @disabled($item['quantity'] <=1)
                                            class="inline-flex size-9
                                                        items-center justify-center
                                                        rounded-full text-primary
                                                        transition hover:bg-surface-warm
                                                        disabled:cursor-not-allowed
                                                        disabled:opacity-35">
                                            <span class="sr-only">
                                                Decrease quantity for
                                                {{ $item['name'] }}
                                            </span>

                                            <svg
                                                class="size-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                aria-hidden="true">
                                                <path
                                                    stroke-linecap="round"
                                                    d="M6 12h12" />
                                            </svg>
                                        </button>

                                        <span
                                            data-cart-quantity
                                            class="min-w-9 px-1 text-center
                                                        text-sm font-semibold
                                                        tabular-nums text-ink"
                                            aria-live="polite">
                                            {{ $quantities[$item['key']] ?? $item['quantity'] }}
                                        </span>

                                        <button
                                            type="button"
                                            wire:click="incrementQuantity('{{ $item['key'] }}')"
                                            wire:loading.attr="disabled"
                                            @disabled($item['quantity']>= \App\Support\Cart\SessionCart::MAX_QUANTITY)
                                            class="inline-flex size-9
                                            items-center justify-center
                                            rounded-full text-primary
                                            transition hover:bg-surface-warm
                                            disabled:cursor-not-allowed
                                            disabled:opacity-35">
                                            <span class="sr-only">
                                                Increase quantity for
                                                {{ $item['name'] }}
                                            </span>

                                            <svg
                                                class="size-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                aria-hidden="true">
                                                <path
                                                    stroke-linecap="round"
                                                    d="M12 6v12M6 12h12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    wire:click="removeItem('{{ $item['key'] }}')"
                                    wire:confirm="Remove {{ $item['name'] }} from your cart?"
                                    wire:loading.attr="disabled"
                                    class="inline-flex size-11 items-center
                                                justify-center rounded-full
                                                text-coral-deep transition
                                                hover:bg-coral/10
                                                disabled:cursor-not-allowed
                                                disabled:opacity-50">
                                    <span class="sr-only">
                                        Remove {{ $item['name'] }}
                                    </span>

                                    <svg
                                        class="size-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.75"
                                        aria-hidden="true">
                                        <path
                                            stroke-linecap="round"
                                            d="M4 7h16M9 3h6l1 4H8l1-4Z" />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m7 7 1 14h8l1-14M10 11v6M14 11v6" />
                                    </svg>
                                </button>
                            </div>

                            @error($quantityErrorKey)
                            <p
                                id="{{ $quantityErrorId }}"
                                class="mt-2 text-sm font-medium
                                                text-coral-deep"
                                role="alert">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

            <a
                href="{{ route('menu') }}"
                class="mt-6 inline-flex min-h-11 items-center gap-2
                        rounded-xl text-sm font-semibold text-primary
                        transition hover:text-coral-deep">
                <span aria-hidden="true">&larr;</span>
                Continue browsing
            </a>
        </section>

        <aside
            data-cart-summary
            class="rounded-panel border border-line bg-surface-soft
                    p-5 shadow-panel sm:p-6 xl:sticky xl:top-28"
            aria-labelledby="cart-summary-title">
            <h2
                id="cart-summary-title"
                class="font-display text-3xl text-ink">
                Order Summary
            </h2>

            @unless ($cart['accepting_online_orders'])
            <x-public.alert
                type="warning"
                surface="light"
                title="Online ordering is paused"
                class="mt-5">
                {{ $cart['online_orders_closed_message'] }}
            </x-public.alert>
            @endunless

            <fieldset class="mt-6 border-b border-line pb-6">
                <legend class="text-sm font-semibold text-ink">
                    Fulfillment
                </legend>

                <div class="mt-3 grid gap-2">
                    @foreach ($fulfillmentOptions as $value => $label)
                    @php
                    $isPickup = $value ===
                    \App\Enums\FulfillmentMethod::Pickup->value;
                    $isSelected =
                    $fulfillmentMethod === $value;
                    $inputId =
                    'fulfillment-method-'.$value;
                    @endphp

                    <label
                        for="{{ $inputId }}"
                        @class([ 'flex min-h-16 cursor-pointer items-center
                    justify-between gap-3 rounded-card border
                    px-4 py-3 text-left transition' , 'border-primary bg-primary text-canvas'=>
                        $isSelected,
                        'border-primary/15 bg-surface text-ink
                        hover:border-primary/30' =>
                        ! $isSelected,
                        ])>
                        <span>
                            <span class="block text-sm font-semibold">
                                {{ $label }}
                            </span>

                            <span
                                class="mt-0.5 block text-xs
                            leading-5 opacity-75">
                                {{ $isPickup
                            ? 'Collect your order from Coast & Cay.'
                            : 'Available within approved local ZIP codes.' }}
                            </span>
                        </span>

                        <input
                            id="{{ $inputId }}"
                            type="radio"
                            name="fulfillment-method"
                            value="{{ $value }}"
                            wire:click="selectFulfillment('{{ $value }}')"
                            wire:loading.attr="disabled"
                            wire:target="selectFulfillment"
                            @checked($isSelected)
                            class="size-5 shrink-0 cursor-pointer
                        accent-coral disabled:cursor-wait
                        disabled:opacity-50">
                    </label>
                    @endforeach
                </div>

                @error('fulfillmentMethod')
                <p
                    class="mt-2 text-sm font-medium text-coral-deep"
                    role="alert">
                    {{ $message }}
                </p>
                @enderror
            </fieldset>

            @if (
            $fulfillmentMethod
            === \App\Enums\FulfillmentMethod::Delivery->value
            )
            <form
                wire:submit="saveFulfillment"
                class="border-b border-line py-6">
                <label
                    for="delivery-zip"
                    class="text-sm font-semibold text-ink">
                    Delivery ZIP code
                </label>

                <p
                    id="delivery-zip-help"
                    class="mt-1 text-xs leading-5 text-muted">
                    Delivery is limited to approved five-digit local ZIP
                    codes.
                </p>

                <div class="mt-3 flex gap-2">
                    <input
                        id="delivery-zip"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]{5}"
                        maxlength="5"
                        autocomplete="postal-code"
                        wire:model="deliveryZip"
                        aria-describedby="delivery-zip-help delivery-zip-feedback"
                        aria-invalid="{{ $errors->has('deliveryZip') ? 'true' : 'false' }}"
                        class="min-h-11 min-w-0 flex-1 rounded-xl
                                    border border-primary/20 bg-surface px-4
                                    text-ink placeholder:text-muted/70"
                        placeholder="90210">

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex min-h-11 items-center
                                    justify-center rounded-xl border
                                    border-primary/20 px-4 text-sm
                                    font-semibold text-primary transition
                                    hover:border-primary hover:bg-primary
                                    hover:text-canvas
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveFulfillment">
                            Check ZIP
                        </span>

                        <span wire:loading wire:target="saveFulfillment">
                            Checking…
                        </span>
                    </button>
                </div>

                <div id="delivery-zip-feedback">
                    @error('deliveryZip')
                    <p
                        class="mt-2 text-sm font-medium
                                        text-coral-deep"
                        role="alert">
                        {{ $message }}
                    </p>
                    @enderror

                    @if (
                    ! $errors->has('deliveryZip')
                    && $cart['delivery_zip']
                    && $cart['fulfillment_error'] === null
                    )
                    <p
                        class="mt-2 text-sm font-medium
                                        text-primary"
                        role="status">
                        Delivery is available for
                        {{ $cart['delivery_zip'] }}.
                    </p>
                    @endif
                </div>
            </form>
            @endif

            @if (
            $cart['fulfillment_error']
            && (
            $deliveryZipAttempted
            || $cart['delivery_zip']
            )
            && ! $errors->has('deliveryZip')
            )
            <x-public.alert
                type="warning"
                surface="light"
                title="Fulfillment needs attention"
                class="mt-5">
                {{ $cart['fulfillment_error'] }}
            </x-public.alert>
            @endif

            <form
                wire:submit="applyCoupon"
                class="border-b border-line py-6">
                <label
                    for="coupon-code"
                    class="text-sm font-semibold text-ink">
                    Coupon
                </label>

                @if ($cart['coupon'])
                <div
                    class="mt-3 flex items-center justify-between gap-4
                                rounded-card border border-primary/10
                                bg-surface px-4 py-3">
                    <div class="min-w-0">
                        <p
                            class="truncate font-semibold uppercase
                                        tracking-[0.08em] text-ink">
                            {{ $cart['coupon']['code'] }}
                        </p>

                        <p class="mt-1 text-xs text-muted">
                            {{ $cart['coupon']['formatted_value'] }}
                            · -{{ $cart['formatted_discount'] }}
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="removeCoupon"
                        wire:loading.attr="disabled"
                        class="min-h-11 shrink-0 rounded-xl px-3
                                    text-sm font-semibold text-coral-deep
                                    transition hover:bg-coral/10
                                    disabled:opacity-50">
                        Remove
                    </button>
                </div>
                @else
                <div class="mt-3 flex gap-2">
                    <input
                        id="coupon-code"
                        type="text"
                        wire:model="couponCode"
                        maxlength="64"
                        autocomplete="off"
                        aria-invalid="{{ $errors->has('couponCode') ? 'true' : 'false' }}"
                        class="min-h-11 min-w-0 flex-1 rounded-xl
                                    border border-primary/20 bg-surface px-4
                                    uppercase text-ink placeholder:text-muted/70"
                        placeholder="ISLAND10">

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex min-h-11 items-center
                                    justify-center rounded-xl border
                                    border-primary/20 px-4 text-sm
                                    font-semibold text-primary transition
                                    hover:border-primary hover:bg-primary
                                    hover:text-canvas
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50">
                        <span wire:loading.remove wire:target="applyCoupon">
                            Apply
                        </span>

                        <span wire:loading wire:target="applyCoupon">
                            Applying…
                        </span>
                    </button>
                </div>

                @error('couponCode')
                <p
                    class="mt-2 text-sm font-medium text-coral-deep"
                    role="alert">
                    {{ $message }}
                </p>
                @enderror
                @endif
            </form>

            <dl class="mt-6 space-y-3 text-sm">
                <div
                    data-summary-row="items"
                    class="flex items-center justify-between gap-4">
                    <dt class="text-muted">Items</dt>
                    <dd class="font-semibold tabular-nums text-ink">
                        {{ $cart['item_count'] }}
                    </dd>
                </div>

                <div
                    data-summary-row="subtotal"
                    class="flex items-center justify-between gap-4">
                    <dt class="text-muted">Subtotal</dt>
                    <dd class="font-semibold tabular-nums text-ink">
                        {{ $cart['formatted_subtotal'] }}
                    </dd>
                </div>

                @if ($cart['discount_cents'] > 0)
                <div
                    data-summary-row="discount"
                    class="flex items-center justify-between gap-4">
                    <dt class="text-muted">Discount</dt>
                    <dd class="font-semibold tabular-nums text-primary">
                        -{{ $cart['formatted_discount'] }}
                    </dd>
                </div>
                @endif

                <div
                    data-summary-row="tax"
                    class="flex items-center justify-between gap-4">
                    <dt class="text-muted">
                        Tax
                        <span class="text-xs">
                            ({{ $cart['formatted_tax_rate'] }})
                        </span>
                    </dt>
                    <dd class="font-semibold tabular-nums text-ink">
                        {{ $cart['formatted_tax'] }}
                    </dd>
                </div>

                <div
                    data-summary-row="delivery"
                    class="flex items-start justify-between gap-4">
                    <dt class="text-muted">
                        Delivery
                        @if (
                        $fulfillmentMethod
                        === \App\Enums\FulfillmentMethod::Pickup->value
                        )
                        <span class="block text-xs">
                            Pickup selected
                        </span>
                        @endif
                    </dt>
                    <dd class="font-semibold tabular-nums text-ink">
                        {{ $cart['formatted_delivery_fee'] }}
                    </dd>
                </div>

                <div
                    data-summary-row="total"
                    class="mt-5 flex items-center justify-between gap-4
                            border-t border-primary/15 pt-5">
                    <dt class="font-display text-2xl text-ink">
                        Total
                    </dt>
                    <dd
                        class="text-2xl font-semibold tabular-nums
                                text-ink">
                        {{ $cart['formatted_grand_total'] }}
                    </dd>
                </div>
            </dl>

            @if (
            \Illuminate\Support\Facades\Route::has('checkout.index')
            && $cart['is_checkout_ready']
            )
            <a
                href="{{ route('checkout.index') }}"
                data-cart-checkout
                class="mt-7 inline-flex min-h-13 w-full items-center
                            justify-center rounded-xl bg-primary px-6 py-3
                            text-center text-xs font-semibold uppercase
                            tracking-[0.14em] text-canvas shadow-card
                            transition duration-300 hover:-translate-y-0.5
                            hover:bg-primary-deep hover:shadow-panel
                            motion-reduce:transform-none
                            motion-reduce:transition-none">
                Continue to Checkout
            </a>
            @else
            <button
                type="button"
                disabled
                aria-describedby="checkout-disabled-reason"
                class="mt-7 inline-flex min-h-13 w-full
                            cursor-not-allowed items-center justify-center
                            rounded-xl bg-primary px-6 py-3 text-center
                            text-xs font-semibold uppercase
                            tracking-[0.14em] text-canvas opacity-45">
                Continue to Checkout
            </button>

            <p
                id="checkout-disabled-reason"
                class="mt-2 text-xs leading-5 text-muted">
                {{ $checkoutDisabledReason }}
            </p>
            @endif

            <p class="mt-4 text-center text-xs leading-5 text-muted">
                Prices, availability, and fulfillment are confirmed by the
                server before checkout.
            </p>

            <a
                href="{{ route('menu') }}"
                class="mt-4 block min-h-11 rounded-xl py-3 text-center
                        text-sm font-semibold text-primary transition
                        hover:bg-surface hover:text-coral-deep">
                Continue Browsing
            </a>
        </aside>
    </div>
    @endif
</div>