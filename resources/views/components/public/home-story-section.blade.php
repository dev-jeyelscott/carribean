@props([
    'storyCopy',
    'image' => null,
])

<section
    id="story"
    data-home-panel
    data-home-label="Our story"
    aria-labelledby="home-story-heading"
    class="home-panel home-story-stage relative isolate overflow-hidden
        bg-primary-deep text-white">
    <div
        data-home-story-media
        class="home-story-media absolute inset-0 -z-30 overflow-hidden">
        @if ($image?->image_url)
            <x-public.responsive-image
                :image="$image"
                :alt="$image->alt_text
                    ?: $image->title
                    ?: 'Warm Coast and Cay restaurant dining room'"
                variant="hero"
                sizes="100vw"
                width="2000"
                height="1400"
                img-class="size-full object-cover object-center" />
        @else
            <div
                class="size-full
                    bg-[radial-gradient(circle_at_70%_24%,rgb(242_199_107_/_24%),transparent_25%),linear-gradient(135deg,#206f7c,#0c342b_68%)]"
                aria-hidden="true">
            </div>
        @endif
    </div>

    <div
        class="public-container relative z-10 grid min-h-[100svh]
            items-center gap-8 py-24 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.58fr)]
            lg:gap-12">
        <article
            data-home-reveal
            class="home-story-card max-w-2xl rounded-[2.25rem]
                border border-white/20 bg-canvas/94 p-7 text-ink
                shadow-elevated backdrop-blur-xl sm:p-9 lg:p-11">
            <p class="public-eyebrow">
                Our story
            </p>

            <h2
                id="home-story-heading"
                class="mt-5 max-w-[11ch] font-display text-5xl
                    leading-[0.96] text-primary-deep sm:text-6xl">
                Rooted in the Caribbean. At home on the coast.
            </h2>

            <div
                class="mt-6 flex items-center gap-3 text-coral"
                aria-hidden="true">
                <span class="h-px w-16 bg-current"></span>

                <svg
                    class="size-5"
                    viewBox="0 0 32 32"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5">
                    <path
                        stroke-linecap="round"
                        d="M16 28V12M16 12c-1-5-6-7-11-5 4 1 7 3 9 6M16 12c2-5 7-7 12-4-5 0-8 2-11 6M16 12c-4-3-8-3-12 0 5-1 8 1 11 4M17 14c4-3 8-2 11 1-5-1-8 0-11 3" />
                </svg>
            </div>

            <p class="mt-6 max-w-xl text-base leading-8 text-muted">
                {{ $storyCopy }}
            </p>

            <blockquote
                class="mt-7 border-l-2 border-coral pl-5 font-display
                    text-2xl italic leading-snug text-primary">
                “Food should carry a sense of place, but always make room for
                everyone at the table.”
            </blockquote>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a
                    href="{{ route('about') }}"
                    class="public-button-primary">
                    Discover Our Story
                </a>

                <a
                    href="{{ route('gallery') }}"
                    class="public-button-secondary text-primary">
                    View the Gallery
                </a>
            </div>
        </article>

        <div
            class="grid gap-3 self-end pb-2 sm:grid-cols-3
                lg:grid-cols-1 lg:justify-self-end">
            <article
                data-home-reveal
                class="home-story-note">
                <span class="home-story-note__number">
                    01
                </span>

                <div>
                    <h3 class="font-display text-xl text-white">
                        Good food.
                    </h3>

                    <p class="mt-1 text-xs leading-5 text-white/80">
                        Layered flavor prepared with character and care.
                    </p>
                </div>
            </article>

            <article
                data-home-reveal
                class="home-story-note">
                <span class="home-story-note__number">
                    02
                </span>

                <div>
                    <h3 class="font-display text-xl text-white">
                        Good people.
                    </h3>

                    <p class="mt-1 text-xs leading-5 text-white/80">
                        Hospitality that feels personal and genuine.
                    </p>
                </div>
            </article>

            <article
                data-home-reveal
                class="home-story-note">
                <span class="home-story-note__number">
                    03
                </span>

                <div>
                    <h3 class="font-display text-xl text-white">
                        Good energy.
                    </h3>

                    <p class="mt-1 text-xs leading-5 text-white/80">
                        Caribbean warmth with relaxed California ease.
                    </p>
                </div>
            </article>
        </div>
    </div>
</section>
