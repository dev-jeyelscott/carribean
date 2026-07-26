@props([
'eyebrow' => null,
'title',
'description' => null,
'image' => null,
'imageUrl' => null,
'imageAlt' => '',
'primaryLabel' => null,
'primaryUrl' => null,
'secondaryLabel' => null,
'secondaryUrl' => null,
])

<section
    data-public-hero
    class="public-hero-viewport relative isolate flex items-center overflow-hidden bg-brand-palm-dark">
    <div data-gsap="hero-image" class="absolute inset-0 -z-30">
        <x-public.responsive-image
            :image="$image"
            :fallback-url="$imageUrl"
            :alt="$imageAlt"
            variant="hero"
            sizes="100vw"
            width="1920"
            height="1280"
            loading="eager"
            fetchpriority="high"
            img-class="h-full w-full object-cover object-center" />
    </div>

    <div
        class="absolute inset-0 -z-20 bg-[linear-gradient(to_bottom,rgba(8,38,31,0.60),rgba(8,38,31,0.40)_45%,rgba(8,38,31,0.90))]"></div>

    <div
        class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_78%_24%,rgba(242,199,107,0.28),transparent_28%),linear-gradient(to_right,rgba(12,52,43,0.92),rgba(12,52,43,0.45)_58%,transparent)]"></div>

    <div
        class="absolute -right-28 top-32 -z-10 size-80 rounded-full border border-brand-sun/25"
        aria-hidden="true"></div>

    <div class="public-container pb-16 pt-32 sm:pb-24 sm:pt-40 lg:pb-28 lg:pt-48">
        <div data-gsap="hero-content" class="max-w-5xl">
            @if ($eyebrow)
            <p
                data-gsap-reveal
                class="text-xs font-semibold uppercase tracking-[0.34em] text-brand-sun sm:text-sm">
                {{ $eyebrow }}
            </p>
            @endif

            <h1
                data-gsap-reveal
                class="mt-6 max-w-5xl font-display text-5xl leading-[0.94]
                    text-white sm:text-7xl lg:text-[6.5rem]">
                {{ $title }}
            </h1>

            @if ($description)
            <p
                data-gsap-reveal
                class="mt-7 max-w-2xl text-base leading-8 text-white/80 sm:text-lg">
                {{ $description }}
            </p>
            @endif

            @if ($primaryLabel || $secondaryLabel)
            <div
                data-gsap-reveal
                class="mt-10 flex flex-col gap-4 sm:flex-row">
                @if ($primaryLabel && $primaryUrl)
                <a
                    href="{{ $primaryUrl }}"
                    class="public-button-primary">
                    {{ $primaryLabel }}
                </a>
                @endif

                @if ($secondaryLabel && $secondaryUrl)
                <a
                    href="{{ $secondaryUrl }}"
                    class="public-button-secondary text-white">
                    {{ $secondaryLabel }}
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>

    <a
        href="#restaurant-story"
        class="absolute bottom-8 left-1/2 hidden -translate-x-1/2
            flex-col items-center gap-3 text-[0.65rem] font-semibold uppercase
            tracking-[0.28em] text-white/65 transition
            hover:text-brand-sun md:flex">
        Discover

        <span
            data-gsap="discover-line"
            class="h-12 w-px bg-gradient-to-b from-brand-sun/80 to-transparent"
            aria-hidden="true"></span>
    </a>
</section>