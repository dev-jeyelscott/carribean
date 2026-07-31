@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
    'structuredData' => null,
    'headerOverlay' => null,
    'headerTransparent' => true,
])

@php
    /*
     * Determine whether the fixed header overlays the current page content.
     */
    $isHomepage = request()->routeIs('home');

    $headerOverlaysContent = is_bool($headerOverlay)
        ? $headerOverlay
        : $isHomepage;

    /*
     * Public pages use the transparent homepage navigation unless a page
     * explicitly requests the solid variant.
     */
    $headerUsesTransparentSurface = is_bool($headerTransparent)
        ? $headerTransparent
        : true;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-public.seo-meta
        :title="$title"
        :description="$description"
        :canonical="$canonical"
        :image="$image"
        :type="$type"
        :noindex="$noindex" />

    <x-public.restaurant-structured-data />

    @if (
        is_array($structuredData)
        && $structuredData !== []
    )
        <x-public.structured-data
            :data="$structuredData" />
    @endif

    @vite([
        'resources/css/public.css',
        'resources/css/public-header.css',
        'resources/js/app.js',
        'resources/js/public-scroll-navigation.js',
    ])

    @livewireStyles
</head>

<body
    data-page="{{ $isHomepage ? 'home' : 'default' }}"
    data-public-header-overlay="{{ $headerOverlaysContent ? 'true' : 'false' }}"
    data-public-header-transparent="{{ $headerUsesTransparentSurface ? 'true' : 'false' }}"
    class="min-h-screen overflow-x-hidden bg-canvas
        font-sans text-ink antialiased">
    <a
        href="#main-content"
        class="fixed left-4 top-4 z-[100] -translate-y-24
            rounded-full bg-primary px-5 py-3 text-sm
            font-semibold text-canvas transition
            focus:translate-y-0">
        Skip to content
    </a>

    <div class="min-h-screen">
        <div data-public-header-shell>
            <x-public.header />
        </div>

        <x-public.notification-center />

        <main
            id="main-content"
            tabindex="-1"
            @class([
                'public-main',
                'public-main--header-offset' => ! $headerOverlaysContent,
            ])>
            {{ $slot }}
        </main>

        {{--
            Fixed controls are mounted outside main and outside page-specific
            animation roots. They can therefore remain viewport-fixed above
            every page section.
        --}}
        <div
            data-public-fixed-navigation
            class="public-fixed-navigation">
            <div data-public-section-pager-portal></div>

            <x-public.scroll-identifier />
        </div>

        <x-public.footer />
    </div>

    @livewireScriptConfig
</body>

</html>
