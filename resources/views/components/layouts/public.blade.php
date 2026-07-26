<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-public.seo-meta
        :title="$title ?? null"
        :description="$description ?? null">

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
