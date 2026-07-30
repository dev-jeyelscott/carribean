@props([
    'items',
    'current' => null,
    'label' => 'Page sections',
    'context' => 'page',
    'enhancer' => 'shared',
])

@php
    /*
     * Normalize the supplied section definitions so the component only renders
     * valid hash links with human-readable labels.
     */
    $pagerItems = collect($items)
        ->filter(
            fn (mixed $item): bool => is_array($item)
                && filled(data_get($item, 'id'))
                && filled(data_get($item, 'label')),
        )
        ->map(
            fn (array $item): array => [
                'id' => ltrim(
                    (string) data_get($item, 'id'),
                    '#',
                ),
                'label' => (string) data_get($item, 'label'),
            ],
        )
        ->values();

    /*
     * Fall back to the first valid section when the caller does not explicitly
     * provide an initial active section.
     */
    $activeSection = filled($current)
        ? ltrim((string) $current, '#')
        : data_get($pagerItems->first(), 'id');
@endphp

@if ($pagerItems->isNotEmpty())
    <nav
        {{ $attributes->class('about-section-nav') }}
        data-section-pager
        data-section-pager-context="{{ $context }}"
        data-section-pager-enhancer="{{ $enhancer }}"
        data-section-pager-state="loading"
        aria-label="{{ $label }}">
        <ul class="about-section-nav__list">
            @foreach ($pagerItems as $item)
                @php
                    $isCurrent = $item['id'] === $activeSection;
                @endphp

                <li>
                    <a
                        href="#{{ $item['id'] }}"
                        data-section-pager-link
                        @if ($context === 'about')
                            data-about-section-link
                        @endif
                        @if ($context === 'gallery')
                            data-gallery-section-link
                        @endif
                        class="about-section-nav__link"
                        aria-label="{{ $item['label'] }}"
                        aria-current="{{ $isCurrent ? 'location' : 'false' }}">
                        <span
                            class="about-section-nav__dot"
                            aria-hidden="true">
                        </span>

                        <span class="about-section-nav__label">
                            {{ $item['label'] }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
