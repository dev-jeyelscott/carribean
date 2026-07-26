<x-layouts.public
    title="Account profile"
    description="Update your Coast & Cay customer details.">
    <section class="bg-brand-sand-soft pb-20 pt-32 sm:pt-36">
        <div class="public-container">
            <div class="max-w-3xl">
                <p class="public-eyebrow">Customer account</p>

                <h1
                    class="mt-4 font-display text-4xl
                        text-brand-palm-dark sm:text-5xl">
                    Profile details
                </h1>

                <p class="mt-5 leading-8 text-brand-muted">
                    Keep your contact details accurate so checkout and order
                    notifications are easier to complete.
                </p>
            </div>

            <div class="mt-10">
                <x-account.navigation />
            </div>

            <div
                class="mt-8 max-w-3xl rounded-[2rem]
                    border border-brand-palm/10 bg-white p-6
                    shadow-island sm:p-8">
                <livewire:account.profile />
            </div>
        </div>
    </section>
</x-layouts.public>
