@props([
'item',
'variant' => 'default',
])

@php
$menuCategory = $item->relationLoaded('menuCategory')
? $item->menuCategory
: null;

$itemUrl = route('menu-items.show', $item);
$formattedPrice = $item->formattedPrice();
$imageAlt = $item->image_alt_text ?: $item->name;
@endphp

@if ($variant === 'luxury')
<article
    data-gsap="card"
    data-menu-motion="card"
    class="group">
    <a
        href="{{ $itemUrl }}"
        class="block rounded-island focus-visible:outline-none
                focus-visible:ring-2 focus-visible:ring-brand-sun
                focus-visible:ring-offset-4 focus-visible:ring-offset-brand-palm-dark">
        <div
            data-menu-motion="card-image"
            class="relative aspect-[4/5] overflow-hidden rounded-island
                    bg-brand-palm shadow-island-dark">
            @if ($item->image_url)
            <x-public.responsive-image
                :image="$item"
                :alt="$imageAlt"
                variant="card"
                sizes="(min-width: 1024px) 30vw, (min-width: 768px) 45vw, 100vw"
                width="720"
                height="900"
                img-class="h-full w-full object-cover transition duration-700 group-hover:scale-105" />
            @else
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(242,199,107,0.25),transparent_32%),linear-gradient(145deg,#206f7c,#0c342b)]"></div>

            <div
                class="absolute inset-x-6 bottom-6 border-t border-white/20
                            pt-4 text-xs uppercase tracking-[0.22em] text-white/65">
                Image coming soon
            </div>
            @endif

            <div
                class="absolute inset-0 bg-gradient-to-t
                        from-brand-palm-dark/55 via-transparent to-transparent
                        transition duration-500 group-hover:from-brand-palm-dark/30"></div>

            <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                @unless ($item->is_available)
                <span
                    class="rounded-full bg-brand-cream px-3 py-1
                                text-[0.65rem] font-semibold uppercase
                                tracking-[0.15em] text-brand-forest">
                    Unavailable
                </span>
                @endunless

                @if ($item->is_available && ! $item->is_purchasable)
                <span
                    class="rounded-full bg-brand-sand-soft px-3 py-1
                                text-[0.65rem] font-semibold uppercase
                                tracking-[0.15em] text-brand-forest">
                    Dine-in only
                </span>
                @endif
            </div>
        </div>

        <div data-menu-motion="card-copy" class="pt-6">
            @if ($menuCategory)
            <p
                class="text-[0.68rem] font-semibold uppercase
                            tracking-[0.22em] text-brand-coral">
                {{ $menuCategory->name }}
            </p>
            @endif

            <div class="mt-3 flex items-start justify-between gap-5">
                <h3
                    class="font-display text-2xl leading-tight
                            text-brand-cream transition
                            group-hover:text-brand-sun">
                    {{ $item->name }}
                </h3>

                @if ($formattedPrice)
                <p class="shrink-0 text-sm font-semibold text-brand-sun">
                    {{ $formattedPrice }}
                </p>
                @endif
            </div>

            @if ($item->description)
            <p class="mt-3 text-sm leading-7 text-brand-cream/65">
                {{ $item->description }}
            </p>
            @endif

            <p
                class="mt-5 text-xs font-semibold uppercase
                        tracking-[0.18em] text-brand-sun">
                View details
            </p>
        </div>
    </a>
</article>
@else
<article
    class="rounded-island border border-brand-palm/10
            bg-white p-5 shadow-island">
    <a
        href="{{ $itemUrl }}"
        class="block rounded-[1.1rem] focus-visible:outline-none
                focus-visible:ring-2 focus-visible:ring-brand-palm">
        @if ($item->image_url)
        <x-public.responsive-image
            :image="$item"
            :alt="$imageAlt"
            variant="card"
            sizes="(min-width: 1024px) 30vw, (min-width: 768px) 45vw, 100vw"
            width="720"
            height="480"
            img-class="mb-5 h-48 w-full rounded-[1.1rem] object-cover" />
        @endif

        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="font-display text-xl text-brand-forest">
                    {{ $item->name }}
                </h3>

                @if ($menuCategory)
                <p
                    class="mt-1 text-xs font-semibold uppercase
                                tracking-[0.18em] text-brand-coral-dark">
                    {{ $menuCategory->name }}
                </p>
                @endif
            </div>

            @if ($formattedPrice)
            <p class="shrink-0 font-semibold text-brand-palm">
                {{ $formattedPrice }}
            </p>
            @endif
        </div>

        @if ($item->description)
        <p class="mt-3 text-sm leading-6 text-brand-muted">
            {{ $item->description }}
        </p>
        @endif

        <div class="mt-5 flex flex-wrap items-center gap-2">
            @unless ($item->is_available)
            <span
                class="rounded-full bg-brand-sand-soft px-3 py-1
                            text-xs font-semibold text-brand-forest">
                Currently unavailable
            </span>
            @endunless

            @if ($item->is_available && ! $item->is_purchasable)
            <span
                class="rounded-full bg-brand-cream px-3 py-1
                            text-xs font-semibold text-brand-forest">
                Display only
            </span>
            @endif
        </div>
    </a>
</article>
@endif