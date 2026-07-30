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
     * Normalize section definitions so only valid same-page targets render.
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
     * Use the first valid section when no explicit initial section is supplied.
     */
    $activeSection = filled($current)
        ? ltrim((string) $current, '#')
        : data_get($pagerItems->first(), 'id');

    /*
     * Gallery uses About-style desktop section transitions by default.
     * Other future consumers remain opt-in.
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
