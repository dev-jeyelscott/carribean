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

<nav
    aria-label="Customer account"
    class="flex flex-wrap items-center gap-2 rounded-2xl
        border border-brand-palm/10 bg-white/75 p-2 shadow-sm">
    @foreach ($accountLinks as $link)
        <a
            href="{{ route($link['route']) }}"
            @class([
                'rounded-xl px-4 py-3 text-sm font-semibold transition',
                'bg-brand-palm text-brand-cream' =>
                    request()->routeIs($link['active']),
                'text-brand-palm hover:bg-brand-palm/10' =>
                    ! request()->routeIs($link['active']),
            ])
            @if (request()->routeIs($link['active']))
                aria-current="page"
            @endif>
            {{ $link['label'] }}
        </a>
    @endforeach

    <form
        method="POST"
        action="{{ route('logout') }}"
        class="ml-auto">
        @csrf

        <button
            type="submit"
            class="rounded-xl px-4 py-3 text-sm font-semibold
                text-brand-coral-dark transition hover:bg-brand-coral/10">
            Log out
        </button>
    </form>
</nav>
