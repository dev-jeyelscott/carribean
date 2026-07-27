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
])

@php
    /*
     * Resolve the approved static homepage artwork first.
     *
     * The image remains a decorative marketing background while all meaningful
     * headline, description, and action content stays as accessible HTML.
     */
    $heroAvifUrl = asset('images/heroes/coast-cay-home-hero.avif');
    $heroWebpUrl = asset('images/heroes/coast-cay-home-hero.webp');
    $heroFallbackUrl = asset('images/heroes/coast-cay-home-hero.png');
@endphp

<section
    data-public-hero
    class="relative isolate min-h-[42rem] overflow-hidden bg-canvas
        sm:min-h-[46rem] lg:min-h-[48rem]"
>
    {{-- 
        The hero artwork is loaded eagerly because it is the primary
        above-the-fold visual and likely Largest Contentful Paint candidate.
    --}}
    <picture class="absolute inset-0 -z-20 block size-full">
        <source
            srcset="{{ $heroAvifUrl }}"
            type="image/avif"
        >

        <source
            srcset="{{ $heroWebpUrl }}"
            type="image/webp"
        >

        <img
            src="{{ $heroFallbackUrl }}"
            alt=""
            width="3840"
            height="2160"
            loading="eager"
            fetchpriority="high"
            decoding="async"
            aria-hidden="true"
            class="size-full object-cover
                object-[72%_center]
                sm:object-[70%_center]
                lg:object-center"
        >
    </picture>

    {{--
        Preserve strong text contrast without obscuring the food presentation.
        The gradient becomes lighter toward the right side of the composition.
    --}}
    <div
        class="pointer-events-none absolute inset-0 -z-10
            bg-gradient-to-r
            from-canvas
            via-canvas/95
            to-canvas/10
            sm:via-canvas/85
            lg:via-canvas/60
            lg:to-transparent"
        aria-hidden="true"
    ></div>

    {{--
        Mobile receives a subtle lower wash so the CTAs remain readable when
        the responsive crop brings food closer to the content.
    --}}
    <div
        class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-48
            bg-gradient-to-t from-canvas/75 to-transparent lg:hidden"
        aria-hidden="true"
    ></div>

    <div
        class="public-container flex min-h-[42rem] items-center
            pb-24 pt-16
            sm:min-h-[46rem] sm:pb-28 sm:pt-20
            lg:min-h-[48rem] lg:pb-28 lg:pt-24"
    >
        <div
            data-gsap="hero-content"
            class="relative z-10 max-w-xl"
        >
            @if ($eyebrow)
                <p
                    data-gsap-reveal
                    class="public-eyebrow"
                >
                    {{ $eyebrow }}
                </p>
            @endif

            <h1
                data-gsap-reveal
                class="mt-5 max-w-[12ch] font-display text-5xl font-semibold
                    leading-[0.96] tracking-[-0.025em] text-ink
                    sm:text-6xl
                    lg:text-7xl
                    xl:text-[5.25rem]"
            >
                {{ $title }}

                @if ($accentTitle)
                    <span
                        class="relative mt-3 block font-normal italic
                            text-coral"
                    >
                        {{ $accentTitle }}

                        <span
                            class="absolute -bottom-3 left-1 h-1 w-52
                                -rotate-2 rounded-full bg-ocean
                                sm:w-64"
                            aria-hidden="true"
                        ></span>
                    </span>
                @endif
            </h1>

            @if ($description)
                <p
                    data-gsap-reveal
                    class="mt-9 max-w-lg text-base leading-8 text-muted
                        sm:text-lg"
                >
                    {{ $description }}
                </p>
            @endif

            <div
                data-gsap-reveal
                class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap"
            >
                @if ($primaryLabel && $primaryUrl)
                    <a
                        href="{{ $primaryUrl }}"
                        class="public-button-primary"
                    >
                        {{ $primaryLabel }}

                        <span class="ml-2" aria-hidden="true">
                            &rarr;
                        </span>
                    </a>
                @endif

                @if ($secondaryLabel && $secondaryUrl)
                    <a
                        href="{{ $secondaryUrl }}"
                        class="public-button-secondary text-primary"
                    >
                        {{ $secondaryLabel }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div
        class="public-torn-edge"
        aria-hidden="true"
    ></div>
</section>
