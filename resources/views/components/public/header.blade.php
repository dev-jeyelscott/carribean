@php
    use Illuminate\Support\Facades\Route;

    $restaurantName = $settings['restaurant_name']
        ?? config('app.name');

    $tagline = $settings['tagline']
        ?? 'Caribbean warmth, California ease.';

    $phone = $settings['phone'] ?? null;
    $address = $settings['address'] ?? null;
    $openingHours = $settings['opening_hours'] ?? null;
    $mapLink = $settings['map_link'] ?? null;

    $phoneDigits = is_string($phone)
        ? preg_replace('/\D+/', '', $phone)
        : null;

    $phoneTarget = is_string($phone)
        && is_string($phoneDigits)
        && $phoneDigits !== ''
            ? (str_starts_with(ltrim($phone), '+') ? '+' : '').$phoneDigits
            : null;

    $primaryLinks = [
        [
            'label' => 'Home',
            'route' => 'home',
            'patterns' => ['home'],
        ],
        [
            'label' => 'Menu',
            'route' => 'menu',
            'patterns' => ['menu', 'menu-items.*'],
        ],
        [
            'label' => 'About',
            'route' => 'about',
            'patterns' => ['about'],
        ],
        [
            'label' => 'Gallery',
            'route' => 'gallery',
            'patterns' => ['gallery'],
        ],
        [
            'label' => 'Contact',
            'route' => 'contact.create',
            'patterns' => ['contact.*'],
        ],
    ];

    if (
        ($hasPublishedBlogPosts ?? false)
        && Route::has('blog.index')
    ) {
        array_splice(
            $primaryLinks,
            4,
            0,
            [[
                'label' => 'Journal',
                'route' => 'blog.index',
                'patterns' => ['blog.*'],
            ]],
        );
    }

    $orderUrl = Route::has('cart.index')
        ? route('cart.index')
        : route('menu');

    $cartUrl = Route::has('cart.index')
        ? route('cart.index')
        : null;

    $accountUrl = auth()->check() && Route::has('account.index')
        ? route('account.index')
        : (Route::has('login') ? route('login') : null);

    $socialLinks = array_filter([
        'Instagram' => $settings['instagram_url'] ?? null,
        'Facebook' => $settings['facebook_url'] ?? null,
        'TikTok' => $settings['tiktok_url'] ?? null,
    ]);
