<div class="public-container">
    <div
        class="flex flex-col gap-5 border-b border-brand-palm/10
            pb-8 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="public-eyebrow">Secure checkout</p>

            <h1
                class="mt-4 font-display text-5xl text-brand-forest
                    sm:text-6xl">
                Complete Your Order
            </h1>
        </div>

        <a
            href="{{ route('cart.index') }}"
            class="text-sm font-semibold text-brand-palm underline
                decoration-brand-palm/30 underline-offset-4">
            Return to cart
        </a>
    </div>

    @error('checkout')
        <x-public.alert type="error" class="mt-8">
            {{ $message }}
        </x-public.alert>
    @enderror

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

            <p class="mx-auto mt-4 max-w-xl text-brand-muted">
                Add at least one menu item before continuing to checkout.
            </p>

            <a
                href="{{ route('menu') }}"
                class="public-button-primary mt-8">
                Browse the Menu
            </a>
        </div>
    @else
        <form wire:submit="placeOrder" class="mt-10">
            <div class="grid gap-10 lg:grid-cols-[1fr_23rem]">
                <div class="space-y-8">
                    <section
                        class="rounded-island border border-brand-palm/10
                            bg-white p-6 shadow-island sm:p-8">
                        <p class="public-eyebrow">Contact</p>

                        <h2
                            class="mt-3 font-display text-3xl
                                text-brand-forest">
                            Customer Details
                        </h2>

                        <div class="mt-7 grid gap-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label
                                    for="checkout-name"
                                    class="text-sm font-semibold
                                        text-brand-forest">
                                    Full name
                                </label>

                                <input
                                    id="checkout-name"
                                    type="text"
                                    wire:model.blur="name"
                                    autocomplete="name"
                                    class="mt-2 min-h-12 w-full rounded-island
                                        border border-brand-palm/20
                                        bg-brand-cream px-4">

                                @error('name')
                                    <p
                                        class="mt-2 text-sm font-medium
                                            text-brand-coral-dark"
                                        role="alert">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="checkout-email"
                                    class="text-sm font-semibold
                                        text-brand-forest">
                                    Email
                                </label>

                                <input
                                    id="checkout-email"
                                    type="email"
                                    wire:model.blur="email"
                                    autocomplete="email"
                                    class="mt-2 min-h-12 w-full rounded-island
                                        border border-brand-palm/20
                                        bg-brand-cream px-4">

                                @error('email')
                                    <p
                                        class="mt-2 text-sm font-medium
                                            text-brand-coral-dark"
                                        role="alert">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="checkout-phone"
                                    class="text-sm font-semibold
                                        text-brand-forest">
                                    Phone
                                </label>

                                <input
                                    id="checkout-phone"
                                    type="tel"
                                    wire:model.blur="phone"
                                    autocomplete="tel"
                                    class="mt-2 min-h-12 w-full rounded-island
                                        border border-brand-palm/20
                                        bg-brand-cream px-4">

                                @error('phone')
                                    <p
                                        class="mt-2 text-sm font-medium
                                            text-brand-coral-dark"
                                        role="alert">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-island border border-brand-palm/10
                            bg-white p-6 shadow-island sm:p-8">
                        <p class="public-eyebrow">Fulfillment</p>

                        <h2
                            class="mt-3 font-display text-3xl
                                text-brand-forest">
                            {{ $fulfillmentMethod->label() }}
                        </h2>

                        @if (
                            $fulfillmentMethod
                            === \App\Enums\FulfillmentMethod::Delivery
                        )
                            <div class="mt-7 grid gap-6 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label
                                        for="recipient-name"
                                        class="text-sm font-semibold
                                            text-brand-forest">
                                        Recipient name
                                    </label>

                                    <input
                                        id="recipient-name"
                                        type="text"
                                        wire:model.blur="recipientName"
                                        autocomplete="name"
                                        class="mt-2 min-h-12 w-full
                                            rounded-island border
                                            border-brand-palm/20
                                            bg-brand-cream px-4">

                                    @error('recipientName')
                                        <p
                                            class="mt-2 text-sm font-medium
                                                text-brand-coral-dark"
                                            role="alert">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label
                                        for="street-address"
                                        class="text-sm font-semibold
                                            text-brand-forest">
                                        Street address
                                    </label>

                                    <input
                                        id="street-address"
                                        type="text"
                                        wire:model.blur="streetAddress"
                                        autocomplete="street-address"
                                        class="mt-2 min-h-12 w-full
                                            rounded-island border
                                            border-brand-palm/20
                                            bg-brand-cream px-4">

                                    @error('streetAddress')
                                        <p
                                            class="mt-2 text-sm font-medium
                                                text-brand-coral-dark"
                                            role="alert">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label
                                        for="apartment"
                                        class="text-sm font-semibold
                                            text-brand-forest">
                                        Apartment or unit
                                        <span class="font-normal text-brand-muted">
                                            (optional)
                                        </span>
                                    </label>

                                    <input
                                        id="apartment"
                                        type="text"
                                        wire:model.blur="apartmentOrUnit"
                                        autocomplete="address-line2"
                                        class="mt-2 min-h-12 w-full
                                            rounded-island border
                                            border-brand-palm/20
                                            bg-brand-cream px-4">
                                </div>

                                <div>
                                    <label
                                        for="delivery-city"
                                        class="text-sm font-semibold
                                            text-brand-forest">
                                        City
                                    </label>

                                    <input
                                        id="delivery-city"
                                        type="text"
                                        wire:model.blur="deliveryCity"
                                        autocomplete="address-level2"
                                        class="mt-2 min-h-12 w-full
                                            rounded-island border
                                            border-brand-palm/20
                                            bg-brand-cream px-4">

                                    @error('deliveryCity')
                                        <p
                                            class="mt-2 text-sm font-medium
                                                text-brand-coral-dark"
                                            role="alert">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        for="delivery-state"
                                        class="text-sm font-semibold
                                            text-brand-forest">
                                        State
                                    </label>

                                    <input
                                        id="delivery-state"
                                        type="text"
                                        maxlength="2"
                                        wire:model.blur="deliveryState"
                                        autocomplete="address-level1"
                                        class="mt-2 min-h-12 w-full uppercase
                                            rounded-island border
                                            border-brand-palm/20
                                            bg-brand-cream px-4">

                                    @error('deliveryState')
                                        <p
                                            class="mt-2 text-sm font-medium
                                                text-brand-coral-dark"
                                            role="alert">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        for="delivery-postal-code"
                                        class="text-sm font-semibold
                                            text-brand-forest">
                                        ZIP code
                                    </label>

                                    <input
                                        id="delivery-postal-code"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="5"
                                        readonly
                                        wire:model="deliveryPostalCode"
                                        autocomplete="postal-code"
                                        class="mt-2 min-h-12 w-full
                                            rounded-island border
                                            border-brand-palm/20
                                            bg-brand-sand-soft px-4">

                                    <p class="mt-2 text-xs text-brand-muted">
                                        Change the delivery ZIP from the cart.
                                    </p>

                                    @error('deliveryPostalCode')
                                        <p
                                            class="mt-2 text-sm font-medium
                                                text-brand-coral-dark"
                                            role="alert">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        for="delivery-phone"
                                        class="text-sm font-semibold
                                            text-brand-forest">
                                        Delivery phone
                                    </label>

                                    <input
                                        id="delivery-phone"
                                        type="tel"
                                        wire:model.blur="deliveryPhone"
                                        autocomplete="tel"
                                        class="mt-2 min-h-12 w-full
                                            rounded-island border
                                            border-brand-palm/20
                                            bg-brand-cream px-4">

                                    @error('deliveryPhone')
                                        <p
                                            class="mt-2 text-sm font-medium
                                                text-brand-coral-dark"
                                            role="alert">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label
                                        for="delivery-instructions"
                                        class="text-sm font-semibold
                                            text-brand-forest">
                                        Delivery instructions
                                        <span class="font-normal text-brand-muted">
                                            (optional)
                                        </span>
                                    </label>

                                    <textarea
                                        id="delivery-instructions"
                                        rows="3"
                                        wire:model.blur="deliveryInstructions"
                                        class="mt-2 w-full rounded-island
                                            border border-brand-palm/20
                                            bg-brand-cream px-4 py-3"></textarea>

                                    @error('deliveryInstructions')
                                        <p
                                            class="mt-2 text-sm font-medium
                                                text-brand-coral-dark"
                                            role="alert">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        @else
                            <p class="mt-5 leading-7 text-brand-muted">
                                Your order will be prepared for collection
                                at the restaurant.
                            </p>

                            @if (
                                \App\Models\SiteSetting::pickupInstructions()
                            )
                                <p
                                    class="mt-4 rounded-island
                                        bg-brand-sand-soft p-4 text-sm
                                        leading-6 text-brand-forest">
                                    {{ \App\Models\SiteSetting::pickupInstructions() }}
                                </p>
                            @endif
                        @endif
                    </section>

                    <section
                        class="rounded-island border border-brand-palm/10
                            bg-white p-6 shadow-island sm:p-8">
                        <p class="public-eyebrow">Payment</p>

                        <h2
                            class="mt-3 font-display text-3xl
                                text-brand-forest">
                            Payment Method
                        </h2>

                        @if ($paymentOptions === [])
                            <x-public.alert type="warning" class="mt-6">
                                No payment method is currently available for
                                this fulfillment option.
                            </x-public.alert>
                        @else
                            <div class="mt-6 space-y-3">
                                @foreach ($paymentOptions as $value => $label)
                                    <label
                                        class="flex cursor-pointer items-center
                                            gap-3 rounded-island border
                                            border-brand-palm/15
                                            bg-brand-cream p-4">
                                        <input
                                            type="radio"
                                            value="{{ $value }}"
                                            wire:model="paymentMethod"
                                            class="size-4">

                                        <span
                                            class="font-semibold
                                                text-brand-forest">
                                            {{ $label }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        @error('paymentMethod')
                            <p
                                class="mt-3 text-sm font-medium
                                    text-brand-coral-dark"
                                role="alert">
                                {{ $message }}
                            </p>
                        @enderror
                    </section>

                    <section
                        class="rounded-island border border-brand-palm/10
                            bg-white p-6 shadow-island sm:p-8">
                        <label
                            for="customer-note"
                            class="font-display text-3xl text-brand-forest">
                            Order Note
                        </label>

                        <p class="mt-2 text-sm leading-6 text-brand-muted">
                            Optional preparation or order instructions.
                            Do not include payment-card details.
                        </p>

                        <textarea
                            id="customer-note"
                            rows="4"
                            wire:model.blur="customerNote"
                            class="mt-5 w-full rounded-island border
                                border-brand-palm/20 bg-brand-cream
                                px-4 py-3"></textarea>

                        @error('customerNote')
                            <p
                                class="mt-2 text-sm font-medium
                                    text-brand-coral-dark"
                                role="alert">
                                {{ $message }}
                            </p>
                        @enderror
                    </section>
                </div>

                <aside
                    class="h-fit rounded-island border
                        border-brand-palm/10 bg-brand-sand-soft
                        p-7 shadow-island lg:sticky lg:top-28">
                    <h2
                        class="font-display text-3xl text-brand-forest">
                        Order Summary
                    </h2>

                    <div class="mt-6 space-y-5">
                        @foreach ($cart['items'] as $item)
                            <div
                                class="border-b border-brand-palm/10
                                    pb-5 last:border-b-0">
                                <div
                                    class="flex items-start
                                        justify-between gap-4">
                                    <div>
                                        <p
                                            class="font-semibold
                                                text-brand-forest">
                                            {{ $item['quantity'] }} ×
                                            {{ $item['name'] }}
                                        </p>

                                        @foreach ($item['options'] as $option)
                                            <p
                                                class="mt-1 text-xs
                                                    text-brand-muted">
                                                {{ $option['group_name'] }}:
                                                {{ $option['name'] }}
                                            </p>
                                        @endforeach
                                    </div>

                                    <p
                                        class="font-semibold
                                            text-brand-forest">
                                        {{ $item['formatted_line_total'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <dl class="mt-6 space-y-4">
                        <div class="flex justify-between gap-4">
                            <dt class="text-brand-muted">
                                Subtotal
                            </dt>

                            <dd class="font-semibold text-brand-forest">
                                {{ $cart['formatted_subtotal'] }}
                            </dd>
                        </div>

                        @if ($cart['discount_cents'] > 0)
                            <div class="flex justify-between gap-4">
                                <dt class="text-brand-muted">
                                    Discount
                                </dt>

                                <dd class="font-semibold text-emerald-700">
                                    -{{ $cart['formatted_discount'] }}
                                </dd>
                            </div>
                        @endif

                        <div class="flex justify-between gap-4">
                            <dt class="text-brand-muted">
                                Tax
                            </dt>

                            <dd class="font-semibold text-brand-forest">
                                {{ $cart['formatted_tax'] }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-brand-muted">
                                Delivery
                            </dt>

                            <dd class="font-semibold text-brand-forest">
                                {{ $cart['formatted_delivery_fee'] }}
                            </dd>
                        </div>

                        <div
                            class="flex justify-between gap-4 border-t
                                border-brand-palm/10 pt-5">
                            <dt
                                class="font-display text-2xl
                                    text-brand-forest">
                                Total
                            </dt>

                            <dd
                                class="text-2xl font-semibold
                                    text-brand-forest">
                                {{ $cart['formatted_grand_total'] }}
                            </dd>
                        </div>
                    </dl>

                    @if (
                        $cart['is_checkout_ready']
                        && $paymentOptions !== []
                    )
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="placeOrder"
                            class="public-button-primary mt-7 w-full
                                disabled:cursor-not-allowed
                                disabled:opacity-60">
                            <span wire:loading.remove wire:target="placeOrder">
                                Place Order
                            </span>

                            <span wire:loading wire:target="placeOrder">
                                Placing Order…
                            </span>
                        </button>
                    @else
                        <button
                            type="button"
                            disabled
                            class="public-button-primary mt-7 w-full
                                cursor-not-allowed opacity-60">
                            Checkout Unavailable
                        </button>
                    @endif

                    <p
                        class="mt-4 text-center text-xs leading-5
                            text-brand-muted">
                        Your order is not confirmed until restaurant staff
                        reviews and accepts it.
                    </p>
                </aside>
            </div>
        </form>
    @endif
</div>
