<section
    id="account-orders"
    class="bg-brand-sand-soft pb-24 pt-32 sm:pt-36 lg:pb-32">
    <div class="public-container">
        <div class="max-w-3xl">
            <p class="public-eyebrow">
                Customer account
            </p>

            <h1
                class="mt-4 font-display text-4xl leading-tight
                    text-brand-palm-dark sm:text-5xl">
                Your orders
            </h1>

            <p
                class="mt-5 max-w-2xl text-base leading-8
                    text-brand-muted">
                Review current orders, previous purchases, payment details,
                and restaurant fulfillment updates.
            </p>
        </div>

        <div class="mt-10">
            <x-account.navigation />
        </div>

        @if ($this->orders->isEmpty())
            <div
                class="mt-8 rounded-[2rem] border border-dashed
                    border-brand-palm/25 bg-white/70 px-6 py-16
                    text-center">
                <p class="public-eyebrow">
                    Nothing here yet
                </p>

                <h2
                    class="mt-4 font-display text-3xl
                        text-brand-palm-dark">
                    Your first order is still ahead.
                </h2>

                <p
                    class="mx-auto mt-4 max-w-xl leading-7
                        text-brand-muted">
                    Once you place an order while signed in, its payment
                    and fulfillment progress will appear here.
                </p>

                <a
                    href="{{ route('menu') }}"
                    class="public-button-primary mt-7">
                    Explore the menu
                </a>
            </div>
        @else
            <div class="mt-8 grid gap-5">
                @foreach ($this->orders as $order)
                    <article
                        wire:key="customer-order-{{ $order->id }}"
                        class="rounded-[2rem] border border-brand-palm/10
                            bg-white p-6 shadow-island sm:p-8">
                        <div
                            class="flex flex-col gap-6 lg:flex-row
                                lg:items-center lg:justify-between">
                            <div>
                                <div
                                    class="flex flex-wrap items-center
                                        gap-3">
                                    <p
                                        class="font-display text-2xl
                                            text-brand-forest">
                                        {{ $order->order_number }}
                                    </p>

                                    <span
                                        class="rounded-full
                                            bg-brand-sand-soft px-3 py-1
                                            text-xs font-semibold uppercase
                                            tracking-[0.12em]
                                            text-brand-palm-dark">
                                        {{ $order->status->label() }}
                                    </span>
                                </div>

                                <p
                                    class="mt-3 text-sm leading-6
                                        text-brand-muted">
                                    Placed
                                    <time
                                        datetime="{{ $order->placed_at->toIso8601String() }}">
                                        {{ $order->placed_at->format(
                                            'M j, Y \a\t g:i A',
                                        ) }}
                                    </time>
                                </p>

                                <dl
                                    class="mt-5 flex flex-wrap gap-x-8
                                        gap-y-3 text-sm">
                                    <div>
                                        <dt class="text-brand-muted">
                                            Fulfillment
                                        </dt>

                                        <dd
                                            class="mt-1 font-semibold
                                                text-brand-forest">
                                            {{ $order->fulfillment_method->label() }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-brand-muted">
                                            Payment
                                        </dt>

                                        <dd
                                            class="mt-1 font-semibold
                                                text-brand-forest">
                                            {{ $order->payment_status->label() }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-brand-muted">
                                            Items
                                        </dt>

                                        <dd
                                            class="mt-1 font-semibold
                                                text-brand-forest">
                                            {{ $order->items_count }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div
                                class="flex flex-col items-start gap-4
                                    sm:flex-row sm:items-center
                                    lg:flex-col lg:items-end">
                                <p
                                    class="font-display text-3xl
                                        text-brand-forest">
                                    {{ $order->formattedGrandTotal() }}
                                </p>

                                <a
                                    href="{{ route(
                                        'account.orders.show',
                                        ['order' => $order],
                                    ) }}"
                                    class="public-button-primary">
                                    View order
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($this->orders->hasPages())
                <div class="mt-10">
                    {{ $this->orders->links(
                        data: [
                            'scrollTo' => '#account-orders',
                        ],
                    ) }}
                </div>
            @endif
        @endif
    </div>
</section>
