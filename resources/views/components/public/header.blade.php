@php
use Illuminate\Support\Facades\Route;

$restaurantName = $settings['restaurant_name']
?? config('app.name');

$primaryLinks = [
['label' => 'Home', 'route' => 'home'],
['label' => 'Menu', 'route' => 'menu'],
['label' => 'About', 'route' => 'about'],
['label' => 'Gallery', 'route' => 'gallery'],
['label' => 'Contact', 'route' => 'contact.create'],
];

$orderUrl = Route::has('cart.index')
? route('cart.index')
: route('menu');

$cartUrl = Route::has('cart.index')
? route('cart.index')
: null;

$accountUrl = Route::has('account.index')
? route('account.index')
: null;
@endphp

<header
    x-data="{ open: false, scrolled: window.scrollY > 32 }"
    @scroll.window="scrolled = window.scrollY > 32"
    @keydown.escape.window="open = false"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    :class="(scrolled || open)
        ? 'border-brand-palm/10 bg-brand-cream/95 text-brand-forest shadow-[0_18px_45px_rgba(12,52,43,0.10)] backdrop-blur-xl'
        : 'border-transparent bg-transparent text-brand-cream'"
    class="fixed inset-x-0 top-0 z-50 border-b transition duration-500">
    <div class="public-container flex min-h-20 items-center justify-between gap-5">
        <a
            href="{{ route('home') }}"
            class="group inline-flex min-w-0 items-center gap-2 font-display
                text-xl tracking-[0.08em] text-current sm:text-2xl"
            aria-label="{{ $restaurantName }} home">
            <span class="truncate">{{ $restaurantName }}</span>

            <span
                class="size-2 shrink-0 rounded-full bg-brand-coral transition
                    duration-300 group-hover:scale-150"
                aria-hidden="true"></span>
        </a>

        <nav
            class="hidden items-center gap-6 lg:flex"
            aria-label="Primary navigation">
            @foreach ($primaryLinks as $link)
            @php
            $isActive = request()->routeIs($link['route']);
            @endphp

            <a
                href="{{ route($link['route']) }}"
                @class([ 'whitespace-nowrap text-[0.7rem] font-semibold uppercase tracking-[0.16em] transition duration-300' , 'text-brand-coral'=> $isActive,
                'text-current opacity-80 hover:text-brand-coral hover:opacity-100' => ! $isActive,
                ])
                @if ($isActive) aria-current="page" @endif
                >
                {{ $link['label'] }}
            </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            @if ($accountUrl)
            <a
                href="{{ $accountUrl }}"
                class="px-2 py-3 text-[0.68rem] font-semibold uppercase
                        tracking-[0.16em] text-current opacity-80 transition
                        hover:text-brand-coral hover:opacity-100">
                Account
            </a>
            @endif

            @if ($cartUrl)
            <a
                href="{{ $cartUrl }}"
                class="px-2 py-3 text-[0.68rem] font-semibold uppercase
                        tracking-[0.16em] text-current opacity-80 transition
                        hover:text-brand-coral hover:opacity-100">
                Cart
            </a>
            @endif

            <a
                href="{{ route('reservation-request.create') }}"
                class="inline-flex min-h-11 items-center justify-center rounded-full
                    border border-current/35 px-5 py-2 text-[0.66rem]
                    font-semibold uppercase tracking-[0.15em] text-current
                    transition hover:border-brand-coral hover:bg-brand-coral
                    hover:text-white">
                Reserve
            </a>

            <a
                href="{{ $orderUrl }}"
                class="inline-flex min-h-11 items-center justify-center rounded-full
                    bg-brand-coral px-5 py-2 text-[0.66rem] font-semibold
                    uppercase tracking-[0.15em] text-white transition
                    hover:-translate-y-0.5 hover:bg-brand-coral-dark">
                Order Online
            </a>
        </div>

        <button
            type="button"
            class="inline-flex size-11 items-center justify-center rounded-full
                border border-current/30 text-current transition
                hover:border-brand-coral hover:text-brand-coral lg:hidden"
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
                <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
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
                <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
            </svg>
        </button>
    </div>

    <nav
        id="mobile-navigation"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-3"
        class="absolute inset-x-0 top-full max-h-[calc(100vh-5rem)]
            overflow-y-auto border-t border-brand-palm/10 bg-brand-cream
            text-brand-forest shadow-2xl lg:hidden"
        aria-label="Mobile navigation">
        <div class="public-container py-6">
            <div class="flex flex-col">
                @foreach ($primaryLinks as $link)
                @php
                $isActive = request()->routeIs($link['route']);
                @endphp

                <a
                    href="{{ route($link['route']) }}"
                    @click="open = false"
                    @class([ 'border-b border-brand-palm/10 py-4 text-sm font-semibold uppercase tracking-[0.17em] transition' , 'text-brand-coral'=> $isActive,
                    'text-brand-forest hover:text-brand-coral' => ! $isActive,
                    ])
                    @if ($isActive) aria-current="page" @endif
                    >
                    {{ $link['label'] }}
                </a>
                @endforeach
            </div>

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

            @if ($accountUrl || $cartUrl)
            <div class="mt-5 flex flex-wrap gap-5 text-sm font-semibold">
                @if ($accountUrl)
                <a href="{{ $accountUrl }}" @click="open = false">
                    Account
                </a>
                @endif

                @if ($cartUrl)
                <a href="{{ $cartUrl }}" @click="open = false">
                    Cart
                </a>
                @endif
            </div>
            @endif
        </div>
    </nav>
</header>