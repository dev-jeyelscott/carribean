@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
    'structuredData' => null,
])

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @class([
        'home-scroll-snap' => request()->routeIs('home'),
    ])
>

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
        'resources/js/app.js',
    ])

    @livewireStyles
</head>

<body
    class="min-h-screen overflow-x-hidden bg-brand-cream
        font-sans text-brand-forest antialiased">
    <a
        href="#main-content"
        class="fixed left-4 top-4 z-[100] -translate-y-24
            rounded-full bg-brand-palm px-5 py-3 text-sm
            font-semibold text-brand-cream transition
            focus:translate-y-0">
        Skip to content
    </a>

    <div class="min-h-screen">
        <x-public.header />

        <x-public.notification-center />

        <main id="main-content" tabindex="-1">
            {{ $slot }}
        </main>

        <x-public.footer />
    </div>

    @livewireScriptConfig
</body>

</html>
