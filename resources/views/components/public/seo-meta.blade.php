@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
])

@php
    $siteName = \App\Models\SiteSetting::value(
        'restaurant_name',
        config('app.name'),
    ) ?? config('app.name');

    $titleText = is_string($title)
        ? trim($title)
        : null;

    $pageTitle = filled($titleText)
        ? (
            stripos($titleText, $siteName) !== false
                ? $titleText
                : "{$titleText} | {$siteName}"
        )
        : $siteName;

    $metaDescription = is_string($description) && filled($description)
        ? trim($description)
        : (
            \App\Models\SiteSetting::value(
                'meta_description',
                'Caribbean food, warm hospitality, and California ease.',
            )
            ?? 'Caribbean food, warm hospitality, and California ease.'
        );

    $canonicalUrl = is_string($canonical) && filled($canonical)
        ? $canonical
        : url()->current();

    $requestedImage = is_string($image) && filled($image)
        ? $image
        : \App\Models\SiteSetting::value(
            'social_share_image_url',
        );

    $imageUrl = null;

    if (is_string($requestedImage) && filled($requestedImage)) {
        $imageUrl = filter_var(
            $requestedImage,
            FILTER_VALIDATE_URL,
        )
            ? $requestedImage
            : url('/'.ltrim($requestedImage, '/'));
    }

    $shouldPreventIndexing = (bool) $noindex
        || ! app()->isProduction();

    $locale = str_replace(
        '-',
        '_',
        str_replace(
            '_',
            '-',
            app()->getLocale(),
        ),
    );
@endphp

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<meta name="theme-color" content="#0c342b">
<meta name="color-scheme" content="light">

<title>{{ $pageTitle }}</title>

<meta
    name="description"
    content="{{ $metaDescription }}">

<meta
    name="robots"
    content="{{ $shouldPreventIndexing
        ? 'noindex, nofollow'
        : 'index, follow, max-image-preview:large' }}">

<link
    rel="canonical"
    href="{{ $canonicalUrl }}">

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:locale" content="{{ $locale }}">

@if ($imageUrl)
    <meta property="og:image" content="{{ $imageUrl }}">
    <meta property="og:image:alt" content="{{ $pageTitle }}">
@endif

<meta
    name="twitter:card"
    content="{{ $imageUrl ? 'summary_large_image' : 'summary' }}">

<meta name="twitter:title" content="{{ $pageTitle }}">

<meta
    name="twitter:description"
    content="{{ $metaDescription }}">

@if ($imageUrl)
    <meta name="twitter:image" content="{{ $imageUrl }}">
@endif
