@props([
    'eyebrow' => null,
    'title',
    'accentTitle' => null,
    'description' => null,
    'image' => null,
    'imageUrl' => null,
    'imageAlt' => '',
    'primaryLabel' => null,
    'primaryUrl' => null,
    'secondaryLabel' => null,
    'secondaryUrl' => null,
    'showJournal' => false,
])

@php
    /*
     * Use the approved responsive homepage artwork.
     *
     * Meaningful marketing copy remains accessible HTML rather than being
     * embedded inside the image.
     */
    $heroAvifUrl = asset('images/heroes/coast-cay-home-hero.avif');
    $heroWebpUrl = asset('images/heroes/coast-cay-home-hero.webp');
    $heroFallbackUrl = asset('images/heroes/coast-cay-home-hero.png');

    /*
     * Define the homepage section pager from sections that will actually
     * render. Journal is included only while published content exists.
     */
    $homeNavigation = [
        [
            'id' => 'home',
            'label' => 'Home',
        ],
        [
            'id' => 'featured',
            'label' => 'Featured dishes',
        ],
        [
            'id' => 'story',
            'label' => 'Our story',
        ],
        [
            'id' => 'menu-explorer',
            'label' => 'Explore menu',
        ],
        [
            'id' => 'gallery-preview',
            'label' => 'Gallery',
        ],
    ];

    if ($showJournal) {
        $homeNavigation[] = [
            'id' => 'journal',
            'label' => 'Journal',
        ];
    }

    $homeNavigation[] = [
        'id' => 'visit',
        'label' => 'Visit',
    ];
@endphp

<section
    id="home"
    data-public-hero
    data-home-panel
    data-home-label="Home"
    aria-labelledby="homepage-hero-title"
    class="home-panel home-hero relative isolate overflow-hidden
        bg-primary-deep text-white">
    <picture class="absolute inset-0 -z-30 block size-full">
        <source
            srcset="{{ $heroAvifUrl }}"
            type="image/avif">

        <source
            srcset="{{ $heroWebpUrl }}"
            type="image/webp">

        <img
            src="{{ $heroFallbackUrl }}"
            alt=""
            width="3840"
            height="2160"
            loading="eager"
            fetchpriority="high"
            decoding="async"
            aria-hidden="true"
            class="size-full object-cover object-[72%_center]
                sm:object-[70%_center] lg:object-center">
    </picture>

    <div
        class="pointer-events-none absolute inset-0 -z-20
            bg-[linear-gradient(90deg,rgba(4,21,17,0.94)_0%,rgba(5,27,22,0.84)_38%,rgba(5,27,22,0.34)_68%,rgba(5,27,22,0.16)_100%)]"
        aria-hidden="true">
    </div>

    <div
        class="pointer-events-none absolute inset-x-0 bottom-0 -z-20 h-56
            bg-gradient-to-t from-primary-deep/75 to-transparent"
        aria-hidden="true">
    </div>

    <div
        class="public-container flex min-h-[100svh] items-center
            pb-24 pt-36 sm:pt-40 lg:pb-28 lg:pt-36">
        <div class="relative z-10 max-w-2xl">
            @if ($eyebrow)
                <p
                    data-home-reveal
                    class="text-xs font-semibold uppercase tracking-[0.24em]
                        text-white/80">
                    {{ $eyebrow }}
                </p>
            @endif

            <h1
                id="homepage-hero-title"
                data-home-reveal
                class="mt-5 max-w-[11ch] font-display text-5xl font-semibold
                    leading-[0.95] tracking-[-0.025em] text-white
                    sm:text-6xl lg:text-7xl xl:text-[5.6rem]">
                {{ $title }}

                @if ($accentTitle)
                    <span class="mt-3 block font-normal italic text-coral">
                        {{ $accentTitle }}
                    </span>
                @endif
            </h1>

            @if ($description)
                <p
                    data-home-reveal
                    class="mt-8 max-w-xl text-base leading-8 text-white/80
                        sm:text-lg">
                    {{ $description }}
                </p>
            @endif

            <div
                data-home-reveal
                class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @if ($primaryLabel && $primaryUrl)
                    <a
                        href="{{ $primaryUrl }}"
                        class="public-button-primary">
                        {{ $primaryLabel }}

                        <span class="ml-2" aria-hidden="true">
                            &rarr;
                        </span>
                    </a>
                @endif

                @if ($secondaryLabel && $secondaryUrl)
                    <a
                        href="{{ $secondaryUrl }}"
                        class="inline-flex min-h-12 items-center justify-center
                            rounded-xl border border-white/55 bg-black/15
                            px-6 py-3 text-center text-xs font-semibold
                            uppercase tracking-[0.14em] text-white
                            backdrop-blur-sm transition duration-300
                            hover:-translate-y-0.5 hover:border-white
                            hover:bg-white hover:text-primary-deep
                            motion-reduce:transform-none">
                        {{ $secondaryLabel }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <x-public.section-pager
        :items="$homeNavigation"
        current="home"
        label="Homepage sections"
        context="home"
        enhancer="shared"
        :snap="false"
        class="home-section-nav" />

    <a
        href="#featured"
        data-home-scroll-link
        class="absolute bottom-7 left-5 z-10 inline-flex items-center gap-3
            text-[0.68rem] font-semibold uppercase tracking-[0.18em]
            text-white/80 transition hover:text-white sm:left-6 lg:left-10">
        <span
            class="flex h-10 w-6 items-start justify-center rounded-full
                border border-white/45 pt-2"
            aria-hidden="true">
            <span class="block size-1.5 rounded-full bg-coral"></span>
        </span>

        Scroll
    </a>
</section>
