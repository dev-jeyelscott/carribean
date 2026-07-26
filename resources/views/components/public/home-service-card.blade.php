@props([
    'title',
    'description',
    'icon',
    'tone' => 'primary',
])

@php
    $iconClasses = [
        'primary' => 'bg-primary text-white',
        'ocean' => 'bg-ocean text-white',
        'coral' => 'bg-coral text-white',
    ];

    $selectedIconClasses = $iconClasses[$tone]
        ?? $iconClasses['primary'];
@endphp

<article
    {{ $attributes->class([
        'flex h-full items-center gap-5 rounded-card border border-line',
        'bg-surface p-6 shadow-card transition duration-300 ease-island',
        'hover:-translate-y-1 hover:shadow-panel',
        'motion-reduce:transform-none motion-reduce:transition-none',
    ]) }}
    data-gsap-reveal>
    <div
        @class([
            'flex size-16 shrink-0 items-center justify-center rounded-full',
            $selectedIconClasses,
        ])>
        @if ($icon === 'dine-in')
            <svg
                class="size-7"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
                aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 3v7.5M3.75 3v4.5A2.25 2.25 0 0 0 6 9.75 2.25 2.25 0 0 0 8.25 7.5V3M6 9.75V21M15.75 3v18M15.75 3c2.485 0 4.5 2.35 4.5 5.25s-2.015 5.25-4.5 5.25" />
            </svg>
        @elseif ($icon === 'pickup')
            <svg
                class="size-7"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
                aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M5 8.5h14l-1 12H6l-1-12Z" />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8.5 9V6.75a3.5 3.5 0 0 1 7 0V9" />
            </svg>
        @else
            <svg
                class="size-8"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
                aria-hidden="true">
                <circle cx="7" cy="18" r="2" />
                <circle cx="18" cy="18" r="2" />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 18h7M4.5 18H3.25a1.25 1.25 0 0 1-1.15-1.74l1.4-3.26h5.25l2.5-5h3.25l2.25 5H20a2 2 0 0 1 2 2v1.75A1.25 1.25 0 0 1 20.75 18H20M7.25 13H17" />
            </svg>
        @endif
    </div>

    <div>
        <h3 class="font-display text-2xl text-ink">
            {{ $title }}
        </h3>

        <p class="mt-2 text-sm leading-6 text-muted">
            {{ $description }}
        </p>
    </div>
</article>
