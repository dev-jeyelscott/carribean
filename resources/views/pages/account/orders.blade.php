<x-layouts.public
    title="My orders"
    description="View your Coast & Cay restaurant orders.">
    <section class="bg-brand-sand-soft pb-20 pt-32 sm:pt-36">
        <div class="public-container">
            <div class="max-w-3xl">
                <p class="public-eyebrow">Customer account</p>

                <h1
                    class="mt-4 font-display text-4xl
                        text-brand-palm-dark sm:text-5xl">
                    Your orders
                </h1>

                <p class="mt-5 leading-8 text-brand-muted">
                    Order history will be connected here after the transactional
                    order domain is implemented in Phase 6.
                </p>
            </div>

            <div class="mt-10">
                <x-account.navigation />
            </div>

            <div
                class="mt-8 rounded-[2rem] border border-dashed
                    border-brand-palm/25 bg-white/70 px-6 py-16
                    text-center">
                <p class="public-eyebrow">Nothing here yet</p>

                <h2
                    class="mt-4 font-display text-3xl
                        text-brand-palm-dark">
                    Your first order is still ahead.
                </h2>

                <p
                    class="mx-auto mt-4 max-w-xl leading-7
                        text-brand-muted">
                    This page is intentionally an account shell only. It will
                    receive real order data once checkout and order snapshots
                    are implemented.
                </p>

                <a href="{{ route('menu') }}" class="public-button-primary mt-7">
                    Explore the menu
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
