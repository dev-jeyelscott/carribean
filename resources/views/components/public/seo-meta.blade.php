@props([
'title' => null,
'description' => null,
])

@php
$siteName = \App\Models\SiteSetting::value(
'restaurant_name',
config('app.name'),
);

$pageTitle = $title
? "{$title} | {$siteName}"
: $siteName;

$metaDescription = $description
?: \App\Models\SiteSetting::value(
'meta_description',
'Caribbean food, warm hospitality, and California ease.',
);
@endphp

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0c342b">
<meta name="color-scheme" content="light">

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">