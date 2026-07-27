@props([
    'category',
    'tone' => 'coral',
])

@php
$iconClasses = match ($tone) {
    'ocean' => 'bg-ocean/10 text-ocean',
    'sun' => 'bg-sun/20 text-coral-deep',
    'primary' => 'bg-primary/10 text-primary',
    default => 'bg-coral/10 text-coral',
};
@endphp

<a
    href="{{ route('menu') }}#category-{{ $category->slug }}"
    data-reveal
    class="group inline-flex min-h-14 items-center gap-3 rounded-full
        border border-line bg-surface px-5 py-3 text-sm font-semibold
        text-ink shadow-card transition duration-300 hover:-translate-y-0.5
        hover:border-coral/35 hover:text-coral hover:shadow-panel
        motion-reduce:transform-none">
    <span
        class="flex size-8 shrink-0 items-center justify-center
            rounded-full {{ $iconClasses }}">
        <svg
            class="size-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
            aria-hidden="true">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M5 11h14a7 7 0 0 1-14 0Z" />

            <path
                stroke-linecap="round"
                d="M8 8c0-1 1-1.5 1-2.5M12 8c0-1 1-1.5 1-2.5M16 8c0-1 1-1.5 1-2.5" />
        </svg>
    </span>

    {{ $category->name }}
</a>
