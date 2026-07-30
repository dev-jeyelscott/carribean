<x-layouts.public
    title="Account profile"
    description="Update your Coast & Cay customer details.">
    @php
        $user = auth()->user();
        $isVerified = $user->hasVerifiedEmail();
        $hasCheckoutDefaults = filled($user->name)
            && filled($user->email)
            && filled($user->phone);
    @endphp

    <section
        class="relative isolate overflow-hidden bg-surface-soft
            pb-24 pt-14 sm:pt-18 lg:pb-32 lg:pt-20">
        <div
            class="pointer-events-none absolute -left-32 top-40 -z-10
                size-80 rounded-full bg-ocean/10 blur-3xl"
            aria-hidden="true">
        </div>

        <div
            class="pointer-events-none absolute -right-24 top-10 -z-10
                size-72 rounded-full bg-coral/10 blur-3xl"
            aria-hidden="true">
        </div>

        <div class="public-container">
            <div
                data-reveal
                class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr]
                    lg:items-stretch">
                <div
                    class="flex flex-col justify-center rounded-[2.25rem]
                        border border-line bg-surface/85 p-7 shadow-card
                        backdrop-blur-sm sm:p-10 lg:p-12">
                    <p class="public-eyebrow">
                        Customer profile
                    </p>

                    <h1
                        class="mt-5 max-w-3xl font-display text-5xl
                            leading-[0.98] text-ink sm:text-6xl">
                        Your details,
                        <span class="text-primary">
                            ready when you are.
                        </span>
                    </h1>

                    <p
                        class="mt-6 max-w-2xl text-base leading-8
                            text-muted sm:text-lg">
                        Keep the contact information used during checkout and
                        restaurant order updates accurate.
                    </p>
                </div>

                <aside
                    aria-label="Customer identity"
                    class="relative overflow-hidden rounded-[2.25rem]
                        bg-primary-deep p-7 text-canvas shadow-elevated
                        sm:p-10">
                    <div
                        class="pointer-events-none absolute -right-20
                            -top-20 size-64 rounded-full bg-white/[0.05]"
                        aria-hidden="true">
                    </div>

                    <div class="relative">
                        <div
                            class="flex size-20 items-center justify-center
                                rounded-full border border-white/20
                                bg-white/10 font-display text-3xl text-white">
                            {{ $user->initials() }}
                        </div>

                        <h2
                            class="mt-6 font-display text-3xl text-white">
                            {{ $user->name }}
                        </h2>

                        <p
                            class="mt-2 break-all text-sm leading-6
                                text-white/65">
                            {{ $user->email }}
                        </p>

                        <div
                            class="mt-8 flex flex-wrap gap-3
                                border-t border-white/15 pt-7">
                            <span
                                @class([
                                    'inline-flex items-center gap-2',
                                    'rounded-full px-3 py-1.5 text-sm',
                                    'font-semibold',
                                    'bg-white/10 text-white' => $isVerified,
                                    'bg-coral/20 text-canvas' => ! $isVerified,
                                ])>
                                <span
                                    @class([
                                        'size-2 rounded-full',
                                        'bg-sun' => $isVerified,
                                        'bg-coral' => ! $isVerified,
                                    ])
                                    aria-hidden="true">
                                </span>

                                {{ $isVerified
                                    ? 'Email verified'
                                    : 'Verification pending' }}
                            </span>

                            <span
                                @class([
                                    'inline-flex items-center rounded-full',
                                    'px-3 py-1.5 text-sm font-semibold',
                                    'bg-ocean/30 text-white' =>
                                        $hasCheckoutDefaults,
                                    'bg-white/10 text-white/75' =>
                                        ! $hasCheckoutDefaults,
                                ])>
                                {{ $hasCheckoutDefaults
                                    ? 'Checkout-ready'
                                    : 'Profile incomplete' }}
                            </span>
                        </div>
                    </div>
                </aside>
            </div>

            <div data-reveal class="mt-8">
                <x-account.navigation />
            </div>

            <div
                class="mt-8 grid gap-8 lg:grid-cols-[0.72fr_1.28fr]
                    lg:items-start">
                <aside
                    data-reveal
                    aria-labelledby="profile-summary-heading"
                    class="rounded-[2rem] border border-line bg-surface
                        p-6 shadow-card sm:p-8 lg:sticky lg:top-28">
                    <p class="public-eyebrow">
                        Checkout defaults
                    </p>

                    <h2
                        id="profile-summary-heading"
                        class="mt-3 font-display text-3xl text-ink">
                        Account snapshot
                    </h2>

                    <p class="mt-4 leading-7 text-muted">
                        These details prefill checkout and help the restaurant
                        contact you about an order.
                    </p>

                    <dl
                        class="mt-7 divide-y divide-line border-y
                            border-line">
                        <div class="py-5">
                            <dt
                                class="text-xs font-semibold uppercase
                                    tracking-[0.16em] text-muted">
                                Current email
                            </dt>

                            <dd
                                class="mt-2 break-all font-semibold
                                    text-ink">
                                {{ $user->email }}
                            </dd>
                        </div>

                        <div class="py-5">
                            <dt
                                class="text-xs font-semibold uppercase
                                    tracking-[0.16em] text-muted">
                                Current phone
                            </dt>

                            <dd class="mt-2 font-semibold text-ink">
                                {{ $user->phone ?: 'Not provided' }}
                            </dd>
                        </div>

                        <div class="py-5">
                            <dt
                                class="text-xs font-semibold uppercase
                                    tracking-[0.16em] text-muted">
                                Member since
                            </dt>

                            <dd class="mt-2 font-semibold text-ink">
                                {{ $user->created_at?->format('F Y')
                                    ?? 'Recently' }}
                            </dd>
                        </div>
                    </dl>

                    <div
                        class="mt-7 rounded-[1.25rem] bg-primary/[0.07]
                            p-5">
                        <p class="text-sm font-semibold text-primary-deep">
                            Privacy note
                        </p>

                        <p class="mt-2 text-sm leading-6 text-muted">
                            Profile details are used for account access,
                            checkout defaults, and restaurant order
                            communication.
                        </p>
                    </div>
                </aside>

                <section
                    data-reveal
                    aria-labelledby="contact-information-heading"
                    class="rounded-[2rem] border border-line bg-surface
                        p-6 shadow-panel sm:p-8 lg:p-10">
                    <div
                        class="border-b border-line pb-6">
                        <p class="public-eyebrow">
                            Profile details
                        </p>

                        <h2
                            id="contact-information-heading"
                            class="mt-3 font-display text-4xl text-ink">
                            Contact information
                        </h2>

                        <p class="mt-3 max-w-2xl leading-7 text-muted">
                            Changes are applied to future checkout forms.
                            Existing orders retain their original customer
                            snapshots.
                        </p>
                    </div>

                    <div class="mt-8">
                        <livewire:account.profile />
                    </div>
                </section>
            </div>
        </div>
    </section>
</x-layouts.public>
