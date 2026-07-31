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