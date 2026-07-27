@php
use Illuminate\Support\Facades\Route;

$restaurantName = $settings['restaurant_name']
    ?? config('app.name');

$tagline = $settings['tagline']
    ?? 'Caribbean warmth, California ease.';

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

$orderUrl = route('menu');

$cartUrl = Route::has('cart.index')
    ? route('cart.index')
    : null;

$accountUrl = auth()->check() && Route::has('account.index')
    ? route('account.index')
    : (Route::has('login') ? route('login') : null);
@endphp

<header
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    class="sticky inset-x-0 top-0 z-50 border-b border-line/80
        bg-canvas/95 text-ink shadow-[0_4px_24px_rgb(12_52_43_/_5%)]
        backdrop-blur-xl">
    <div
        class="public-container flex min-h-[5.5rem] items-center
            justify-between gap-5">
        <a
            href="{{ route('home') }}"
            class="group inline-flex min-w-0 items-center gap-3"
            aria-label="{{ $restaurantName }} home">
            <span
                class="flex size-12 shrink-0 items-center justify-center
                    rounded-full border border-primary/15 bg-surface text-primary
                    shadow-card transition duration-300
                    group-hover:-rotate-3 group-hover:border-coral/40
                    motion-reduce:transform-none">
                <svg
                    class="size-7"
                    viewBox="0 0 32 32"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        d="M16 28V12M16 12c-1-5-6-7-11-5 4 1 7 3 9 6M16 12c2-5 7-7 12-4-5 0-8 2-11 6M16 12c-4-3-8-3-12 0 5-1 8 1 11 4M17 14c4-3 8-2 11 1-5-1-8 0-11 3" />
                </svg>
            </span>

            <span class="min-w-0">
                <span
                    class="block truncate font-display text-2xl
                        leading-none text-primary">
                    {{ $restaurantName }}
                </span>

                <span
                    class="mt-1 hidden truncate text-[0.56rem] font-semibold
                        uppercase tracking-[0.2em] text-muted sm:block">
                    {{ $tagline }}
                </span>
            </span>
        </a>

        <nav
            class="hidden items-center gap-7 lg:flex"
            aria-label="Primary navigation">
            @foreach ($primaryLinks as $link)
                @php
                    $isActive = request()->routeIs(...$link['patterns']);
                @endphp

                <a
                    href="{{ route($link['route']) }}"
                    @class([
                        'relative py-3 text-sm font-medium transition
                        after:absolute after:inset-x-0 after:-bottom-0.5
                        after:h-0.5 after:origin-left after:bg-coral
                        after:transition-transform after:duration-300',
                        'text-coral after:scale-x-100' => $isActive,
                        'text-ink/80 after:scale-x-0 hover:text-coral
                        hover:after:scale-x-100' => ! $isActive,
                    ])
                    @if ($isActive) aria-current="page" @endif>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-2 lg:flex">
            @if ($accountUrl)
                <a
                    href="{{ $accountUrl }}"
                    class="inline-flex size-11 items-center justify-center
                        rounded-xl text-primary transition hover:bg-surface-soft
                        hover:text-coral"
                    aria-label="View customer account">
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
                </a>
            @endif

            @if ($cartUrl)
                <a
                    href="{{ $cartUrl }}"
                    class="inline-flex min-h-11 items-center gap-2 rounded-xl
                        px-3 text-primary transition hover:bg-surface-soft
                        hover:text-coral"
                    aria-label="View shopping cart">
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

                    <livewire:cart.cart-count />
                </a>
            @endif

            <a
                href="{{ $orderUrl }}"
                class="ml-2 inline-flex min-h-12 items-center gap-3
                    rounded-xl bg-coral px-5 text-xs font-semibold uppercase
                    tracking-[0.13em] text-white shadow-card transition
                    duration-300 hover:-translate-y-0.5 hover:bg-coral-deep
                    hover:shadow-panel motion-reduce:transform-none">
                Order Online

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
            </a>
        </div>

        <div class="flex items-center gap-2 lg:hidden">
            @if ($cartUrl)
                <a
                    href="{{ $cartUrl }}"
                    class="inline-flex size-11 items-center justify-center
                        rounded-xl border border-primary/15 text-primary"
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
                            class="absolute -right-3 -top-3 text-coral">
                            <livewire:cart.cart-count />
                        </span>
                    </span>
                </a>
            @endif

            <button
                type="button"
                class="inline-flex size-11 items-center justify-center
                    rounded-xl border border-primary/15 text-primary"
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

    <nav
        id="mobile-navigation"
        x-show="open"
        x-cloak
        x-transition
        class="absolute inset-x-0 top-full max-h-[calc(100vh-5.5rem)]
            overflow-y-auto border-t border-line bg-canvas shadow-panel
            lg:hidden"
        aria-label="Mobile navigation">
        <div class="public-container py-6">
            <div class="flex flex-col">
                @foreach ($primaryLinks as $link)
                    @php
                        $isActive = request()->routeIs(...$link['patterns']);
                    @endphp

                    <a
                        href="{{ route($link['route']) }}"
                        @click="open = false"
                        @class([
                            'flex items-center justify-between border-b
                            border-line py-4 text-sm font-semibold transition',
                            'text-coral' => $isActive,
                            'text-ink hover:text-coral' => ! $isActive,
                        ])
                        @if ($isActive) aria-current="page" @endif>
                        {{ $link['label'] }}

                        <span class="text-coral" aria-hidden="true">
                            &rarr;
                        </span>
                    </a>
                @endforeach
            </div>

            <a
                href="{{ $orderUrl }}"
                @click="open = false"
                class="public-button-primary mt-6 w-full">
                Order Online
            </a>

            @if ($accountUrl)
                <a
                    href="{{ $accountUrl }}"
                    @click="open = false"
                    class="mt-5 block text-center text-sm font-semibold
                        text-primary transition hover:text-coral">
                    Customer Account
                </a>
            @endif
        </div>
    </nav>
</header>
