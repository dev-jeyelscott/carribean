@props([
    'type' => 'info',
    'title' => null,
    'surface' => 'dark',
])

@php
    /*
     * Preserve the existing dark-surface appearance by default while allowing
     * public pages such as the cart to request accessible light-surface colors.
     */
    $palettes = [
        'dark' => [
            'info' => 'border-blue-300/30 bg-blue-400/10 text-blue-100',
            'success' => 'border-emerald-300/30 bg-emerald-400/10 text-emerald-100',
            'warning' => 'border-amber-300/30 bg-amber-400/10 text-amber-100',
            'error' => 'border-red-300/30 bg-red-400/10 text-red-100',
        ],
        'light' => [
            'info' => 'border-ocean/25 bg-ocean/10 text-ink',
            'success' => 'border-primary/25 bg-primary/10 text-ink',
            'warning' => 'border-sun/55 bg-sun/15 text-ink',
            'error' => 'border-coral/35 bg-coral/10 text-ink',
        ],
    ];

    $surfacePalette = $palettes[$surface] ?? $palettes['dark'];

    $classes = $surfacePalette[$type]
        ?? ($surface === 'light'
            ? 'border-line bg-surface-soft text-ink'
            : 'border-white/10 bg-white/5 text-stone-100');

    $role = in_array($type, ['warning', 'error'], true)
        ? 'alert'
        : 'status';

    $ariaLive = $role === 'alert' ? 'assertive' : 'polite';
@endphp

<div
    role="{{ $role }}"
    aria-live="{{ $ariaLive }}"
    {{ $attributes->merge([
        'class' => "rounded-card border px-4 py-3 text-sm leading-6 {$classes}",
    ]) }}>
    @if ($title)
        <p class="font-semibold text-current">
            {{ $title }}
        </p>
    @endif

    <div @class(['mt-1' => $title])>
        {{ $slot }}
    </div>
</div>
