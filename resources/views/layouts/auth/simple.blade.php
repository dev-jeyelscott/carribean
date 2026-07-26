<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>

    <body
        class="min-h-screen bg-brand-cream font-sans
            text-brand-forest antialiased">
        <main class="relative isolate min-h-svh overflow-hidden">
            <div
                class="pointer-events-none absolute -left-32 top-16 size-96
                    rounded-full bg-brand-sun/20 blur-3xl"
                aria-hidden="true"></div>

            <div
                class="pointer-events-none absolute -right-32 bottom-0 size-96
                    rounded-full bg-brand-ocean/15 blur-3xl"
                aria-hidden="true"></div>

            <div
                class="relative mx-auto grid min-h-svh max-w-7xl
                    lg:grid-cols-[1.05fr_0.95fr]">
                <aside
                    class="relative hidden overflow-hidden bg-brand-palm-dark
                        px-12 py-14 text-brand-cream lg:flex
                        lg:flex-col lg:justify-between">
                    <div
                        class="absolute inset-0 opacity-60
                            [background-image:radial-gradient(circle_at_20%_20%,rgb(242_199_107_/_0.20),transparent_30%),radial-gradient(circle_at_80%_70%,rgb(32_111_124_/_0.28),transparent_38%)]"
                        aria-hidden="true"></div>

                    <a
                        href="{{ route('home') }}"
                        class="relative z-10 inline-flex items-center gap-3
                            font-display text-2xl tracking-[0.08em]"
                        wire:navigate>
                        <span>{{ config('app.name', 'Coast & Cay') }}</span>

                        <span
                            class="size-2 rounded-full bg-brand-coral"
                            aria-hidden="true"></span>
                    </a>

                    <div class="relative z-10 max-w-lg">
                        <p
                            class="mb-5 text-xs font-semibold uppercase
                                tracking-[0.32em] text-brand-sun">
                            Caribbean hospitality
                        </p>

                        <h1
                            class="font-display text-5xl leading-[1.05]
                                sm:text-6xl">
                            Good food should feel like coming home.
                        </h1>

                        <p
                            class="mt-6 max-w-md text-base leading-8
                                text-brand-cream/75">
                            Create an account to make checkout faster and keep
                            your restaurant details together.
                        </p>
                    </div>

                    <p
                        class="relative z-10 text-sm
                            text-brand-cream/60">
                        Caribbean warmth, California ease.
                    </p>
                </aside>

                <section
                    class="flex items-center justify-center px-5 py-12
                        sm:px-8 lg:px-14">
                    <div class="w-full max-w-md">
                        <a
                            href="{{ route('home') }}"
                            class="mb-8 inline-flex items-center gap-3
                                font-display text-2xl tracking-[0.08em]
                                text-brand-palm-dark lg:hidden"
                            wire:navigate>
                            <span>{{ config('app.name', 'Coast & Cay') }}</span>

                            <span
                                class="size-2 rounded-full bg-brand-coral"
                                aria-hidden="true"></span>
                        </a>

                        <div
                            class="rounded-[2rem] border border-brand-palm/10
                                bg-white/90 p-6 shadow-island backdrop-blur
                                sm:p-8">
                            {{ $slot }}
                        </div>

                        <p
                            class="mt-6 text-center text-sm
                                text-brand-muted">
                            <a
                                href="{{ route('home') }}"
                                class="font-semibold text-brand-palm
                                    hover:text-brand-coral-dark">
                                Return to the restaurant
                            </a>
                        </p>
                    </div>
                </section>
            </div>
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
