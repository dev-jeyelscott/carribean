@php
    /*
     * Resolve the active authentication mode from Fortify's named routes.
     *
     * Password recovery and verification screens use the login-oriented shell
     * while preserving their own slot content.
     */
    $isLogin = request()->routeIs('login');
    $isRegister = request()->routeIs('register');

    $visualTitle = $isRegister
        ? 'Good food, fewer steps.'
        : 'Your table is waiting.';

    $visualDescription = $isRegister
        ? 'Create your customer account for faster checkout and clear order history.'
        : 'Sign in to continue ordering and keep every Coast & Cay visit organized.';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>

    <body
        data-page="auth"
        class="auth-page min-h-screen font-sans text-brand-forest antialiased">
        <main class="auth-shell">
            <div class="auth-shell__grid">
                <aside class="auth-visual-panel">
                    <a
                        href="{{ route('home') }}"
                        class="auth-brand-link"
                        wire:navigate>
                        <span
                            class="auth-brand-link__mark"
                            aria-hidden="true">
                            <svg
                                class="size-5"
                                viewBox="0 0 32 32"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5">
                                <path
                                    stroke-linecap="round"
                                    d="M16 28V12M16 12c-1-5-6-7-11-5 4 1 7 3 9 6M16 12c2-5 7-7 12-4-5 0-8 2-11 6M16 12c-4-3-8-3-12 0 5-1 8 1 11 4M17 14c4-3 8-2 11 1-5-1-8 0-11 3" />
                            </svg>
                        </span>

                        <span>
                            {{ config('app.name', 'Coast & Cay') }}
                        </span>
                    </a>

                    <div class="relative z-10 max-w-xl">
                        <p
                            class="mb-5 text-xs font-semibold uppercase
                                tracking-[0.3em] text-brand-sun">
                            Caribbean hospitality
                        </p>

                        <h1 class="auth-visual-title">
                            {{ $visualTitle }}
                        </h1>

                        <p
                            class="mt-6 max-w-md text-base leading-8
                                text-brand-cream/76">
                            {{ $visualDescription }}
                        </p>

                        <div
                            class="auth-benefit-list"
                            aria-label="Customer account benefits">
                            <article class="auth-benefit-card">
                                <span class="auth-benefit-card__number">
                                    01
                                </span>

                                <div>
                                    <h2 class="font-display text-lg text-white">
                                        Browse your favorites
                                    </h2>

                                    <p
                                        class="mt-1 text-xs leading-5
                                            text-white/65">
                                        Explore dishes and configure every option.
                                    </p>
                                </div>
                            </article>

                            <article class="auth-benefit-card">
                                <span class="auth-benefit-card__number">
                                    02
                                </span>

                                <div>
                                    <h2 class="font-display text-lg text-white">
                                        Checkout with ease
                                    </h2>

                                    <p
                                        class="mt-1 text-xs leading-5
                                            text-white/65">
                                        Keep your customer details ready for orders.
                                    </p>
                                </div>
                            </article>

                            <article class="auth-benefit-card">
                                <span class="auth-benefit-card__number">
                                    03
                                </span>

                                <div>
                                    <h2 class="font-display text-lg text-white">
                                        Follow every order
                                    </h2>

                                    <p
                                        class="mt-1 text-xs leading-5
                                            text-white/65">
                                        Review progress and previous selections.
                                    </p>
                                </div>
                            </article>
                        </div>
                    </div>

                    <div
                        class="relative z-10 flex items-center justify-between
                            gap-4 text-xs text-brand-cream/60">
                        <span>
                            Caribbean warmth, California ease.
                        </span>

                        <span
                            class="inline-flex items-center gap-2
                                uppercase tracking-[0.14em]">
                            <span
                                class="size-1.5 rounded-full bg-brand-coral"
                                aria-hidden="true">
                            </span>

                            Secure customer account
                        </span>
                    </div>
                </aside>

                <section class="auth-form-panel">
                    <div
                        class="auth-form-wrap
                            {{ $isRegister ? 'max-w-xl' : 'max-w-md' }}">
                        <a
                            href="{{ route('home') }}"
                            class="auth-mobile-brand"
                            wire:navigate>
                            <span>
                                {{ config('app.name', 'Coast & Cay') }}
                            </span>

                            <span
                                class="size-2 rounded-full bg-brand-coral"
                                aria-hidden="true">
                            </span>
                        </a>

                        @if (Route::has('register'))
                            <nav
                                class="auth-route-switch"
                                aria-label="Customer authentication">
                                <a
                                    href="{{ route('login') }}"
                                    class="auth-route-switch__link"
                                    aria-current="{{ $isLogin ? 'page' : 'false' }}"
                                    wire:navigate>
                                    Log in
                                </a>

                                <a
                                    href="{{ route('register') }}"
                                    class="auth-route-switch__link"
                                    aria-current="{{ $isRegister ? 'page' : 'false' }}"
                                    wire:navigate>
                                    Create account
                                </a>
                            </nav>
                        @endif

                        <div class="auth-form-card">
                            {{ $slot }}
                        </div>

                        <p class="mt-6 text-center">
                            <a
                                href="{{ route('home') }}"
                                class="auth-return-link"
                                wire:navigate>
                                <span aria-hidden="true">&larr;</span>
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
