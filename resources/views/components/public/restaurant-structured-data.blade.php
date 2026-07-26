@php
    $settings = \App\Models\SiteSetting::publicContactMap();

    $restaurantName = $settings['restaurant_name']
        ?? config('app.name');

    $socialLinks = array_values(array_filter([
        $settings['instagram_url'] ?? null,
        $settings['facebook_url'] ?? null,
        $settings['tiktok_url'] ?? null,
    ]));

    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'Restaurant',
        '@id' => route('home').'#restaurant',
        'name' => $restaurantName,
        'url' => route('home'),
        'menu' => route('menu'),
        'servesCuisine' => 'Caribbean',
        'priceRange' => '$$',
    ];

    if (filled($settings['phone'] ?? null)) {
        $data['telephone'] = $settings['phone'];
    }

    if (filled($settings['email'] ?? null)) {
        $data['email'] = $settings['email'];
    }

    if (filled($settings['address'] ?? null)) {
        $data['address'] = $settings['address'];
    }

    if ($socialLinks !== []) {
        $data['sameAs'] = $socialLinks;
    }
@endphp

<x-public.structured-data :data="$data" />
