@php
    use App\Enums\OrderStatus;
    use App\Support\Money;

    /*
     * Convert the order lifecycle into a non-authoritative visual progress
     * value. The actual order status remains the enum stored by the backend.
     */
    $statusProgress = match ($order->status) {
        OrderStatus::PendingConfirmation => 14,
        OrderStatus::Confirmed => 32,
        OrderStatus::Preparing => 52,
        OrderStatus::ReadyForPickup,
        OrderStatus::OutForDelivery => 74,
        OrderStatus::PickedUp,
        OrderStatus::Delivered => 90,
        OrderStatus::Completed,
        OrderStatus::Rejected,
        OrderStatus::Cancelled => 100,
    };

    $statusMessage = match ($order->status) {
        OrderStatus::PendingConfirmation =>
            'The restaurant team is reviewing your order.',
        OrderStatus::Confirmed =>
            'Your order has been accepted by the restaurant.',
        OrderStatus::Preparing =>
            'The kitchen is currently preparing your order.',
        OrderStatus::ReadyForPickup =>
            'Your order is ready to collect.',
        OrderStatus::OutForDelivery =>
            'Your order has left the restaurant for delivery.',
        OrderStatus::PickedUp =>
            'Your pickup has been recorded.',
        OrderStatus::Delivered =>
            'Your delivery has arrived.',
        OrderStatus::Completed =>
            'This order has been completed.',
        OrderStatus::Rejected =>
            'The restaurant could not accept this order.',
        OrderStatus::Cancelled =>
            'This order has been cancelled.',
    };
@endphp

