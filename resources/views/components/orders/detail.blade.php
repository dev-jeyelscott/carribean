@props([
'order',
'showAccountNavigation' => false,
'backUrl' => '#',
'backLabel' => 'Go back',
'canConfirmReceived' => false,
])

@php
/*
* Resolve the immutable fulfillment snapshot used by the customer-facing
* page. Internal order notes are intentionally never rendered here.
*/
$deliveryAddress = $order->addresses->firstWhere(
'type',
'delivery',
);

$isDelivery = $order->fulfillment_method
=== \App\Enums\FulfillmentMethod::Delivery;

/*
* Build the expected customer journey from the order's fulfillment type.
* Exceptional terminal states are displayed separately from this journey.
*/
$statusFlow = $isDelivery
? [
\App\Enums\OrderStatus::PendingConfirmation,
\App\Enums\OrderStatus::Confirmed,
\App\Enums\OrderStatus::Preparing,
\App\Enums\OrderStatus::OutForDelivery,
\App\Enums\OrderStatus::Delivered,
\App\Enums\OrderStatus::Completed,
]
: [
\App\Enums\OrderStatus::PendingConfirmation,
\App\Enums\OrderStatus::Confirmed,
\App\Enums\OrderStatus::Preparing,
\App\Enums\OrderStatus::ReadyForPickup,
\App\Enums\OrderStatus::PickedUp,
\App\Enums\OrderStatus::Completed,
];

$currentStepIndex = array_search(
$order->status,
$statusFlow,
true,
);

$isExceptionalStatus = in_array(
$order->status,
[
\App\Enums\OrderStatus::Rejected,
\App\Enums\OrderStatus::Cancelled,
],
true,
);

$placedAt = $order->placed_at ?? $order->created_at;

$latestHistory = $order->statusHistories->last();

$lastUpdatedAt = $latestHistory?->created_at
?? $placedAt;

/*
* Provide a concise next-step explanation instead of exposing raw enum
* values or operational terminology.
*/
$statusDescription = match ($order->status) {
\App\Enums\OrderStatus::PendingConfirmation =>
'The restaurant received your order and is reviewing it now.',

\App\Enums\OrderStatus::Confirmed =>
'Your order has been accepted and will move into preparation soon.',

\App\Enums\OrderStatus::Preparing =>
'The kitchen is preparing your order.',

\App\Enums\OrderStatus::ReadyForPickup =>
'Your order is ready. You may now collect it from the restaurant.',

\App\Enums\OrderStatus::OutForDelivery =>
'Your order has left the restaurant and is on its way.',

\App\Enums\OrderStatus::PickedUp =>
'The order has been collected. Confirm receipt when convenient.',

\App\Enums\OrderStatus::Delivered =>
'The order has been delivered. Confirm receipt when convenient.',

\App\Enums\OrderStatus::Completed =>
'This order has been completed. Thank you for dining with us.',

\App\Enums\OrderStatus::Rejected =>
'The restaurant was unable to accept this order.',

\App\Enums\OrderStatus::Cancelled =>
'This order has been cancelled.',
};

/*
* Explain the customer's payment state independently from fulfillment.
*/
$paymentDescription = match ($order->payment_status) {
\App\Enums\PaymentStatus::Pending =>
'Payment will be collected using the selected payment method.',

\App\Enums\PaymentStatus::Paid =>
'Payment has been received.',

\App\Enums\PaymentStatus::Failed =>
'The payment was not completed. Contact the restaurant for help.',

\App\Enums\PaymentStatus::Refunded =>
'The recorded payment has been refunded.',
};
@endphp

