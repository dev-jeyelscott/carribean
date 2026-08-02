@props([
    'image',
    'variant' => 'default',
    'index' => null,
    'layout' => 'square',
])

@php
    /*
     * Preserve the existing reusable variants while introducing the collage
     * card used by the one-section Gallery page.
     */
    $isCollage = $variant === 'collage';
    $isContactSheet = $variant === 'contact-sheet';
    $isEditorial = $variant === 'editorial';

    $displayTitle = $image->title ?: 'Coast & Cay moment';

    $displayCategory = filled($image->category)
        ? str($image->category)->headline()->toString()
        : 'Coast & Cay';

    $displayIndex = $index ?? 1;
    $largeImageUrl = $image->responsiveImageUrl('large');
    $sourceSet = $image->responsiveImageSrcset();
@endphp

@if ($isCollage)
    <article
        data-gallery-item
        data-gallery-layout="{{ $layout }}"
        data-gallery-image-state="loading"
        {{ $attributes->class(['gallery-collage-card']) }}
    >
        @if ($largeImageUrl)
            <a
                data-gallery-open
                data-gallery-src="{{ $largeImageUrl }}"
                data-gallery-srcset="{{ $sourceSet }}"
                data-gallery-alt="{{ $image->alt_text ?: $displayTitle }}"
                data-gallery-title="{{ $displayTitle }}"
                data-gallery-category="{{ $displayCategory }}"
                data-gallery-index="{{ $displayIndex }}"
                href="{{ $largeImageUrl }}"
                class="gallery-collage-card__button"
                aria-label="Open {{ $displayTitle }} in fullscreen view"
            >
                <x-public.responsive-image
                    :image="$image"
                    :alt="$image->alt_text ?: $displayTitle"
                    variant="large"
                    sizes="(min-width: 1280px) 42vw,
                        (min-width: 768px) 50vw,
                        100vw"
                    width="1200"
                    height="1200"
                    img-class="gallery-collage-card__image"
                />

                <span
                    class="gallery-collage-card__veil"
                    aria-hidden="true"
                ></span>

                <span
                    class="gallery-collage-card__topline"
                    aria-hidden="true"
                >
                    <span class="gallery-collage-card__number">
                        {{ str_pad(
                            (string) $displayIndex,
                            2,
                            '0',
                            STR_PAD_LEFT,
                        ) }}
                    </span>

                    <span class="gallery-collage-card__category">
                        {{ $displayCategory }}
                    </span>
                </span>

                <span class="gallery-collage-card__content">
                    <span class="gallery-collage-card__title">
                        {{ $displayTitle }}
                    </span>

                    <span
                        class="gallery-collage-card__open"
                        aria-hidden="true"
                    >
                        View
                        <span>↗</span>
                    </span>
                </span>
            </a>
        @else
            <div class="gallery-collage-card__fallback">
                <span>{{ $displayTitle }}</span>
            </div>
        @endif
    </article>
@elseif ($isContactSheet)
    <article
        data-gallery-item
        {{ $attributes->class(['gallery-contact-card']) }}
    >
        @if ($largeImageUrl)
            <a
                data-gallery-open
                data-gallery-src="{{ $largeImageUrl }}"
                data-gallery-srcset="{{ $sourceSet }}"
                data-gallery-alt="{{ $image->alt_text ?: $displayTitle }}"
                data-gallery-title="{{ $displayTitle }}"
                data-gallery-category="{{ $displayCategory }}"
                href="{{ $largeImageUrl }}"
                class="gallery-contact-card__button"
                aria-label="Open {{ $displayTitle }} in the gallery viewer"
            >
                <x-public.responsive-image
                    :image="$image"
                    :alt="$image->alt_text ?: $displayTitle"
                    variant="large"
                    sizes="(min-width: 1280px) 38vw,
                        (min-width: 768px) 48vw,
                        100vw"
                    width="1200"
                    height="900"
                    img-class="gallery-contact-card__image"
                />

                <span
                    class="gallery-contact-card__veil"
                    aria-hidden="true"
                ></span>

                <span
                    class="gallery-contact-card__meta"
                    aria-hidden="true"
                >
                    <span class="gallery-contact-card__number">
                        {{ str_pad(
                            (string) $displayIndex,
                            2,
                            '0',
                            STR_PAD_LEFT,
                        ) }}
                    </span>

                    <span class="gallery-contact-card__category">
                        {{ $displayCategory }}
                    </span>
                </span>

                <span class="gallery-contact-card__content">
                    <span class="gallery-contact-card__title">
                        {{ $displayTitle }}
                    </span>

                    <span
                        class="gallery-contact-card__open"
                        aria-hidden="true"
                    >
                        Open frame ↗
                    </span>
                </span>
            </a>
        @else
            <div class="gallery-contact-card__fallback">
                <span>{{ $displayTitle }}</span>
            </div>
        @endif
    </article>
