@php
    use App\Enums\OrderStatus;

    $orders = $this->orders;
    $latestOrder = $orders->first();

    /*
     * Convert each backend lifecycle status into visual progress only.
     */
    $progressForStatus = static fn (OrderStatus $status): int => match (
        $status
    ) {
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

    /*
     * Provide concise customer-facing context without changing order state.
     */
    $summaryForStatus = static fn (OrderStatus $status): string => match (
        $status
    ) {
        OrderStatus::PendingConfirmation =>
            'Waiting for restaurant confirmation.',
        OrderStatus::Confirmed =>
            'Accepted and scheduled for preparation.',
        OrderStatus::Preparing =>
            'The kitchen is preparing this order.',
        OrderStatus::ReadyForPickup =>
            'Ready to collect from the restaurant.',
        OrderStatus::OutForDelivery =>
            'Currently travelling to the delivery address.',
        OrderStatus::PickedUp =>
            'The pickup handoff has been recorded.',
        OrderStatus::Delivered =>
            'The delivery handoff has been recorded.',
        OrderStatus::Completed =>
            'This order is complete.',
        OrderStatus::Rejected =>
            'The restaurant was unable to accept this order.',
        OrderStatus::Cancelled =>
            'This order was cancelled.',
    };
@endphp

<div
    id="account-orders"
    data-public-page-motion
    class="account-orders-page">
    <section
        data-public-fullscreen-hero
        class="account-orders-hero">
        <div class="public-container relative z-10">
            <div
                data-public-hero-copy
                class="max-w-4xl">
                <p class="public-eyebrow text-brand-sun">
                    Customer account
                </p>

                <h1
                    class="mt-6 font-display text-5xl leading-[0.94]
                        text-white sm:text-7xl lg:text-[6rem]">
                    Your orders,
                    <span class="block text-white/68">
                        clearly tracked.
                    </span>
                </h1>

                <p
                    class="mt-7 max-w-2xl text-base leading-8
                        text-white/70 sm:text-lg">
                    Review current orders, previous purchases, payment
                    details, and restaurant fulfillment updates in one place.
                </p>
            </div>

            <div
                data-public-hero-media
                class="mt-10 max-w-5xl">
                <x-account.navigation />
            </div>

            <dl
                data-public-reveal-group
                class="mt-8 grid max-w-4xl gap-3
                    sm:grid-cols-3">
                <div
                    data-public-reveal-item
                    class="rounded-[1.25rem] border border-white/14
                        bg-white/[0.08] p-5 backdrop-blur-md">
                    <dt
                        class="text-[0.65rem] font-semibold uppercase
                            tracking-[0.17em] text-white/48">
                        Orders saved
                    </dt>

                    <dd
                        class="mt-3 font-display text-3xl
                            text-white">
                        {{ $orders->total() }}
                    </dd>
                </div>

                <div
                    data-public-reveal-item
                    class="rounded-[1.25rem] border border-white/14
                        bg-white/[0.08] p-5 backdrop-blur-md">
                    <dt
                        class="text-[0.65rem] font-semibold uppercase
                            tracking-[0.17em] text-white/48">
                        Latest activity
                    </dt>

                    <dd
                        class="mt-3 font-semibold leading-6
                            text-white">
                        {{ $latestOrder
                            ? $latestOrder->placed_at->format('M j, Y')
                            : 'No orders yet' }}
                    </dd>
                </div>

                <div
                    data-public-reveal-item
                    class="rounded-[1.25rem] border border-white/14
                        bg-white/[0.08] p-5 backdrop-blur-md">
                    <dt
                        class="text-[0.65rem] font-semibold uppercase
                            tracking-[0.17em] text-white/48">
                        Account access
                    </dt>

                    <dd
                        class="mt-3 font-semibold leading-6
                            text-white">
                        Private and secure
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    <section class="account-orders-content">
        <div class="public-container">
            @if ($orders->isEmpty())
                <div
                    data-public-reveal-group
                    class="rounded-[2.5rem] border border-dashed
                        border-primary/22 bg-surface/90 px-6 py-16
                        text-center shadow-card backdrop-blur-sm">
                    <div data-public-reveal-item>
                        <div
                            class="mx-auto flex size-16 items-center
                                justify-center rounded-full
                                bg-primary/[0.08] text-primary">
                            <svg
                                class="size-7"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5.5 8.5h13l-1 11h-11l-1-11Z" />

                                <path
                                    stroke-linecap="round"
                                    d="M9 9V6.75a3 3 0 0 1 6 0V9" />
                            </svg>
                        </div>

                        <p class="public-eyebrow mt-6">
                            Nothing here yet
                        </p>

                        <h2
                            class="mt-4 font-display text-4xl
                                text-primary-deep">
                            Your first order is still ahead.
                        </h2>

                        <p
                            class="mx-auto mt-5 max-w-xl leading-8
                                text-muted">
                            Once you place an order while signed in, its
                            payment and fulfillment progress will appear here.
                        </p>

                        <a
                            href="{{ route('menu') }}"
                            class="public-button-primary mt-8">
                            Explore the Menu
                        </a>
                    </div>
                </div>
            @else
                <div
                    data-public-reveal-group
                    class="grid gap-5">
                    @foreach ($orders as $order)
                        @php
                            $progress = $progressForStatus(
                                $order->status,
                            );
                        @endphp

                        <article
                            wire:key="customer-order-{{ $order->id }}"
                            data-public-reveal-item
                            data-order-status="{{ $order->status->value }}"
                            class="order-history-card p-6
                                sm:p-8 lg:p-9">
                            <div
                                class="grid gap-7
                                    lg:grid-cols-[minmax(0,1fr)_auto]
                                    lg:items-center">
                                <div>
                                    <div
                                        class="flex flex-wrap items-center
                                            gap-3">
                                        <p
                                            class="font-display text-2xl
                                                text-primary-deep
                                                sm:text-3xl">
                                            {{ $order->order_number }}
                                        </p>

                                        <x-public.order-status
                                            :status="$order->status" />
                                    </div>

                                    <p
                                        class="mt-3 text-sm leading-6
                                            text-muted">
                                        Placed

                                        <time
                                            datetime="{{ $order->placed_at->toIso8601String() }}">
                                            {{ $order->placed_at->format(
                                                'M j, Y \a\t g:i A',
                                            ) }}
                                        </time>
                                    </p>

                                    <p
                                        class="mt-4 max-w-2xl text-sm
                                            leading-7 text-muted">
                                        {{ $summaryForStatus(
                                            $order->status,
                                        ) }}
                                    </p>

                                    <dl
                                        class="mt-6 flex flex-wrap gap-x-9
                                            gap-y-4 text-sm">
                                        <div>
                                            <dt class="text-muted">
                                                Fulfillment
                                            </dt>

                                            <dd
                                                class="mt-1 font-semibold
                                                    text-primary-deep">
                                                {{ $order->fulfillment_method->label() }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-muted">
                                                Payment
                                            </dt>

                                            <dd
                                                class="mt-1 font-semibold
                                                    text-primary-deep">
                                                {{ $order->payment_status->label() }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-muted">
                                                Items
                                            </dt>

                                            <dd
                                                class="mt-1 font-semibold
                                                    text-primary-deep">
                                                {{ $order->items_count }}
                                                {{ $order->items_count === 1
                                                    ? 'item'
                                                    : 'items' }}
                                            </dd>
                                        </div>
                                    </dl>

                                    <div class="mt-7 max-w-2xl">
                                        <div class="order-history-progress">
                                            <span
                                                data-public-progress
                                                data-progress="{{ $progress }}"
                                                style="transform: scaleX({{
                                                    $progress / 100
                                                }});">
                                            </span>
                                        </div>

                                        <div
                                            class="mt-2 flex justify-between
                                                text-[0.62rem] font-semibold
                                                uppercase tracking-[0.15em]
                                                text-muted">
                                            <span>Received</span>
                                            <span>Completed</span>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="flex flex-col items-start gap-4
                                        border-t border-line pt-6
                                        lg:items-end lg:border-l
                                        lg:border-t-0 lg:pl-9 lg:pt-0">
                                    <p
                                        class="text-xs font-semibold
                                            uppercase tracking-[0.16em]
                                            text-muted">
                                        Order total
                                    </p>

                                    <p
                                        class="font-display text-4xl
                                            text-primary-deep">
                                        {{ $order->formattedGrandTotal() }}
                                    </p>

                                    <a
                                        href="{{ route(
                                            'account.orders.show',
                                            ['order' => $order],
                                        ) }}"
                                        class="public-button-primary">
                                        View Order
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($orders->hasPages())
                    <div class="mt-10">
                        {{ $orders->links(
                            data: [
                                'scrollTo' => '#account-orders',
                            ],
                        ) }}
                    </div>
                @endif
            @endif
        </div>
    </section>
</div>
