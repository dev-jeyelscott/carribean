@props([
    'label' => 'Scroll',
])

@php
    /*
     * Display the shared scroll identifier only on immersive public pages.
     * Standard transactional and account pages retain normal document flow.
     */
    $isVisible = request()->routeIs([
        'menu',
        'about',
        'gallery',
        'blog.index',
        'blog.show',
        'contact.create',
    ]);
@endphp

@if ($isVisible)
    <button
        type="button"
        data-public-scroll-identifier
        class="public-scroll-identifier"
        aria-label="Scroll to the next page section">
        <span
            class="public-scroll-identifier__track"
            aria-hidden="true">
            <span class="public-scroll-identifier__dot"></span>
        </span>

        <span class="public-scroll-identifier__label">
            {{ $label }}
        </span>
    </button>
@endif