@elseif ($isEditorial)
    <article
        data-gsap="tile"
        {{ $attributes->class([
            'group relative isolate min-h-72 overflow-hidden
                bg-brand-ink-soft',
        ]) }}
    >
        @if ($image->image_url)
            <x-public.responsive-image
                :image="$image"
                :alt="$image->alt_text
                    ?: $image->title
                    ?: 'Restaurant gallery image'"
                variant="large"
                sizes="(min-width: 1024px) 66vw,
                    (min-width: 768px) 50vw,
                    100vw"
                width="1200"
                height="900"
                img-class="absolute inset-0 -z-20 size-full object-cover
                    transition duration-700 ease-out
                    group-hover:scale-105"
            />
        @else
            <div
                class="absolute inset-0 -z-20
                    bg-[radial-gradient(circle_at_28%_22%,
                    rgba(201,164,93,0.3),transparent_32%),
                    linear-gradient(145deg,#4d4437,#171916)]"
            ></div>
        @endif

        <div
            class="absolute inset-0 -z-10 bg-gradient-to-t
                from-brand-ink/95 via-brand-ink/10 to-transparent
                opacity-85 transition duration-500
                group-hover:opacity-100"
        ></div>

        <div
            class="absolute inset-0 -z-10 ring-1 ring-inset
                ring-white/10 transition duration-500
                group-hover:ring-brand-gold/55"
        ></div>

        @if ($image->title || $image->category)
            <div
                data-gsap-reveal
                class="absolute inset-x-0 bottom-0 p-6 sm:p-7"
            >
                @if ($image->category)
                    <p
                        class="text-[0.65rem] font-semibold uppercase
                            tracking-[0.26em] text-brand-gold"
                    >
                        {{ $image->category }}
                    </p>
                @endif

                @if ($image->title)
                    <h3
                        class="mt-2 max-w-xl font-display text-2xl
                            leading-tight text-white sm:text-3xl"
                    >
                        {{ $image->title }}
                    </h3>
                @endif
            </div>
        @endif
    </article>
@else
    <article
        {{ $attributes->class([
            'group overflow-hidden rounded-2xl border border-white/10
                bg-white/[0.03]',
        ]) }}
    >
        @if ($image->image_url)
            <div class="overflow-hidden">
                <x-public.responsive-image
                    :image="$image"
                    :alt="$image->alt_text
                        ?: $image->title
                        ?: 'Restaurant gallery image'"
                    variant="card"
                    sizes="(min-width: 1024px) 30vw,
                        (min-width: 768px) 45vw,
                        100vw"
                    width="960"
                    height="720"
                    img-class="h-64 w-full object-cover transition
                        duration-500 group-hover:scale-105"
                />
            </div>
        @endif

        @if ($image->title || $image->category)
            <div class="p-4">
                @if ($image->title)
                    <h3 class="font-semibold text-white">
                        {{ $image->title }}
                    </h3>
                @endif

                @if ($image->category)
                    <p
                        class="mt-1 text-xs uppercase tracking-widest
                            text-amber-300"
                    >
                        {{ $image->category }}
                    </p>
                @endif
            </div>
        @endif
    </article>
@endif
