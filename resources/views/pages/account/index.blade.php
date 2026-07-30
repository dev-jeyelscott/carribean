<x-layouts.public
    title="My account"
    description="Manage your Coast & Cay profile and restaurant orders.">
    @php
        $firstName = str($user->name)->before(' ');
        $isVerified = $user->hasVerifiedEmail();
    @endphp

    <section
        class="relative isolate overflow-hidden bg-surface-soft
            pb-24 pt-14 sm:pt-18 lg:pb-32 lg:pt-20">
        <div
            class="pointer-events-none absolute -left-32 top-28 -z-10
                size-80 rounded-full bg-coral/10 blur-3xl"
            aria-hidden="true">
        </div>

        <div
            class="pointer-events-none absolute -right-40 top-8 -z-10
                size-[30rem] rounded-full bg-ocean/10 blur-3xl"
            aria-hidden="true">
        </div>

        <div class="public-container">
            <div
                data-reveal
                class="grid gap-8 lg:grid-cols-[1.18fr_0.82fr]
                    lg:items-stretch">
                <div
                    class="flex flex-col justify-center rounded-[2.25rem]
                        border border-line bg-surface/80 p-7 shadow-card
                        backdrop-blur-sm sm:p-10 lg:p-12">
                    <p class="public-eyebrow">
                        Customer account
                    </p>

                    <h1
                        class="mt-5 max-w-3xl font-display text-5xl
                            leading-[0.98] text-ink sm:text-6xl lg:text-7xl">
                        Welcome back,
                        <span class="text-primary">
                            {{ $firstName }}.
                        </span>
                    </h1>

                    <p
                        class="mt-6 max-w-2xl text-base leading-8
                            text-muted sm:text-lg">
                        Keep checkout details ready, follow restaurant orders,
                        and return to the menu whenever the next craving arrives.
                    </p>

                    <div
                        class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ route('menu') }}"
                            class="public-button-primary">
                            Order something good
                        </a>

                        <a
                            href="{{ route('account.orders.index') }}"
                            class="public-button-secondary text-primary">
                            View order history
                        </a>
                    </div>
                </div>

                <aside
                    aria-labelledby="account-snapshot-heading"
                    class="relative overflow-hidden rounded-[2.25rem]
                        bg-primary-deep p-7 text-canvas shadow-elevated
                        sm:p-10 lg:p-12">
                    <div
                        class="pointer-events-none absolute -right-16
                            -top-20 size-64 rounded-full border
                            border-white/10 bg-white/[0.04]"
                        aria-hidden="true">
                    </div>

                    <div
                        class="pointer-events-none absolute -bottom-20
                            -left-16 size-56 rounded-full bg-coral/10
                            blur-2xl"
                        aria-hidden="true">
                    </div>

                    <div class="relative">
                        <div
                            class="flex flex-col gap-6 sm:flex-row
                                sm:items-center">
                            <div
                                class="flex size-20 shrink-0 items-center
                                    justify-center rounded-full border
                                    border-white/20 bg-white/10 font-display
                                    text-3xl text-white shadow-card">
                                {{ $user->initials() }}
                            </div>

                            <div class="min-w-0">
                                <p
                                    class="text-xs font-semibold uppercase
                                        tracking-[0.22em] text-sun">
                                    Account snapshot
                                </p>

                                <h2
                                    id="account-snapshot-heading"
                                    class="mt-2 truncate font-display
                                        text-3xl text-white">
                                    {{ $user->name }}
                                </h2>

                                <p class="mt-1 truncate text-sm text-white/65">
                                    {{ $user->email }}
                                </p>
                            </div>
                        </div>

                        <dl
                            class="mt-9 grid gap-4 border-t border-white/15
                                pt-7 sm:grid-cols-2">
                            <div>
                                <dt
                                    class="text-xs font-semibold uppercase
                                        tracking-[0.16em] text-white/50">
                                    Email status
                                </dt>

                                <dd class="mt-2">
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
                                            ? 'Verified'
                                            : 'Verification pending' }}
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt
                                    class="text-xs font-semibold uppercase
                                        tracking-[0.16em] text-white/50">
                                    Member since
                                </dt>

                                <dd class="mt-2 font-semibold text-white">
                                    {{ $user->created_at?->format('F Y')
                                        ?? 'Recently' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </aside>
            </div>

            <div data-reveal class="mt-8">
                <x-account.navigation />
            </div>

            <div
                class="mt-8 grid gap-8 lg:grid-cols-[1.2fr_0.8fr]
                    lg:items-start">
                <div class="space-y-8">
                    <div
                        data-reveal
                        class="grid gap-4 sm:grid-cols-3">
                        <x-account.summary-tile
                            label="Orders placed"
                            :value="$orderCount"
                            detail="Orders connected to this customer account." />

                        <x-account.summary-tile
                            label="Active orders"
                            :value="$activeOrderCount"
                            detail="Orders still moving through fulfillment."
                            tone="ocean" />

                        <x-account.summary-tile
                            label="Email status"
                            :value="$isVerified ? 'Verified' : 'Pending'"
                            :detail="$isVerified
                                ? 'Order updates can reach your verified inbox.'
                                : 'Verify your inbox when convenient.'"
                            tone="coral" />
                    </div>

                    <section
                        data-reveal
                        aria-labelledby="latest-order-heading"
                        class="overflow-hidden rounded-[2rem] border
                            border-line bg-surface shadow-panel">
                        <div
                            class="flex flex-col gap-5 border-b border-line
                                px-6 py-6 sm:flex-row sm:items-center
                                sm:justify-between sm:px-8">
                            <div>
                                <p class="public-eyebrow">
                                    Latest activity
                                </p>

                                <h2
                                    id="latest-order-heading"
                                    class="mt-2 font-display text-3xl
                                        text-ink">
                                    Most recent order
                                </h2>
                            </div>

                            @if ($latestOrder)
                                <span
                                    class="inline-flex w-fit rounded-full
                                        bg-primary/[0.09] px-4 py-2 text-xs
                                        font-semibold uppercase
                                        tracking-[0.14em] text-primary">
                                    {{ $latestOrder->status->label() }}
                                </span>
                            @endif
                        </div>

                        @if ($latestOrder)
                            <div class="p-6 sm:p-8">
                                <div
                                    class="grid gap-7 md:grid-cols-[1fr_auto]
                                        md:items-end">
                                    <div>
                                        <p
                                            class="font-display text-3xl
                                                text-ink">
                                            {{ $latestOrder->order_number }}
                                        </p>

                                        <p
                                            class="mt-3 text-sm leading-7
                                                text-muted">
                                            Placed
                                            <time
                                                datetime="{{ $latestOrder
                                                    ->placed_at
                                                    ->toIso8601String() }}">
                                                {{ $latestOrder
                                                    ->placed_at
                                                    ->format(
                                                        'M j, Y \a\t g:i A',
                                                    ) }}
                                            </time>
                                        </p>

                                        <dl
                                            class="mt-6 flex flex-wrap
                                                gap-x-10 gap-y-4 text-sm">
                                            <div>
                                                <dt class="text-muted">
                                                    Items
                                                </dt>

                                                <dd
                                                    class="mt-1 font-semibold
                                                        text-ink">
                                                    {{ $latestOrder->items_count }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-muted">
                                                    Fulfillment
                                                </dt>

                                                <dd
                                                    class="mt-1 font-semibold
                                                        text-ink">
                                                    {{ $latestOrder
                                                        ->fulfillment_method
                                                        ->label() }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-muted">
                                                    Payment
                                                </dt>

                                                <dd
                                                    class="mt-1 font-semibold
                                                        text-ink">
                                                    {{ $latestOrder
                                                        ->payment_status
                                                        ->label() }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div
                                        class="flex flex-col items-start
                                            gap-4 md:items-end">
                                        <p
                                            class="font-display text-4xl
                                                text-primary-deep">
                                            {{ $latestOrder
                                                ->formattedGrandTotal() }}
                                        </p>

                                        <a
                                            href="{{ route(
                                                'account.orders.show',
                                                ['order' => $latestOrder],
                                            ) }}"
                                            class="public-button-primary">
                                            View order
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div
                                class="grid gap-7 p-6 sm:p-8
                                    md:grid-cols-[1fr_auto]
                                    md:items-center">
                                <div>
                                    <h3
                                        class="font-display text-3xl
                                            text-ink">
                                        Your next island favorite is waiting.
                                    </h3>

                                    <p
                                        class="mt-3 max-w-2xl leading-7
                                            text-muted">
                                        Orders placed while signed in will
                                        appear here with payment and fulfillment
                                        updates.
                                    </p>
                                </div>

                                <a
                                    href="{{ route('menu') }}"
                                    class="public-button-primary">
                                    Explore the menu
                                </a>
                            </div>
                        @endif
                    </section>
                </div>

                <aside
                    data-reveal
                    aria-label="Account quick actions"
                    class="grid gap-5">
                    <a
                        href="{{ route('account.profile') }}"
                        class="group rounded-[2rem] border border-line
                            bg-surface p-7 shadow-card transition
                            duration-300 hover:-translate-y-1
                            hover:shadow-panel motion-reduce:transform-none
                            motion-reduce:transition-none sm:p-8">
                        <div
                            class="flex items-center justify-between gap-4">
                            <span
                                class="inline-flex size-12 items-center
                                    justify-center rounded-full
                                    bg-coral/[0.12] text-coral-deep">
                                <svg
                                    class="size-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.232 5.232 18.768 8.768
                                            M16.732 3.732a2.5 2.5 0 0 1
                                            3.536 3.536L7.5 20.036
                                            3 21l.964-4.5L16.732 3.732Z" />
                                </svg>
                            </span>

                            <span
                                class="text-2xl text-primary transition
                                    group-hover:translate-x-1
                                    motion-reduce:transform-none"
                                aria-hidden="true">
                                →
                            </span>
                        </div>

                        <p class="public-eyebrow mt-8">
                            Profile
                        </p>

                        <h2
                            class="mt-3 font-display text-3xl
                                text-primary-deep">
                            Keep checkout effortless
                        </h2>

                        <p class="mt-4 leading-7 text-muted">
                            Review the name, email address, and phone number used
                            as your checkout defaults.
                        </p>
                    </a>

                    <a
                        href="{{ route('account.orders.index') }}"
                        class="group relative overflow-hidden
                            rounded-[2rem] bg-primary-deep p-7 text-canvas
                            shadow-elevated transition duration-300
                            hover:-translate-y-1 motion-reduce:transform-none
                            motion-reduce:transition-none sm:p-8">
                        <div
                            class="pointer-events-none absolute -right-12
                                -top-16 size-48 rounded-full
                                border border-white/10 bg-white/[0.04]"
                            aria-hidden="true">
                        </div>

                        <div class="relative">
                            <div
                                class="flex items-center justify-between
                                    gap-4">
                                <span
                                    class="inline-flex size-12 items-center
                                        justify-center rounded-full
                                        bg-white/10 text-sun">
                                    <svg
                                        class="size-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6.75 3.75h10.5a1.5 1.5 0 0 1
                                                1.5 1.5v15l-3.375-2.25
                                                L12 20.25 8.625 18
                                                5.25 20.25v-15a1.5 1.5
                                                0 0 1 1.5-1.5Z" />
                                        <path
                                            stroke-linecap="round"
                                            d="M8.5 8h7M8.5 11.5h7" />
                                    </svg>
                                </span>

                                <span
                                    class="text-2xl text-white transition
                                        group-hover:translate-x-1
                                        motion-reduce:transform-none"
                                    aria-hidden="true">
                                    →
                                </span>
                            </div>

                            <p
                                class="mt-8 text-xs font-semibold uppercase
                                    tracking-[0.24em] text-sun">
                                Orders
                            </p>

                            <h2
                                class="mt-3 font-display text-3xl
                                    text-white">
                                Follow every meal
                            </h2>

                            <p class="mt-4 leading-7 text-white/68">
                                Review previous purchases and check the current
                                payment and fulfillment state of active orders.
                            </p>
                        </div>
                    </a>
                </aside>
            </div>
        </div>
    </section>
</x-layouts.public>
