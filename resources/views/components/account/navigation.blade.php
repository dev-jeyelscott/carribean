@php
    $accountLinks = [
        [
            'label' => 'Overview',
            'route' => 'account.index',
            'active' => 'account.index',
        ],
        [
            'label' => 'Profile',
            'route' => 'account.profile',
            'active' => 'account.profile',
        ],
        [
            'label' => 'Orders',
            'route' => 'account.orders.index',
            'active' => 'account.orders.*',
        ],
    ];
@endphp

<div
    class="flex items-center gap-2 rounded-[1.5rem] border border-line
        bg-surface/90 p-2 shadow-card backdrop-blur-md">
    <nav
        aria-label="Customer account"
        class="min-w-0 flex-1 overflow-x-auto [scrollbar-width:none]
            [&::-webkit-scrollbar]:hidden">
        <div class="flex min-w-max items-center gap-1">
            @foreach ($accountLinks as $link)
                @php
                    $isActive = request()->routeIs($link['active']);
                @endphp

                <a
                    href="{{ route($link['route']) }}"
                    @class([
                        'inline-flex min-h-11 items-center gap-2 rounded-[1rem]',
                        'px-4 py-2.5 text-sm font-semibold transition duration-300',
                        'focus-visible:outline-none motion-reduce:transition-none',
                        'bg-primary text-canvas shadow-card' => $isActive,
                        'text-primary hover:bg-primary/[0.08]' => ! $isActive,
                    ])
                    @if ($isActive)
                        aria-current="page"
                    @endif>
                    <span
                        @class([
                            'size-2 rounded-full transition',
                            'bg-coral' => $isActive,
                            'bg-primary/25' => ! $isActive,
                        ])
                        aria-hidden="true">
                    </span>

                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <div
        class="h-7 w-px shrink-0 bg-line"
        aria-hidden="true">
    </div>

    <form
        method="POST"
        action="{{ route('logout') }}"
        class="shrink-0">
        @csrf

        <button
            type="submit"
            class="inline-flex min-h-11 items-center gap-2 rounded-[1rem]
                px-3 py-2.5 text-sm font-semibold text-coral-deep
                transition duration-300 hover:bg-coral/[0.10]
                motion-reduce:transition-none"
            aria-label="Log out of your account">
            <svg
                class="size-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15.75 9V5.625A2.625 2.625 0 0 0
                        13.125 3h-6.75A2.625 2.625 0 0 0
                        3.75 5.625v12.75A2.625 2.625 0 0 0
                        6.375 21h6.75a2.625 2.625 0 0 0
                        2.625-2.625V15m-3-6 3-3m0 0 3 3m-3-3v12" />
            </svg>

            <span class="hidden sm:inline">
                Log out
            </span>
        </button>
    </form>
</div>