<x-layouts.public
    title="Order Received"
    description="Your Coast & Cay order has been received."
    :header-overlay="true">
    <div
        data-public-page-motion
        class="order-confirmation-page">
        <section
            data-public-fullscreen-hero
            class="public-fullscreen-hero order-confirmation-hero">
            <div class="public-container relative z-10">
                <div
                    class="grid items-center gap-12
                        lg:grid-cols-[minmax(0,0.85fr)_minmax(32rem,1.15fr)]
                        lg:gap-16">
                    <div data-public-hero-copy>
                        <div
                            class="flex size-20 items-center justify-center
                                rounded-full border border-white/20
                                bg-white/10 text-brand-sun
                                shadow-elevated backdrop-blur-md">
                            <svg
                                class="size-9"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m5 12.5 4.2 4.2L19 7" />
                            </svg>
                        </div>

                        <p
                            class="mt-8 text-xs font-semibold uppercase
                                tracking-[0.22em] text-brand-sun">
                            Order received
                        </p>

                        <h1
                            class="mt-5 font-display text-5xl
                                leading-[0.94] text-white sm:text-7xl">
                            Thank you.
                            <span class="block text-white/72">
                                We have your order.
                            </span>
                        </h1>

                        <p
                            class="mt-7 max-w-xl text-base leading-8
                                text-white/72 sm:text-lg">
                            {{ $statusMessage }}
                            Keep the order number and secure tracking link
                            available for reference.
                        </p>

                        <div class="mt-8">
                            <x-public.order-status
                                :status="$order->status"
                                class="bg-white/12 text-white
                                    [&::before]:bg-brand-sun" />
                        </div>

                        <div class="mt-8 max-w-xl">
                            <div class="order-progress-track">
                                <span
                                    data-public-progress
                                    data-progress="{{ $statusProgress }}"
                                    class="order-progress-value"
                                    style="transform: scaleX({{
                                        $statusProgress / 100
                                    }});">
                                </span>
                            </div>

                            <div
                                class="mt-3 flex justify-between text-[0.65rem]
                                    font-semibold uppercase
                                    tracking-[0.16em] text-white/48">
                                <span>Received</span>
                                <span>Fulfilled</span>
                            </div>
                        </div>

                        <div
                            class="mt-10 grid gap-4
                                border-t border-white/14 pt-8 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-white/48">
                                    Order number
                                </p>

                                <p
                                    class="mt-2 font-semibold
                                        text-white">
                                    {{ $order->order_number }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-white/48">
                                    Fulfillment
                                </p>

                                <p
                                    class="mt-2 font-semibold
                                        text-white">
                                    {{ $order->fulfillment_method->label() }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-white/48">
                                    Payment method
                                </p>

                                <p
                                    class="mt-2 font-semibold
                                        text-white">
                                    {{ $order->payment_method->label() }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-white/48">
                                    Payment status
                                </p>

                                <p
                                    class="mt-2 font-semibold
                                        text-white">
                                    {{ $order->payment_status->label() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        data-public-hero-media
                        class="order-receipt-card">
                        <div
                            class="border-b border-line px-6 py-6
                                sm:px-8">
                            <div
                                class="flex items-start justify-between
                                    gap-5">
                                <div>
                                    <p class="public-eyebrow">
                                        Your receipt
                                    </p>

                                    <h2
                                        class="mt-3 font-display text-3xl
                                            text-primary-deep">
                                        Order summary
                                    </h2>
                                </div>

                                <p
                                    class="font-display text-3xl
                                        text-primary-deep">
                                    {{ $order->formattedGrandTotal() }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="order-receipt-items divide-y
                                divide-line px-6 sm:px-8">
                            @foreach ($order->items as $item)
                                <article class="py-5">
                                    <div
                                        class="flex items-start
                                            justify-between gap-5">
                                        <div class="min-w-0">
                                            <p
                                                class="font-semibold
                                                    text-primary-deep">
                                                {{ $item->quantity }} ×
                                                {{ $item->name }}
                                            </p>

                                            @foreach (
                                                $item->selected_options ?? []
                                                as $option
                                            )
                                                <p
                                                    class="mt-1 text-sm
                                                        leading-6 text-muted">
                                                    {{ $option['group_name'] }}:
                                                    {{ $option['name'] }}
                                                </p>
                                            @endforeach
                                        </div>

                                        <p
                                            class="shrink-0 font-semibold
                                                text-primary-deep">
                                            {{ Money::formatUsd(
                                                $item->line_total_cents,
                                            ) }}
                                        </p>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div
                            class="border-t border-line bg-surface-soft/55
                                px-6 py-6 sm:px-8">
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between gap-5">
                                    <dt class="text-muted">
                                        Subtotal
                                    </dt>

                                    <dd
                                        class="font-semibold
                                            text-primary-deep">
                                        {{ Money::formatUsd(
                                            $order->subtotal_cents,
                                        ) }}
                                    </dd>
                                </div>

                                @if ($order->discount_cents > 0)
                                    <div class="flex justify-between gap-5">
                                        <dt class="text-muted">
                                            Discount
                                        </dt>

                                        <dd
                                            class="font-semibold
                                                text-primary">
                                            −{{ Money::formatUsd(
                                                $order->discount_cents,
                                            ) }}
                                        </dd>
                                    </div>
                                @endif

                                <div class="flex justify-between gap-5">
                                    <dt class="text-muted">
                                        Tax
                                    </dt>

                                    <dd
                                        class="font-semibold
                                            text-primary-deep">
                                        {{ Money::formatUsd(
                                            $order->tax_cents,
                                        ) }}
                                    </dd>
                                </div>

                                @if ($order->delivery_cents > 0)
                                    <div class="flex justify-between gap-5">
                                        <dt class="text-muted">
                                            Delivery
                                        </dt>

                                        <dd
                                            class="font-semibold
                                                text-primary-deep">
                                            {{ Money::formatUsd(
                                                $order->delivery_cents,
                                            ) }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>

                            <div
                                class="mt-6 flex items-center justify-between
                                    border-t border-primary/10 pt-5">
                                <span
                                    class="font-display text-2xl
                                        text-primary-deep">
                                    Total
                                </span>

                                <span
                                    class="text-2xl font-semibold
                                        text-primary-deep">
                                    {{ $order->formattedGrandTotal() }}
                                </span>
                            </div>

                            <div class="mt-7 grid gap-3 sm:grid-cols-2">
                                <a
                                    href="{{ $trackingUrl }}"
                                    class="public-button-primary">
                                    Track This Order
                                </a>

                                <a
                                    href="{{ route('menu') }}"
                                    class="public-button-secondary
                                        text-primary">
                                    Return to Menu
                                </a>
                            </div>

                            @guest
                                <p
                                    class="mt-5 text-xs leading-6
                                        text-muted">
                                    Save the secure guest tracking link. It
                                    expires 30 days after checkout.
                                </p>
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>
