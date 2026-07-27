@props([
    'item',
])

@php
$menuCategory = $item->relationLoaded('menuCategory')
    ? $item->menuCategory
    : null;

$itemUrl = route('menu-items.show', $item);
$formattedPrice = $item->formattedPrice();
$imageAlt = $item->image_alt_text ?: $item->name;
@endphp

<article
    data-reveal
    class="group overflow-hidden rounded-card border border-line bg-surface
        shadow-card transition duration-300 hover:-translate-y-1
        hover:shadow-panel motion-reduce:transform-none">
    <a
        href="{{ $itemUrl }}"
        class="relative block aspect-[1.45/1] overflow-hidden
            bg-surface-soft">
        @if ($item->image_url)
            <x-public.responsive-image
                :image="$item"
                :alt="$imageAlt"
                variant="card"
                sizes="(min-width: 1280px) 22vw, (min-width: 640px) 45vw, 100vw"
                width="720"
                height="500"
                img-class="h-full w-full object-cover transition duration-500
                    ease-island group-hover:scale-105
                    motion-reduce:transform-none" />
        @else
            <div
                class="absolute inset-0
                    bg-[radial-gradient(circle_at_30%_20%,rgba(242,199,107,0.34),transparent_30%),linear-gradient(145deg,#206f7c,#0c342b)]">
            </div>
        @endif

        @unless ($item->is_available)
            <span
                class="absolute left-3 top-3 rounded-full bg-canvas/95
                    px-3 py-1 text-[0.65rem] font-semibold uppercase
                    tracking-[0.12em] text-primary shadow-card">
                Unavailable
            </span>
        @endunless
    </a>

    <div class="flex min-h-52 flex-col p-5">
        @if ($menuCategory)
            <p
                class="text-[0.65rem] font-semibold uppercase
                    tracking-[0.18em] text-coral">
                {{ $menuCategory->name }}
            </p>
        @endif

        <h3 class="mt-2 font-display text-xl leading-tight text-ink">
            <a
                href="{{ $itemUrl }}"
                class="transition hover:text-coral">
                {{ $item->name }}
            </a>
        </h3>

        @if ($item->description)
            <p class="mt-3 line-clamp-3 text-sm leading-6 text-muted">
                {{ $item->description }}
            </p>
        @endif

        <div class="mt-auto flex items-end justify-between gap-4 pt-5">
            @if ($formattedPrice)
                <p class="font-semibold text-coral-deep">
                    {{ $formattedPrice }}
                </p>
            @endif

            <a
                href="{{ $itemUrl }}"
                class="inline-flex size-9 shrink-0 items-center
                    justify-center rounded-full border border-coral/45
                    text-xl leading-none text-coral transition
                    hover:bg-coral hover:text-white"
                aria-label="View {{ $item->name }}">
                +
            </a>
        </div>
    </div>
</article>
