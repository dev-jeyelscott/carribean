@props([
    'label',
    'value',
    'detail' => null,
    'tone' => 'primary',
])

@php
    $toneClasses = match ($tone) {
        'coral' => 'border-coral/20 bg-coral/[0.09]',
        'ocean' => 'border-ocean/20 bg-ocean/[0.09]',
        default => 'border-primary/15 bg-primary/[0.07]',
    };

    $valueClasses = match ($tone) {
        'coral' => 'text-coral-deep',
        'ocean' => 'text-ocean',
        default => 'text-primary-deep',
    };
@endphp

<article
    {{ $attributes->class([
        'rounded-[1.5rem] border p-5 transition duration-300',
        'hover:-translate-y-1 hover:shadow-card',
        'motion-reduce:transform-none motion-reduce:transition-none',
        $toneClasses,
    ]) }}>
    <p
        class="text-xs font-semibold uppercase tracking-[0.18em]
            text-muted">
        {{ $label }}
    </p>

    <p
        @class([
            'mt-3 font-display text-3xl leading-none sm:text-4xl',
            $valueClasses,
        ])>
        {{ $value }}
    </p>

    @if ($detail)
        <p class="mt-3 text-sm leading-6 text-muted">
            {{ $detail }}
        </p>
    @endif
</article>