@endphp
    x-data="{
        open: false,
        scrolled: window.scrollY > 32,
    }"
    @scroll.window="scrolled = window.scrollY > 32"
    @keydown.escape.window="open = false"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    class="fixed inset-x-0 top-0 z-50">
    {{-- Desktop utility bar for essential restaurant information. --}}
    <div
        x-show="! scrolled && ! open"
        x-transition:enter="transition duration-200 ease-island"
        x-transition:enter-start="-translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition duration-150 ease-island"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="-translate-y-full opacity-0"
        class="hidden border-b border-brand-cream/10
            bg-brand-palm-dark text-brand-cream xl:block">
        <div
            class="public-container flex min-h-10 items-center
                justify-between gap-8">
            <div
                class="flex min-w-0 items-center gap-6 text-[0.65rem]
                    font-semibold uppercase tracking-[0.16em]">
                @if ($phoneTarget)
                    <a
                        href="tel:{{ $phoneTarget }}"
                        class="inline-flex items-center gap-2 transition
                            hover:text-brand-sun">
                        <svg
                            class="size-4 shrink-0 text-brand-coral"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.75"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8.25 4.5 6.75 3H4.5A1.5 1.5 0 0 0 3 4.5C3 13.613 10.387 21 19.5 21a1.5 1.5 0 0 0 1.5-1.5v-2.25l-1.5-1.5-3.75 1.5a13.56 13.56 0 0 1-9-9l1.5-3.75Z" />
                        </svg>

                        <span class="text-brand-sun">Call &amp; order</span>

                        <span class="text-brand-cream/75">
                            {{ $phone }}
                        </span>
                    </a>
                @endif

                @if ($address)
                    @if ($mapLink)
                        <a
                            href="{{ $mapLink }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex min-w-0 items-center gap-2
                                text-brand-cream/70 transition
                                hover:text-brand-sun">
                            <svg
                                class="size-4 shrink-0 text-brand-coral"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.75"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 21s7-5.25 7-12a7 7 0 1 0-14 0c0 6.75 7 12 7 12Z" />

                                <circle cx="12" cy="9" r="2.25" />
                            </svg>

                            <span class="max-w-72 truncate">
                                {{ $address }}
                            </span>
                        </a>
                    @else
                        <span
                            class="inline-flex min-w-0 items-center gap-2
                                text-brand-cream/70">
                            <svg
                                class="size-4 shrink-0 text-brand-coral"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.75"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 21s7-5.25 7-12a7 7 0 1 0-14 0c0 6.75 7 12 7 12Z" />

                                <circle cx="12" cy="9" r="2.25" />
                            </svg>

                            <span class="max-w-72 truncate">
                                {{ $address }}
                            </span>
                        </span>
                    @endif
                @endif
            </div>

            <div
                class="flex shrink-0 items-center gap-6 text-[0.65rem]
                    font-semibold uppercase tracking-[0.16em]">
                @if ($openingHours)
                    <span
                        class="inline-flex items-center gap-2
                            text-brand-cream/70">
                        <svg
                            class="size-4 text-brand-coral"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.75"
                            aria-hidden="true">
                            <circle cx="12" cy="12" r="8.25" />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 7.5V12l3 1.75" />
                        </svg>

                        {{ str($openingHours)->squish() }}
                    </span>
                @endif

                @if ($socialLinks !== [])
                    <div class="flex items-center gap-4">
                        @foreach ($socialLinks as $label => $url)
                            <a
                                href="{{ $url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-brand-cream/65 transition
                                    hover:text-brand-sun">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Main public navigation. --}}
    <div
        :class="(scrolled || open)
            ? 'border-brand-palm/10 bg-brand-cream/95 text-brand-forest shadow-island backdrop-blur-xl'
            : 'border-transparent bg-transparent text-brand-cream'"
        class="border-b transition duration-300 ease-island
            motion-reduce:transition-none">
        <div
            class="public-container flex min-h-20 items-center
                justify-between gap-5">
            <a
                href="{{ route('home') }}"
                class="group inline-flex min-w-0 items-center gap-3"
                aria-label="{{ $restaurantName }} home">
                <span
                    class="flex size-10 shrink-0 items-center justify-center
                        rounded-full bg-brand-coral font-display text-lg
                        text-white transition duration-300 ease-island
                        group-hover:-rotate-6 group-hover:bg-brand-coral-dark
                        motion-reduce:transform-none
                        motion-reduce:transition-none">
                    {{ str($restaurantName)->substr(0, 1)->upper() }}
                </span>

                <span class="min-w-0">
                    <span
                        class="block truncate font-display text-xl
                            tracking-[0.05em] sm:text-2xl">
                        {{ $restaurantName }}
                    </span>

                    <span
                        class="hidden truncate text-[0.56rem] font-semibold
                            uppercase tracking-[0.18em] text-current
                            opacity-60 2xl:block">
                        {{ $tagline }}
                    </span>
                </span>
            </a>

            <nav
                class="hidden items-center gap-6 xl:flex"
                aria-label="Primary navigation">
                @foreach ($primaryLinks as $link)
                    @php
                        $isActive = request()->routeIs(
                            ...$link['patterns'],
                        );
                    @endphp

                    <a
                        href="{{ route($link['route']) }}"
                        @class([
                            'relative whitespace-nowrap py-3 text-[0.68rem]
                                font-semibold uppercase tracking-[0.16em]
                                transition duration-300 ease-island
                                after:absolute after:inset-x-0 after:-bottom-0.5
                                after:h-px after:origin-left
                                after:bg-brand-coral after:transition-transform
                                after:duration-300 motion-reduce:transition-none',
                            'text-brand-coral after:scale-x-100' => $isActive,
                            'text-current opacity-80 after:scale-x-0
                                hover:text-brand-coral hover:opacity-100
                                hover:after:scale-x-100' => ! $isActive,
                        ])
                        @if ($isActive) aria-current="page" @endif>
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 xl:flex">
                @if ($accountUrl)
                    <a
                        href="{{ $accountUrl }}"
                        class="inline-flex min-h-11 items-center gap-2
                            rounded-full px-3 text-[0.66rem] font-semibold
                            uppercase tracking-[0.15em] text-current
                            opacity-80 transition duration-300 ease-island
                            hover:text-brand-coral hover:opacity-100">
                        <svg
                            class="size-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.75"
                            aria-hidden="true">
                            <circle cx="12" cy="8" r="3.25" />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5.5 20a6.5 6.5 0 0 1 13 0" />
                        </svg>

                        Account
                    </a>
                @endif

                @if ($cartUrl)
                    <a
                        href="{{ $cartUrl }}"
                        class="inline-flex min-h-11 items-center gap-2
                            rounded-full px-3 text-[0.66rem] font-semibold
                            uppercase tracking-[0.15em] text-current
                            opacity-80 transition duration-300 ease-island
                            hover:text-brand-coral hover:opacity-100"
                        aria-label="View shopping cart">
                        <svg
                            class="size-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.75"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5.5 8.5h13l-1 11h-11l-1-11Z" />

                            <path
                                stroke-linecap="round"
                                d="M9 9V6.75a3 3 0 0 1 6 0V9" />
                        </svg>

                        Cart

                        <livewire:cart.cart-count />
                    </a>
                @endif

                <a
                    href="{{ route('reservation-request.create') }}"
                    class="inline-flex min-h-11 items-center justify-center
                        rounded-full border border-current/30 px-5 py-2
                        text-[0.64rem] font-semibold uppercase
                        tracking-[0.15em] text-current transition
                        duration-300 ease-island hover:-translate-y-0.5
                        hover:border-brand-coral hover:bg-brand-coral
                        hover:text-white motion-reduce:transform-none
                        motion-reduce:transition-none">
                    Reserve
                </a>

                <a
                    href="{{ $orderUrl }}"
                    class="inline-flex min-h-11 items-center justify-center
                        rounded-full bg-brand-coral px-5 py-2
                        text-[0.64rem] font-semibold uppercase
                        tracking-[0.15em] text-white transition
                        duration-300 ease-island hover:-translate-y-0.5
                        hover:bg-brand-coral-dark hover:shadow-island
                        motion-reduce:transform-none
                        motion-reduce:transition-none">
                    Order Online
                </a>
            </div>

            <div class="flex items-center gap-2 xl:hidden">
                @if ($cartUrl)
                    <a
                        href="{{ $cartUrl }}"
                        class="inline-flex size-11 items-center justify-center
                            rounded-full border border-current/25 text-current
                            transition hover:border-brand-coral
                            hover:text-brand-coral"
                        aria-label="View shopping cart">
                        <span class="relative">
                            <svg
                                class="size-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.75"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5.5 8.5h13l-1 11h-11l-1-11Z" />

                                <path
                                    stroke-linecap="round"
                                    d="M9 9V6.75a3 3 0 0 1 6 0V9" />
                            </svg>

                            <span
                                class="absolute -right-3 -top-3
                                    text-brand-coral">
                                <livewire:cart.cart-count />
                            </span>
                        </span>
                    </a>
                @endif

                <button
                    type="button"
                    class="inline-flex size-11 items-center justify-center
                        rounded-full border border-current/25 text-current
                        transition hover:border-brand-coral
                        hover:text-brand-coral"
                    @click="open = ! open"
                    :aria-expanded="open.toString()"
                    aria-controls="mobile-navigation"
                    aria-label="Toggle navigation">
                    <svg
                        x-show="! open"
                        class="size-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            d="M4 7h16M4 12h16M4 17h16" />
                    </svg>

                    <svg
                        x-show="open"
                        x-cloak
                        class="size-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile and tablet navigation drawer. --}}
    <nav
        id="mobile-navigation"
        x-show="open"
        x-cloak
        x-transition:enter="transition duration-200 ease-island"
        x-transition:enter-start="-translate-y-3 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition duration-150 ease-island"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="-translate-y-3 opacity-0"
        class="absolute inset-x-0 top-full max-h-[calc(100vh-5rem)]
            overflow-y-auto border-t border-brand-palm/10
            bg-brand-cream text-brand-forest shadow-island xl:hidden"
        aria-label="Mobile navigation">
        <div class="public-container py-6">
            <div class="flex flex-col">
                @foreach ($primaryLinks as $link)
                    @php
                        $isActive = request()->routeIs(
                            ...$link['patterns'],
                        );
                    @endphp

                    <a
                        href="{{ route($link['route']) }}"
                        @click="open = false"
                        @class([
                            'flex items-center justify-between border-b
                                border-brand-palm/10 py-4 text-sm
                                font-semibold uppercase tracking-[0.17em]
                                transition duration-300',
                            'text-brand-coral' => $isActive,
                            'text-brand-forest hover:text-brand-coral' => ! $isActive,
                        ])
                        @if ($isActive) aria-current="page" @endif>
                        {{ $link['label'] }}

                        <span
                            class="text-brand-coral"
                            aria-hidden="true">
                            &rarr;
                        </span>
                    </a>
                @endforeach
            </div>

            @if ($accountUrl)
                <a
                    href="{{ $accountUrl }}"
                    @click="open = false"
                    class="mt-5 inline-flex items-center gap-2 text-sm
                        font-semibold text-brand-palm transition
                        hover:text-brand-coral">
                    <svg
                        class="size-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        aria-hidden="true">
                        <circle cx="12" cy="8" r="3.25" />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5.5 20a6.5 6.5 0 0 1 13 0" />
                    </svg>

                    Account
                </a>
            @endif

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <a
                    href="{{ route('reservation-request.create') }}"
                    @click="open = false"
                    class="public-button-secondary text-brand-palm">
                    Reserve a Table
                </a>

                <a
                    href="{{ $orderUrl }}"
                    @click="open = false"
                    class="public-button-primary">
                    Order Online
                </a>
            </div>

            @if ($phoneTarget || $openingHours)
                <div
                    class="mt-7 rounded-island bg-brand-sand-soft p-5
                        text-sm text-brand-muted">
                    @if ($phoneTarget)
                        <a
                            href="tel:{{ $phoneTarget }}"
                            class="font-semibold text-brand-palm transition
                                hover:text-brand-coral">
                            {{ $phone }}
                        </a>
                    @endif

                    @if ($openingHours)
                        <p class="mt-2 leading-6">
                            {{ $openingHours }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </nav>
</header>
