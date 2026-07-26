<x-layouts.public
    title="Order Received"
    description="Your Coast & Cay order has been received.">
    <section class="bg-brand-cream pb-24 pt-32 sm:pt-40 lg:pb-32">
        <div class="public-container max-w-4xl">
            <div
                class="rounded-island border border-brand-palm/10
                    bg-white p-7 shadow-island sm:p-10">
                <p class="public-eyebrow">Order received</p>

                <h1
                    class="mt-4 font-display text-5xl
                        text-brand-forest sm:text-6xl">
                    Thank You
                </h1>

                <p class="mt-5 max-w-2xl leading-8 text-brand-muted">
                    Your order was received and is waiting for restaurant
                    confirmation. Keep your order number for reference.
                </p>

                <dl
                    class="mt-8 grid gap-5 rounded-island
                        bg-brand-sand-soft p-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-brand-muted">
                            Order number
                        </dt>

                        <dd
                            class="mt-1 font-semibold
                                text-brand-forest">
                            {{ $order->order_number }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-brand-muted">
                            Status
                        </dt>

                        <dd
                            class="mt-1 font-semibold
                                text-brand-forest">
                            {{ $order->status->label() }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-brand-muted">
                            Fulfillment
                        </dt>

                        <dd
                            class="mt-1 font-semibold
                                text-brand-forest">
                            {{ $order->fulfillment_method->label() }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-brand-muted">
                            Payment
                        </dt>

                        <dd
                            class="mt-1 font-semibold
                                text-brand-forest">
                            {{ $order->payment_method->label() }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-9">
                    <h2
                        class="font-display text-3xl
                            text-brand-forest">
                        Order Items
                    </h2>

                    <div class="mt-5 divide-y divide-brand-palm/10">
                        @foreach ($order->items as $item)
                            <article class="py-5 first:pt-0">
                                <div
                                    class="flex items-start
                                        justify-between gap-5">
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
                                            <p
                                                class="mt-1 text-sm
                                                    text-brand-muted">
                                                {{ $option['group_name'] }}:
                                                {{ $option['name'] }}
                                            </p>
                                        @endforeach
                                    </div>

                                    <p
                                        class="font-semibold
                                            text-brand-forest">
                                        {{ \App\Support\Money::formatUsd(
                                            $item->line_total_cents,
                                        ) }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div
                    class="mt-7 flex items-center justify-between
                        border-t border-brand-palm/10 pt-6">
                    <span
                        class="font-display text-2xl
                            text-brand-forest">
                        Total
                    </span>

                    <span
                        class="text-2xl font-semibold
                            text-brand-forest">
                        {{ $order->formattedGrandTotal() }}
                    </span>
                </div>

                <div class="mt-9 flex flex-wrap gap-3">
                    @auth
                        <a
                            href="{{ route('account.orders.index') }}"
                            class="public-button-primary">
                            View My Orders
                        </a>
                    @endauth

                    <a
                        href="{{ route('menu') }}"
                        class="public-button-secondary
                            text-brand-palm">
                        Return to Menu
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
