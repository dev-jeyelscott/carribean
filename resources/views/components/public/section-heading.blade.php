@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'align' => 'center',
    'theme' => 'dark',
])

@php
    /*
     * A light theme means the component is rendered on a light surface.
     * A dark theme means it is rendered over a dark or photographic surface.
     */
    $isCentered = $align === 'center';
    $isLightSurface = $theme === 'light';

    $descriptionText = is_string($description)
        ? $description
        : (string) $description;

    $isRichDescription =
        strip_tags($descriptionText) !== $descriptionText;
@endphp

<div
    data-gsap-reveal
    {{ $attributes->except('class') }}
    @class([
        'max-w-3xl',
        'mx-auto text-center' => $isCentered,
    ])>
    @if ($eyebrow)
        <p @class([
            'text-xs font-semibold uppercase tracking-[0.32em]',
            'text-coral-deep' => $isLightSurface,
            'text-sun' => ! $isLightSurface,
        ])>
            {{ $eyebrow }}
        </p>
    @endif

    <h2 @class([
        'mt-4 font-display text-4xl leading-tight sm:text-5xl',
        'text-ink' => $isLightSurface,
        'text-canvas' => ! $isLightSurface,
    ])>
        {{ $title }}
    </h2>

    @if ($description)
        @if ($isRichDescription)
            <div @class([
                'mt-5 max-w-2xl space-y-4 text-base leading-8',
                'mx-auto' => $isCentered,
                '[&_a]:font-semibold [&_a]:underline [&_a]:underline-offset-4',
                '[&_blockquote]:border-l-2 [&_blockquote]:pl-5',
                '[&_h2]:mt-6 [&_h2]:font-display [&_h2]:text-2xl',
                '[&_h3]:mt-5 [&_h3]:font-display [&_h3]:text-xl',
                '[&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-6',
                '[&_p]:leading-8',
                '[&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-6',
                'text-muted [&_a]:text-coral-deep
                    [&_blockquote]:border-coral/50
                    [&_h2]:text-ink [&_h3]:text-ink
                    [&_li]:marker:text-coral-deep
                    [&_strong]:text-ink' => $isLightSurface,
                'text-canvas/75 [&_a]:text-sun
                    [&_blockquote]:border-sun/50
                    [&_h2]:text-canvas [&_h3]:text-canvas
                    [&_li]:marker:text-sun
                    [&_strong]:text-canvas' => ! $isLightSurface,
            ])>
                {!! str($descriptionText)->sanitizeHtml() !!}
            </div>
        @else
            <p @class([
                'mt-5 max-w-2xl text-base leading-8',
                'mx-auto' => $isCentered,
                'text-muted' => $isLightSurface,
                'text-canvas/75' => ! $isLightSurface,
            ])>
                {{ $description }}
            </p>
        @endif
    @endif

    <div
        @class([
            'mt-7 h-px w-14',
            'mx-auto' => $isCentered,
            'bg-coral' => $isLightSurface,
            'bg-sun' => ! $isLightSurface,
        ])
        aria-hidden="true">
    </div>
</div>