<div
    class="order-detail-page"
    data-order-detail-page>
    {{-- Branded status-first hero --}}
    <section
        class="order-detail-hero"
        aria-labelledby="order-detail-heading">
        <div class="public-container">
            <a
                href="{{ $backUrl }}"
                class="order-detail-hero__back">
                <svg
                    aria-hidden="true"
                    viewBox="0 0 24 24"
                    class="size-4 fill-none stroke-current"
                    stroke-width="1.8">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m15 18-6-6 6-6" />
                </svg>

                {{ $backLabel }}
            </a>

            <div
                class="mt-8 grid items-end gap-10
                    lg:grid-cols-[minmax(0,1fr)_auto]">
                <div>
                    <p class="order-detail-hero__eyebrow">
                        Order tracking
                    </p>

                    <h1
                        id="order-detail-heading"
                        class="order-detail-hero__title mt-4">
                        {{ $order->order_number
                            ?: 'Order '.$order->public_id }}
                    </h1>

                    <p class="order-detail-hero__description mt-6">
                        {{ $statusDescription }}
                    </p>

                    <div class="order-detail-hero__meta mt-7">
                        @if ($placedAt)
                        <span>
                            <svg
                                aria-hidden="true"
                                viewBox="0 0 24 24"
                                class="size-4 fill-none stroke-current"
                                stroke-width="1.8">
                                <circle cx="12" cy="12" r="9" />
                                <path
                                    stroke-linecap="round"
                                    d="M12 7v5l3 2" />
                            </svg>

                            Placed
                            <time datetime="{{ $placedAt->toIso8601String() }}">
                                {{ $placedAt->format('M j, Y \a\t g:i A') }}
                            </time>
                        </span>
                        @endif

                        <span>
                            <svg
                                aria-hidden="true"
                                viewBox="0 0 24 24"
                                class="size-4 fill-none stroke-current"
                                stroke-width="1.8">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.5 7.5h15v11h-15zM8 7.5V5.75h8V7.5" />
                            </svg>

                            {{ $order->fulfillment_method->label() }}
                        </span>

                        <span>
                            <svg
                                aria-hidden="true"
                                viewBox="0 0 24 24"
                                class="size-4 fill-none stroke-current"
                                stroke-width="1.8">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 7h14v10H5zM8 11h4" />
                            </svg>

                            {{ $order->formattedGrandTotal() }}
                        </span>
                    </div>
                </div>

                <aside
                    class="order-detail-hero__status"
                    aria-label="Current order status">
                    <div>
                        <span
                            class="order-detail-status-badge"
                            data-order-status="{{ $order->status->value }}">
                            <span
                                class="size-1.5 rounded-full bg-current"
                                aria-hidden="true">
                            </span>

                            {{ $order->status->label() }}
                        </span>

                        <p
                            class="mt-4 font-display text-3xl
                                leading-tight text-white">
                            {{ $statusDescription }}
                        </p>

                        @if ($lastUpdatedAt)
                        <p class="mt-4 text-sm text-white/60">
                            Updated
                            <time
                                datetime="{{ $lastUpdatedAt->toIso8601String() }}">
                                {{ $lastUpdatedAt->diffForHumans() }}
                            </time>
                        </p>
                        @endif
                    </div>

                    <button
                        type="button"
                        wire:click="$refresh"
                        wire:loading.attr="disabled"
                        class="inline-flex min-h-11 items-center
                            justify-center gap-2 rounded-xl border
                            border-white/25 px-4 text-xs font-semibold
                            uppercase tracking-[0.12em] text-white
                            transition hover:border-white/60
                            hover:bg-white hover:text-primary-deep
                            disabled:cursor-wait disabled:opacity-60">
                        <svg
                            aria-hidden="true"
                            viewBox="0 0 24 24"
                            class="size-4 fill-none stroke-current"
                            stroke-width="1.8">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M20 7v5h-5M4 17v-5h5M6.2 8.5A7 7 0 0 1 18 6l2 2M18 15.5A7 7 0 0 1 6 18l-2-2" />
                        </svg>

                        Refresh status
                    </button>
                </aside>
            </div>
        </div>
    </section>

    <div class="order-detail-shell">
        <div class="public-container max-w-[86rem]">
            @if ($showAccountNavigation)
            <div class="order-detail-account-navigation">
                <x-account.navigation />
            </div>
            @endif

            @if (session()->has('order_status'))
            <div
                role="status"
                aria-live="polite"
                class="mb-4 rounded-2xl border border-primary/20
                        bg-primary/10 px-5 py-4 font-semibold text-primary-deep">
                {{ session('order_status') }}
            </div>
            @endif

            {{-- Current status and payment summary --}}
            <div class="order-detail-overview">
                <article
                    class="order-detail-card order-detail-overview-card"
                    aria-labelledby="current-order-status-heading">
                    <div class="flex items-start gap-4">
                        <span class="order-detail-overview-card__icon">
                            <svg
                                aria-hidden="true"
                                viewBox="0 0 24 24"
                                class="size-5 fill-none stroke-current"
                                stroke-width="1.8">
                                <circle cx="12" cy="12" r="9" />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m8.5 12 2.25 2.25L15.5 9.5" />
                            </svg>
                        </span>

                        <div class="min-w-0">
                            <p class="order-detail-overview-label">
                                Current status
                            </p>

                            <h2
                                id="current-order-status-heading"
                                class="mt-2 font-display text-3xl
                                    text-primary-deep">
                                {{ $order->status->label() }}
                            </h2>

                            <p class="mt-3 max-w-2xl leading-7 text-muted">
                                {{ $statusDescription }}
                            </p>
                        </div>
                    </div>

                    @if ($canConfirmReceived)
                    <div
                        class="mt-6 border-t border-primary/10 pt-6">
                        <p class="text-sm leading-7 text-muted">
                            Your order has been fulfilled. Confirm that you
                            received it to complete the order now.
                        </p>

                        <button
                            type="button"
                            wire:click="confirmReceived"
                            wire:loading.attr="disabled"
                            wire:target="confirmReceived"
                            class="public-button-primary mt-5
                                    disabled:cursor-not-allowed
                                    disabled:opacity-60">
                            <span
                                wire:loading.remove
                                wire:target="confirmReceived">
                                Confirm received
                            </span>

                            <span
                                wire:loading
                                wire:target="confirmReceived">
                                Confirming…
                            </span>
                        </button>
                    </div>
                    @endif
                </article>

                <article
                    class="order-detail-card order-detail-overview-card"
                    aria-labelledby="payment-status-heading">
                    <div class="flex items-start gap-4">
                        <span class="order-detail-overview-card__icon">
                            <svg
                                aria-hidden="true"
                                viewBox="0 0 24 24"
                                class="size-5 fill-none stroke-current"
                                stroke-width="1.8">
                                <rect
                                    x="3"
                                    y="5"
                                    width="18"
                                    height="14"
                                    rx="2" />
                                <path
                                    stroke-linecap="round"
                                    d="M3 9h18M7 15h3" />
                            </svg>
                        </span>

                        <div>
                            <p class="order-detail-overview-label">
                                Payment
                            </p>

                            <h2
                                id="payment-status-heading"
                                class="mt-2 font-display text-2xl
                                    text-primary-deep">
                                {{ $order->payment_status->label() }}
                            </h2>

                            <span
                                class="order-detail-payment-badge mt-3"
                                data-payment-status="{{ $order->payment_status->value }}">
                                {{ $order->payment_method->label() }}
                            </span>

                            <p class="mt-3 text-sm leading-6 text-muted">
                                {{ $paymentDescription }}
                            </p>
                        </div>
                    </div>
                </article>
            </div>

            {{-- Visual fulfillment journey --}}
            @if ($isExceptionalStatus)
            <div
                class="order-detail-exception"
                role="status">
                <p class="font-semibold text-coral-deep">
                    {{ $order->status->label() }}
                </p>

                <p class="mt-2 text-sm leading-7 text-muted">
                    Review the update history below for the latest
                    restaurant message. Contact the restaurant when you
                    need further assistance.
                </p>
            </div>
            @else
            <section
                class="order-detail-card order-detail-progress"
                aria-labelledby="order-progress-heading">
                <div class="order-detail-progress__header">
                    <div>
                        <p class="order-detail-overview-label">
                            Fulfillment journey
                        </p>

                        <h2
                            id="order-progress-heading"
                            class="mt-2 font-display text-3xl
                                    text-primary-deep">
                            Where your order is now
                        </h2>
                    </div>

                    <p class="max-w-md text-sm leading-6 text-muted">
                        Restaurant updates appear here as your order moves
                        through each stage.
                    </p>
                </div>

                <ol
                    class="order-detail-progress__list"
                    aria-label="Order fulfillment progress">
                    @foreach ($statusFlow as $index => $status)
                    @php
                    $isComplete = is_int($currentStepIndex)
                    && $index < $currentStepIndex;

                        $isCurrent=is_int($currentStepIndex)
                        && $index===$currentStepIndex;

                        $stepState=$isComplete
                        ? 'complete'
                        : ($isCurrent ? 'current' : 'upcoming' );
                        @endphp

                        <li
                        class="order-detail-progress__step"
                        data-step-state="{{ $stepState }}"
                        @if ($isCurrent)
                        aria-current="step"
                        @endif>
                        <span class="order-detail-progress__marker">
                            @if ($isComplete)
                            <svg
                                aria-hidden="true"
                                viewBox="0 0 24 24"
                                class="size-4 fill-none
                                                stroke-current"
                                stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m6 12 4 4 8-8" />
                            </svg>
                            @else
                            {{ $index + 1 }}
                            @endif
                        </span>

                        <span class="order-detail-progress__label">
                            {{ $status->label() }}
                        </span>
                        </li>
                        @endforeach
                </ol>
            </section>
            @endif

            <div class="order-detail-main">
                <div class="space-y-6">
                    {{-- Immutable ordered-item snapshots --}}
                    <section
                        class="order-detail-card order-detail-section"
                        aria-labelledby="order-items-heading">
                        <p class="order-detail-overview-label">
                            Order items
                        </p>

                        <h2
                            id="order-items-heading"
                            class="order-detail-section-title mt-3">
                            What you ordered
                        </h2>

                        <div class="order-detail-items">
                            @foreach ($order->items as $item)
                            @php
                            $selectedOptions = collect(
                            $item->selected_options ?? [],
                            )->filter(
                            fn (mixed $option): bool =>
                            is_array($option)
                            && filled(
                            $option['name'] ?? null,
                            ),
                            );
                            @endphp

                            <article
                                class="order-detail-item"
                                wire:key="order-item-{{ $item->id }}">
                                <div class="flex min-w-0 items-start gap-3">
                                    <span
                                        class="order-detail-item__quantity">
                                        {{ $item->quantity }}
                                    </span>

                                    <div class="min-w-0">
                                        <h3
                                            class="font-display text-xl
                                                    leading-tight text-ink">
                                            {{ $item->name }}
                                        </h3>

                                        @if (filled($item->description))
                                        <p
                                            class="mt-2 line-clamp-2
                                                        text-sm leading-6
                                                        text-muted">
                                            {{ $item->description }}
                                        </p>
                                        @endif

                                        @if ($selectedOptions->isNotEmpty())
                                        <div
                                            class="order-detail-item__options">
                                            @foreach (
                                            $selectedOptions
                                            as $option
                                            )
                                            <span
                                                class="order-detail-item__option">
                                                {{ $option[
                                                                'group_name'
                                                            ] ?? 'Option' }}:
                                                {{ $option['name'] }}
                                            </span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <p
                                    class="order-detail-item__price
                                            font-semibold tabular-nums
                                            text-primary-deep">
                                    {{ \App\Support\Money::formatUsd(
                                            $item->line_total_cents,
                                        ) }}
                                </p>
                            </article>
                            @endforeach
                        </div>
                    </section>

                    {{-- Customer-visible status history --}}
                    <section
                        class="order-detail-card order-detail-section"
                        aria-labelledby="status-history-heading">
                        <p class="order-detail-overview-label">
                            Restaurant updates
                        </p>

                        <h2
                            id="status-history-heading"
                            class="order-detail-section-title mt-3">
                            Status history
                        </h2>

                        <ol class="order-detail-timeline">
                            @forelse (
                            $order->statusHistories->reverse()
                            as $history
                            )
                            <li
                                class="order-detail-timeline__item"
                                wire:key="order-history-{{ $history->id }}">
                                <div
                                    class="flex flex-col gap-1
                                            sm:flex-row sm:items-start
                                            sm:justify-between sm:gap-6">
                                    <p class="font-semibold text-ink">
                                        {{ $history->new_status->label() }}
                                    </p>

                                    <time
                                        datetime="{{ $history->created_at->toIso8601String() }}"
                                        class="shrink-0 text-xs
                                                text-muted">
                                        {{ $history->created_at->format(
                                                'M j, Y \a\t g:i A',
                                            ) }}
                                    </time>
                                </div>

                                @if (filled($history->public_note))
                                <p
                                    class="mt-2 max-w-2xl text-sm
                                                leading-7 text-muted">
                                    {{ $history->public_note }}
                                </p>
                                @endif
                            </li>
                            @empty
                            <li class="text-sm text-muted">
                                No restaurant updates are available yet.
                            </li>
                            @endforelse
                        </ol>
                    </section>
                </div>

                <aside
                    class="order-detail-aside"
                    aria-label="Order summary and customer details">
                    {{-- Financial snapshot --}}
                    <section
                        class="order-detail-card order-detail-section
                            order-detail-summary"
                        aria-labelledby="order-summary-heading">
                        <p
                            class="text-[0.66rem] font-bold uppercase
                                tracking-[0.18em] text-sun">
                            Order summary
                        </p>

                        <h2
                            id="order-summary-heading"
                            class="mt-3 font-display text-3xl text-white">
                            Total paid or due
                        </h2>

                        <dl class="mt-6 space-y-3">
                            <div class="order-detail-summary__row">
                                <dt>Subtotal</dt>

                                <dd>
                                    {{ \App\Support\Money::formatUsd(
                                        $order->subtotal_cents,
                                    ) }}
                                </dd>
                            </div>

                            @if ($order->discount_cents > 0)
                            <div class="order-detail-summary__row">
                                <dt>
                                    Discount
                                    @if (filled($order->coupon_code))
                                    <span
                                        class="ml-1 text-xs
                                                    text-white/50">
                                        ({{ $order->coupon_code }})
                                    </span>
                                    @endif
                                </dt>

                                <dd>
                                    −{{ \App\Support\Money::formatUsd(
                                            $order->discount_cents,
                                        ) }}
                                </dd>
                            </div>
                            @endif

                            <div class="order-detail-summary__row">
                                <dt>Tax</dt>

                                <dd>
                                    {{ \App\Support\Money::formatUsd(
                                        $order->tax_cents,
                                    ) }}
                                </dd>
                            </div>

                            @if ($order->delivery_cents > 0)
                            <div class="order-detail-summary__row">
                                <dt>Delivery</dt>

                                <dd>
                                    {{ \App\Support\Money::formatUsd(
                                            $order->delivery_cents,
                                        ) }}
                                </dd>
                            </div>
                            @endif

                            <div
                                class="order-detail-summary__row
                                    order-detail-summary__total">
                                <dt class="font-display text-2xl text-white">
                                    Total
                                </dt>

                                <dd class="text-xl">
                                    {{ $order->formattedGrandTotal() }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    {{-- Pickup or delivery information --}}
                    <section
                        class="order-detail-card order-detail-section"
                        aria-labelledby="fulfillment-heading">
                        <p class="order-detail-overview-label">
                            Fulfillment
                        </p>

                        <h2
                            id="fulfillment-heading"
                            class="mt-3 font-display text-2xl
                                text-primary-deep">
                            {{ $order->fulfillment_method->label() }}
                        </h2>

                        @if ($isDelivery && $deliveryAddress)
                        <address
                            class="mt-5 not-italic text-sm leading-7
                                    text-muted">
                            <span class="font-semibold text-ink">
                                {{ $deliveryAddress->recipient_name }}
                            </span>
                            <br>

                            {{ $deliveryAddress->street_address }}

                            @if (
                            filled(
                            $deliveryAddress->apartment_or_unit,
                            )
                            )
                            <br>
                            {{ $deliveryAddress->apartment_or_unit }}
                            @endif

                            <br>

                            {{ $deliveryAddress->city }},
                            {{ $deliveryAddress->state }}
                            {{ $deliveryAddress->postal_code }}
                        </address>

                        @if (
                        filled(
                        $deliveryAddress
                        ->delivery_instructions,
                        )
                        )
                        <div
                            class="mt-5 rounded-xl
                                        bg-surface-soft p-4">
                            <p
                                class="text-[0.65rem] font-bold
                                            uppercase tracking-[0.14em]
                                            text-muted">
                                Delivery instructions
                            </p>

                            <p
                                class="mt-2 text-sm leading-6
                                            text-ink">
                                {{ $deliveryAddress
                                            ->delivery_instructions }}
                            </p>
                        </div>
                        @endif
                        @else
                        <p class="mt-4 text-sm leading-7 text-muted">
                            Collect this order directly from the restaurant.
                            Wait until its status is
                            <strong class="text-ink">
                                Ready for Pickup
                            </strong>
                            before arriving.
                        </p>
                        @endif
                    </section>

                    {{-- Secondary information remains available on demand --}}
                    <details
                        class="order-detail-card order-detail-disclosure">
                        <summary>
                            <span>Customer and payment details</span>

                            <svg
                                aria-hidden="true"
                                viewBox="0 0 24 24"
                                class="order-detail-disclosure__chevron
                                    size-5 fill-none stroke-current"
                                stroke-width="1.8">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>

                        <div class="order-detail-disclosure__content">
                            <dl class="space-y-4 text-sm">
                                <div>
                                    <dt class="text-muted">
                                        Customer
                                    </dt>

                                    <dd class="mt-1 font-semibold text-ink">
                                        {{ $order->customer_name }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-muted">
                                        Email
                                    </dt>

                                    <dd class="mt-1 break-all">
                                        <a
                                            href="mailto:{{ $order->customer_email }}"
                                            class="font-semibold text-primary
                                                transition hover:text-coral">
                                            {{ $order->customer_email }}
                                        </a>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-muted">
                                        Phone
                                    </dt>

                                    <dd class="mt-1">
                                        <a
                                            href="tel:{{ preg_replace(
                                                '/[^0-9+]/',
                                                '',
                                                $order->customer_phone,
                                            ) }}"
                                            class="font-semibold text-primary
                                                transition hover:text-coral">
                                            {{ $order->customer_phone }}
                                        </a>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-muted">
                                        Payment method
                                    </dt>

                                    <dd class="mt-1 font-semibold text-ink">
                                        {{ $order->payment_method->label() }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </details>

                    @if (filled($order->customer_note))
                    <section
                        class="order-detail-card order-detail-section"
                        aria-labelledby="order-note-heading">
                        <p class="order-detail-overview-label">
                            Your note
                        </p>

                        <h2
                            id="order-note-heading"
                            class="mt-3 font-display text-2xl
                                    text-primary-deep">
                            Order instructions
                        </h2>

                        <p class="mt-4 text-sm leading-7 text-muted">
                            {{ $order->customer_note }}
                        </p>
                    </section>
                    @endif

                    <section
                        class="order-detail-card order-detail-section
                            order-detail-help"
                        aria-labelledby="order-help-heading">
                        <p class="order-detail-overview-label">
                            Need assistance?
                        </p>

                        <h2
                            id="order-help-heading"
                            class="mt-3 font-display text-2xl
                                text-primary-deep">
                            We are here to help.
                        </h2>

                        <p class="mt-3 text-sm leading-7 text-muted">
                            Include your order number when contacting the
                            restaurant so the team can assist you quickly.
                        </p>

                        <a
                            href="{{ route('contact.create') }}"
                            class="public-button-secondary mt-5 w-full
                                text-primary">
                            Contact the restaurant
                        </a>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</div>