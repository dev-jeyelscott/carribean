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

<section
    data-public-hero
    class="public-paper-texture relative isolate overflow-hidden bg-canvas">
    <div
        class="pointer-events-none absolute -left-28 top-20 -z-10
            size-72 rounded-full border border-primary/10"
        aria-hidden="true"></div>

    <div
        class="pointer-events-none absolute right-[42%] top-20 -z-10
            size-48 rounded-full bg-sun/10 blur-3xl"
        aria-hidden="true"></div>

    <div
        class="public-container grid min-h-[43rem] items-center gap-12
            pb-20 pt-14 lg:grid-cols-[0.82fr_1.18fr] lg:gap-8 lg:py-20">
        <div data-gsap="hero-content" class="relative z-10 max-w-xl">
            @if ($eyebrow)
                <p
                    data-gsap-reveal
                    class="public-eyebrow">
                    {{ $eyebrow }}
                </p>
            @endif

            <h1
                data-gsap-reveal
                class="mt-5 font-display text-5xl font-semibold
                    leading-[0.98] text-ink sm:text-6xl lg:text-7xl">
                {{ $title }}

                @if ($accentTitle)
                    <span
                        class="relative mt-3 block font-normal italic
                            text-coral">
                        {{ $accentTitle }}

                        <span
                            class="absolute -bottom-3 left-1 h-1 w-52
                                -rotate-2 rounded-full bg-ocean sm:w-64"
                            aria-hidden="true"></span>
                    </span>
                @endif
            </h1>

            @if ($description)
                <p
                    data-gsap-reveal
                    class="mt-9 max-w-lg text-base leading-8 text-muted">
                    {{ $description }}
                </p>
            @endif

            <div
                data-gsap-reveal
                class="mt-8 flex flex-col gap-3 sm:flex-row">
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
                        class="public-button-secondary text-primary">
                        {{ $secondaryLabel }}
                    </a>
                @endif
            </div>
        </div>

        <div
            data-gsap="hero-image"
            class="relative mx-auto w-full max-w-[49rem] lg:ml-auto">
            <div
                class="absolute -right-8 -top-4 size-28 rounded-full
                    border border-coral/15"
                aria-hidden="true"></div>

            <div
                class="absolute -bottom-4 left-0 size-20 rounded-full
                    bg-primary/8"
                aria-hidden="true"></div>

            <div
                class="relative aspect-[1.14/1] overflow-hidden
                    rounded-[48%_52%_46%_54%/48%_44%_56%_52%]
                    border-[0.65rem] border-surface bg-surface-soft
                    shadow-elevated">
                @if ($image?->image_url || $imageUrl)
                    <x-public.responsive-image
                        :image="$image"
                        :fallback-url="$imageUrl"
                        :alt="$imageAlt"
                        variant="hero"
                        sizes="(min-width: 1024px) 58vw, 100vw"
                        width="1400"
                        height="1100"
                        loading="eager"
                        fetchpriority="high"
                        img-class="h-full w-full object-cover object-center" />
                @else
                    <div
                        class="absolute inset-0
                            bg-[radial-gradient(circle_at_35%_24%,rgba(242,199,107,0.40),transparent_28%),linear-gradient(145deg,#206f7c,#0c342b)]">
                    </div>
                @endif
            </div>

            <div
                class="absolute -left-3 top-[22%] flex size-12
                    items-center justify-center rounded-full bg-coral
                    text-white shadow-card"
                aria-hidden="true">
                <svg
                    class="size-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7">
                    <path
                        stroke-linecap="round"
                        d="M12 20c4-3 6-7 6-11-4 0-8 2-10 6-1 2 0 4 4 5Zm0 0c-1-5 0-9 4-13" />
                </svg>
            </div>
        </div>
    </div>

    <div class="public-torn-edge" aria-hidden="true"></div>
</section>
