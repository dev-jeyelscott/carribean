<x-layouts.public
    title="Payment Incomplete"
    description="Retry payment for your Coast & Cay order.">
    <section class="bg-brand-cream pb-24 pt-32 sm:pt-40 lg:pb-32">
        <div class="public-container max-w-3xl">
            <div
                class="rounded-island border border-brand-palm/10
                    bg-white p-7 shadow-island sm:p-10">
                <p class="public-eyebrow">Payment incomplete</p>

                <h1
                    class="mt-4 font-display text-5xl
                        text-brand-forest sm:text-6xl">
                    Your Order Is Saved
                </h1>

                <p class="mt-5 max-w-2xl leading-8 text-brand-muted">
                    Stripe did not confirm payment for this order. No second
                    order will be created when you retry.
                </p>

                @if (session('stripe_error'))
                    <x-public.alert type="error" class="mt-7">
                        {{ session('stripe_error') }}
                    </x-public.alert>
                @endif

                <dl
                    class="mt-8 grid gap-5 rounded-island
                        bg-brand-sand-soft p-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-brand-muted">
                            Order number
                        </dt>

                        <dd class="mt-1 font-semibold text-brand-forest">
                            {{ $order->order_number }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-brand-muted">
                            Total
                        </dt>

                        <dd class="mt-1 font-semibold text-brand-forest">
                            {{ $order->formattedGrandTotal() }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-brand-muted">
                            Order status
                        </dt>

                        <dd class="mt-1 font-semibold text-brand-forest">
                            {{ $order->status->label() }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-brand-muted">
                            Payment status
                        </dt>

                        <dd class="mt-1 font-semibold text-brand-forest">
                            {{ $order->payment_status->label() }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-9 flex flex-wrap gap-3">
                    <form method="POST" action="{{ $retryUrl }}">
                        @csrf

                        <button
                            type="submit"
                            class="public-button-primary">
                            Retry Secure Payment
                        </button>
                    </form>

                    <a
                        href="{{ route('menu') }}"
                        class="public-button-secondary text-brand-palm">
                        Return to Menu
                    </a>
                </div>

                <p class="mt-6 text-sm leading-6 text-brand-muted">
                    Your restaurant order remains pending until Stripe confirms
                    payment and restaurant staff accepts the order.
                </p>
            </div>
        </div>
    </section>
</x-layouts.public>
