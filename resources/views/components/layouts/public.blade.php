@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
    'structuredData' => null,
    'headerOverlay' => null,
])

@php
    /*
     * The homepage overlays the shared navigation on its hero by default.
     * Other public pages reserve the expanded-header height before main content.
     * A page may explicitly override this behavior through :header-overlay.
     */
    $isHomepage = request()->routeIs('home');

    $headerOverlaysContent = is_bool($headerOverlay)
        ? $headerOverlay
        : $isHomepage;
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
    ])

    @livewireStyles
</head>

<body
    data-page="{{ $isHomepage ? 'home' : 'default' }}"
    data-public-header-overlay="{{ $headerOverlaysContent ? 'true' : 'false' }}"
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

        <x-public.footer />
    </div>

    @livewireScriptConfig
</body>

</html>
