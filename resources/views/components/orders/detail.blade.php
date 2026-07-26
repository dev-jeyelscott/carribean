@props([
    'order',
    'showAccountNavigation' => false,
    'backUrl' => '#',
    'backLabel' => 'Go back',
    'canConfirmReceived' => false,
])

@php
$deliveryAddress = $order->addresses->firstWhere(
    'type',
    'delivery',
);
@endphp

<section class="bg-brand-cream pb-24 pt-32 sm:pt-36 lg:pb-32">
    <div class="public-container max-w-6xl">
        <div class="max-w-3xl">
            <p class="public-eyebrow">
                Order tracking
            </p>

            <h1
                class="mt-4 font-display text-4xl leading-tight
                    text-brand-palm-dark sm:text-5xl">
                {{ $order->order_number }}
            </h1>

            <p class="mt-5 leading-8 text-brand-muted">
                Review your order details and the latest restaurant updates.
            </p>
        </div>

        @if ($showAccountNavigation)
            <div class="mt-10">
                <x-account.navigation />
            </div>
        @endif

        @if (session()->has('order_status'))
            <div
                role="status"
                aria-live="polite"
                class="mt-8 rounded-2xl border border-brand-palm/20
                    bg-brand-palm/10 px-5 py-4 font-semibold
                    text-brand-palm-dark">
                {{ session('order_status') }}
            </div>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-[1.45fr_0.75fr]">
            <div class="space-y-6">
                <article
                    class="rounded-[2rem] border border-brand-palm/10
                        bg-white p-6 shadow-island sm:p-8">
                    <div
                        class="flex flex-col gap-5 sm:flex-row
                            sm:items-start sm:justify-between">
                        <div>
                            <p class="public-eyebrow">
                                Current status
                            </p>

                            <h2
                                class="mt-3 font-display text-3xl
                                    text-brand-forest">
                                {{ $order->status->label() }}
                            </h2>

                            <p class="mt-3 text-sm text-brand-muted">
                                Last refreshed
                                <time datetime="{{ now()->toIso8601String() }}">
                                    {{ now()->format('M j, Y \a\t g:i A') }}
                                </time>
                            </p>
                        </div>

                        <div
                            class="rounded-2xl bg-brand-sand-soft
                                px-5 py-4">
                            <p
                                class="text-xs font-semibold uppercase
                                    tracking-[0.16em] text-brand-muted">
                                Payment
                            </p>

                            <p
                                class="mt-2 font-semibold
                                    text-brand-forest">
                                {{ $order->payment_status->label() }}
                            </p>
                        </div>
                    </div>

                    @if ($canConfirmReceived)
                        <div
                            class="mt-7 border-t border-brand-palm/10
                                pt-6">
                            <p class="leading-7 text-brand-muted">
                                Your order has been fulfilled. Confirm receipt
                                to mark it complete now.
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
                    class="rounded-[2rem] border border-brand-palm/10
                        bg-white p-6 shadow-island sm:p-8">
                    <p class="public-eyebrow">
                        Order items
                    </p>

                    <h2
                        class="mt-3 font-display text-3xl
                            text-brand-forest">
                        What you ordered
                    </h2>

                    <div
                        class="mt-6 divide-y divide-brand-palm/10">
                        @foreach ($order->items as $item)
                            <div
                                class="flex items-start justify-between
                                    gap-5 py-5 first:pt-0
                                    last:pb-0">
                                <div>
                                    <p
                                        class="font-semibold
                                            text-brand-forest">
                                        {{ $item->quantity }} ×
                                        {{ $item->name }}
                                    </p>

                                    @foreach (
                                        $item->selected_options ?? []
                                        as $option
                                    )
                                        @if (
                                            is_array($option)
                                            && filled(
                                                $option['name'] ?? null,
                                            )
                                        )
                                            <p
                                                class="mt-1 text-sm
                                                    text-brand-muted">
                                                {{ $option['group_name']
                                                    ?? 'Option' }}:
                                                {{ $option['name'] }}
                                            </p>
                                        @endif
                                    @endforeach
                                </div>

                                <p
                                    class="shrink-0 font-semibold
                                        text-brand-forest">
                                    {{ \App\Support\Money::formatUsd(
                                        $item->line_total_cents,
                                    ) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article
                    class="rounded-[2rem] border border-brand-palm/10
                        bg-white p-6 shadow-island sm:p-8">
                    <p class="public-eyebrow">
                        Progress
                    </p>

                    <h2
                        class="mt-3 font-display text-3xl
                            text-brand-forest">
                        Status history
                    </h2>

                    <ol class="mt-7 space-y-6">
                        @forelse ($order->statusHistories as $history)
                            <li
                                class="relative border-l-2
                                    border-brand-palm/20 pl-6"
                                wire:key="order-history-{{ $history->id }}">
                                <span
                                    aria-hidden="true"
                                    class="absolute -left-[0.45rem]
                                        top-1 h-3 w-3 rounded-full
                                        bg-brand-coral">
                                </span>

                                <div
                                    class="flex flex-col gap-1
                                        sm:flex-row
                                        sm:items-center
                                        sm:justify-between">
                                    <p
                                        class="font-semibold
                                            text-brand-forest">
                                        {{ $history->new_status->label() }}
                                    </p>

                                    <time
                                        datetime="{{ $history->created_at->toIso8601String() }}"
                                        class="text-sm text-brand-muted">
                                        {{ $history->created_at->format(
                                            'M j, Y \a\t g:i A',
                                        ) }}
                                    </time>
                                </div>

                                @if (filled($history->public_note))
                                    <p
                                        class="mt-2 leading-7
                                            text-brand-muted">
                                        {{ $history->public_note }}
                                    </p>
                                @endif
                            </li>
                        @empty
                            <li class="text-brand-muted">
                                No status updates are available yet.
                            </li>
                        @endforelse
                    </ol>
                </article>
            </div>

            <aside class="space-y-6">
                <article
                    class="rounded-[2rem] border border-brand-palm/10
                        bg-brand-palm-dark p-6 text-brand-cream
                        shadow-island-dark sm:p-8">
                    <p
                        class="text-xs font-semibold uppercase
                            tracking-[0.2em] text-brand-sun">
                        Order summary
                    </p>

                    <dl class="mt-6 space-y-4">
                        <div class="flex justify-between gap-4">
                            <dt class="text-brand-cream/70">
                                Subtotal
                            </dt>

                            <dd class="font-semibold">
                                {{ \App\Support\Money::formatUsd(
                                    $order->subtotal_cents,
                                ) }}
                            </dd>
                        </div>

                        @if ($order->discount_cents > 0)
                            <div class="flex justify-between gap-4">
                                <dt class="text-brand-cream/70">
                                    Discount
                                </dt>

                                <dd class="font-semibold">
                                    −{{ \App\Support\Money::formatUsd(
                                        $order->discount_cents,
                                    ) }}
                                </dd>
                            </div>
                        @endif

                        <div class="flex justify-between gap-4">
                            <dt class="text-brand-cream/70">
                                Tax
                            </dt>

                            <dd class="font-semibold">
                                {{ \App\Support\Money::formatUsd(
                                    $order->tax_cents,
                                ) }}
                            </dd>
                        </div>

                        @if ($order->delivery_cents > 0)
                            <div class="flex justify-between gap-4">
                                <dt class="text-brand-cream/70">
                                    Delivery
                                </dt>

                                <dd class="font-semibold">
                                    {{ \App\Support\Money::formatUsd(
                                        $order->delivery_cents,
                                    ) }}
                                </dd>
                            </div>
                        @endif

                        <div
                            class="flex justify-between gap-4
                                border-t border-brand-cream/15 pt-5">
                            <dt class="font-display text-2xl">
                                Total
                            </dt>

                            <dd class="text-xl font-semibold">
                                {{ $order->formattedGrandTotal() }}
                            </dd>
                        </div>
                    </dl>
                </article>

                <article
                    class="rounded-[2rem] border border-brand-palm/10
                        bg-white p-6 shadow-island sm:p-8">
                    <p class="public-eyebrow">
                        Fulfillment
                    </p>

                    <h2
                        class="mt-3 font-display text-2xl
                            text-brand-forest">
                        {{ $order->fulfillment_method->label() }}
                    </h2>

                    @if (
                        $order->fulfillment_method
                            === \App\Enums\FulfillmentMethod::Delivery
                        && $deliveryAddress
                    )
                        <address
                            class="mt-5 not-italic leading-7
                                text-brand-muted">
                            <span
                                class="font-semibold
                                    text-brand-forest">
                                {{ $deliveryAddress->recipient_name }}
                            </span>
                            <br>

                            {{ $deliveryAddress->street_address }}

                            @if (filled($deliveryAddress->apartment_or_unit))
                                <br>
                                {{ $deliveryAddress->apartment_or_unit }}
                            @endif

                            <br>

                            {{ $deliveryAddress->city }},
                            {{ $deliveryAddress->state }}
                            {{ $deliveryAddress->postal_code }}
                            <br>

                            {{ $deliveryAddress->phone }}
                        </address>

                        @if (
                            filled(
                                $deliveryAddress
                                    ->delivery_instructions,
                            )
                        )
                            <div
                                class="mt-5 rounded-2xl
                                    bg-brand-sand-soft p-4">
                                <p
                                    class="text-xs font-semibold
                                        uppercase tracking-[0.14em]
                                        text-brand-muted">
                                    Delivery instructions
                                </p>

                                <p
                                    class="mt-2 leading-7
                                        text-brand-forest">
                                    {{ $deliveryAddress
                                        ->delivery_instructions }}
                                </p>
                            </div>
                        @endif
                    @else
                        <p class="mt-4 leading-7 text-brand-muted">
                            This order will be collected directly from
                            the restaurant. Wait until its status is
                            Ready for Pickup before arriving.
                        </p>
                    @endif
                </article>

                <article
                    class="rounded-[2rem] border border-brand-palm/10
                        bg-white p-6 shadow-island sm:p-8">
                    <p class="public-eyebrow">
                        Customer
                    </p>

                    <dl class="mt-5 space-y-4 text-sm">
                        <div>
                            <dt class="text-brand-muted">
                                Name
                            </dt>

                            <dd
                                class="mt-1 font-semibold
                                    text-brand-forest">
                                {{ $order->customer_name }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-brand-muted">
                                Email
                            </dt>

                            <dd
                                class="mt-1 break-all font-semibold
                                    text-brand-forest">
                                {{ $order->customer_email }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-brand-muted">
                                Phone
                            </dt>

                            <dd
                                class="mt-1 font-semibold
                                    text-brand-forest">
                                {{ $order->customer_phone }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-brand-muted">
                                Payment method
                            </dt>

                            <dd
                                class="mt-1 font-semibold
                                    text-brand-forest">
                                {{ $order->payment_method->label() }}
                            </dd>
                        </div>
                    </dl>
                </article>

                @if (filled($order->customer_note))
                    <article
                        class="rounded-[2rem] border
                            border-brand-palm/10 bg-white p-6
                            shadow-island sm:p-8">
                        <p class="public-eyebrow">
                            Order note
                        </p>

                        <p class="mt-4 leading-7 text-brand-muted">
                            {{ $order->customer_note }}
                        </p>
                    </article>
                @endif

                <a
                    href="{{ $backUrl }}"
                    class="public-button-secondary
                        w-full text-brand-palm">
                    {{ $backLabel }}
                </a>
            </aside>
        </div>
    </div>
</section>
