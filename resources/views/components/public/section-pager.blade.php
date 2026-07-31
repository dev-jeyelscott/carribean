@props([
    'items',
    'current' => null,
    'label' => 'Page sections',
    'context' => 'page',
    'enhancer' => 'shared',
    'snap' => null,
])

@php
    /*
     * Normalize the supplied section definitions so the component renders only
     * valid same-page navigation targets.
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
     * Use the first valid section as the initial active target when the caller
     * does not explicitly provide the current section.
     */
    $activeSection = filled($current)
        ? ltrim((string) $current, '#')
        : data_get($pagerItems->first(), 'id');

    /*
     * Gallery enables section snapping by default. Other consumers must enable
     * it explicitly so normal page scrolling remains the safe default.
     */
    $snapEnabled = is_bool($snap)
        ? $snap
        : $context === 'gallery';
@endphp

@if ($pagerItems->isNotEmpty())
    <nav
        {{ $attributes->class('about-section-nav') }}
        data-section-pager
        data-section-pager-context="{{ $context }}"
        data-section-pager-enhancer="{{ $enhancer }}"
        data-section-pager-snap="{{ $snapEnabled ? 'true' : 'false' }}"
        data-section-pager-state="loading"
        aria-label="{{ $label }}">
        <ul class="about-section-nav__list">
            @foreach ($pagerItems as $item)
                @php
                    /*
                     * Mark the caller-provided current section for accessible
                     * same-page navigation state.
                     */
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
