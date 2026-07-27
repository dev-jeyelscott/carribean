<x-layouts.public
    title="My account"
    description="Manage your Coast & Cay customer account.">
    <section class="bg-brand-sand-soft pb-20 pt-32 sm:pt-36">
        <div class="public-container">
            <div class="max-w-3xl">
                <p class="public-eyebrow">
                    Customer account
                </p>

                <h1
                    class="mt-4 font-display text-4xl leading-tight
                        text-brand-palm-dark sm:text-5xl">
                    Welcome back, {{ auth()->user()->name }}.
                </h1>

                <p
                    class="mt-5 max-w-2xl text-base leading-8
                        text-brand-muted">
                    Manage your contact details and review your current
                    and previous restaurant orders.
                </p>
            </div>

            <div class="mt-10">
                <x-account.navigation />
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                <article
                    class="rounded-[2rem] border border-brand-palm/10
                        bg-white p-7 shadow-island sm:p-8">
                    <p class="public-eyebrow">
                        Profile
                    </p>

                    <h2
                        class="mt-3 font-display text-3xl
                            text-brand-palm-dark">
                        Keep checkout details ready
                    </h2>

                    <p class="mt-4 leading-7 text-brand-muted">
                        Keep your name, email address, and phone number
                        current for faster checkout and reliable order
                        notifications.
                    </p>

                    <a
                        href="{{ route('account.profile') }}"
                        class="public-button-primary mt-7">
                        Edit profile
                    </a>
                </article>

                <article
                    class="rounded-[2rem] border border-brand-palm/10
                        bg-brand-palm-dark p-7 text-brand-cream
                        shadow-island-dark sm:p-8">
                    <p
                        class="text-xs font-semibold uppercase
                            tracking-[0.28em] text-brand-sun">
                        Orders
                    </p>

                    <h2 class="mt-3 font-display text-3xl">
                        Order history
                    </h2>

                    <p class="mt-4 leading-7 text-brand-cream/70">
                        Review order totals, payment status, fulfillment
                        progress, and previous purchases from your secure
                        order history.
                    </p>

                    <a
                        href="{{ route('account.orders.index') }}"
                        class="mt-7 inline-flex min-h-12 items-center
                            justify-center rounded-full border
                            border-brand-cream/35 px-7 py-3 text-xs
                            font-semibold uppercase tracking-[0.18em]
                            transition hover:bg-brand-cream
                            hover:text-brand-palm-dark">
                        View orders
                    </a>
                </article>
            </div>
        </div>
    </section>
</x-layouts.public>
