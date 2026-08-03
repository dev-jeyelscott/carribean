@php
    /*
     * The newest published post receives the featured editorial treatment.
     */
    $featuredPost = $posts->first();

    /*
     * Every remaining visible post belongs to the journal card grid.
     */
    $remainingPosts = $posts->skip(1);
@endphp

<div
    data-public-page-motion
    data-public-editorial-page
    class="journal-page">
    <section
        data-public-fullscreen-hero
        class="public-fullscreen-hero journal-index-hero">
        <div class="public-container relative z-10">
            <div
                class="grid items-center gap-14
                    lg:grid-cols-[minmax(0,1.08fr)_minmax(24rem,0.92fr)]
                    lg:gap-20">
                <div
                    data-public-hero-copy
                    class="max-w-4xl">
                    <p class="public-eyebrow text-brand-sun">
                        Coast &amp; Cay Journal
                    </p>

                    <h1
                        class="mt-6 font-display text-5xl leading-[0.92]
                            text-white sm:text-7xl lg:text-[6.5rem]">
                        Stories carried

                        <span class="block text-brand-sun">
                            from island to table.
                        </span>
                    </h1>

                    <p
                        class="mt-8 max-w-2xl text-base leading-8
                            text-white/72 sm:text-lg">
                        Notes from the kitchen, the people behind the plates,
                        and the Caribbean traditions that shape how we welcome
                        guests.
                    </p>

                    <div class="mt-9 flex flex-wrap gap-4">
                        @if ($featuredPost)
                            <a
                                href="{{ route(
                                    'blog.show',
                                    $featuredPost,
                                ) }}"
                                class="public-button-primary">
                                Read the Latest Story
                            </a>
                        @endif

                        <a
                            href="{{ route('menu') }}"
                            class="public-button-secondary text-white">
                            Explore the Menu
                        </a>
                    </div>

                    <div class="mt-12">
                        <span class="public-hero-scroll-cue">
                            Discover the journal
                        </span>
                    </div>
                </div>

                <div
                    data-public-hero-media
                    data-public-parallax
                    class="relative hidden lg:block"
                    aria-hidden="true">
                    <div class="journal-hero-orbit">
                        <div class="journal-hero-orbit__sun"></div>

                        <div class="journal-hero-orbit__card">
                            <p
                                class="text-[0.62rem] font-semibold uppercase
                                    tracking-[0.2em] text-brand-sun">
                                Field notes
                            </p>

                            <p
                                class="mt-4 font-display text-3xl leading-tight
                                    text-white">
                                Food, place, memory, and an open seat at the
                                table.
                            </p>

                            <div class="mt-6 h-px bg-white/16"></div>

                            <p
                                class="mt-5 text-sm leading-7 text-white/62">
                                Caribbean warmth, told without rushing.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($posts->isEmpty())
        <section
            data-public-reveal-group
            class="journal-feature-section">
            <div class="public-container">
                <div
                    data-public-reveal-item
                    class="mx-auto max-w-2xl rounded-[2.5rem]
                        border border-primary/10 bg-surface p-10 text-center
                        shadow-panel sm:p-14">
                    <p class="public-eyebrow">
                        The journal
                    </p>

                    <h2
                        class="mt-5 font-display text-4xl text-primary-deep
                            sm:text-5xl">
                        Our first story is being prepared.
                    </h2>

                    <p
                        class="mx-auto mt-6 max-w-xl leading-8 text-muted">
                        Until then, explore the menu and discover the dishes
                        currently being served.
                    </p>

                    <a
                        href="{{ route('menu') }}"
                        class="public-button-primary mt-8">
                        Explore the Menu
                    </a>
                </div>
            </div>
        </section>
    @else
        <section
            data-public-reveal-group
            class="journal-feature-section">
            <div class="public-container">
                <article
                    wire:key="featured-blog-post-{{ $featuredPost->id }}"
                    class="journal-feature-card">
                    <div
                        data-public-reveal-item
                        class="journal-feature-media">
                        @if ($featuredPost->image_url)
                            <a
                                href="{{ route(
                                    'blog.show',
                                    $featuredPost,
                                ) }}"
                                class="block size-full">
                                <img
                                    src="{{ $featuredPost->responsiveImageUrl() }}"
                                    @if ($featuredPost->responsiveImageSrcset())
                                        srcset="{{ $featuredPost->responsiveImageSrcset() }}"
                                        sizes="(min-width: 1024px) 56vw, 100vw"
                                    @endif
                                    alt="{{ $featuredPost->image_alt_text
                                        ?: $featuredPost->title }}"
                                    loading="eager"
                                    fetchpriority="high">
                            </a>
                        @else
                            <div class="journal-feature-placeholder">
                                <svg
                                    class="size-20"
                                    viewBox="0 0 32 32"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.25"
                                    aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        d="M16 28V12M16 12c-1-5-6-7-11-5
                                            4 1 7 3 9 6M16 12c2-5 7-7
                                            12-4-5 0-8 2-11 6M16 12c-4-3
                                            -8-3-12 0 5-1 8 1 11 4M17 14
                                            c4-3 8-2 11 1-5-1-8 0-11 3" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    <div
                        data-public-reveal-item
                        class="flex flex-col justify-center p-8
                            sm:p-10 lg:p-14">
                        <p class="public-eyebrow">
                            Featured story
                        </p>

                        @if ($featuredPost->published_at)
                            <time
                                datetime="{{ $featuredPost->published_at->toDateString() }}"
                                class="mt-5 text-xs font-semibold uppercase
                                    tracking-[0.16em] text-muted">
                                {{ $featuredPost->published_at->format(
                                    'F j, Y',
                                ) }}
                            </time>
                        @endif

                        <h2
                            class="mt-5 font-display text-4xl leading-[1.02]
                                text-primary-deep sm:text-5xl">
                            <a
                                href="{{ route(
                                    'blog.show',
                                    $featuredPost,
                                ) }}"
                                class="transition hover:text-coral-deep">
                                {{ $featuredPost->title }}
                            </a>
                        </h2>

                        @if ($featuredPost->excerpt)
                            <p
                                class="mt-6 text-base leading-8 text-muted">
                                {{ $featuredPost->excerpt }}
                            </p>
                        @endif

                        <a
                            href="{{ route(
                                'blog.show',
                                $featuredPost,
                            ) }}"
                            class="mt-8 inline-flex items-center font-semibold
                                text-coral-deep transition
                                hover:text-primary-deep">
                            Read the Story

                            <span
                                class="ml-3"
                                aria-hidden="true">
                                &rarr;
                            </span>
                        </a>
                    </div>
                </article>
            </div>
        </section>

        @if ($remainingPosts->isNotEmpty())
            <section
                data-public-reveal-group
                class="bg-surface-soft py-20 lg:py-28">
                <div class="public-container">
                    <div
                        data-public-reveal-item
                        class="max-w-3xl">
                        <p class="public-eyebrow">
                            More from Coast &amp; Cay
                        </p>

                        <h2
                            class="mt-5 font-display text-4xl
                                text-primary-deep sm:text-6xl">
                            Keep reading.
                        </h2>

                        <p
                            class="mt-5 max-w-2xl leading-8 text-muted">
                            Restaurant news, kitchen stories, hospitality,
                            and the traditions behind the dishes we share.
                        </p>
                    </div>

                    <div
                        class="mt-12 grid gap-7 md:grid-cols-2
                            xl:grid-cols-3">
                        @foreach ($remainingPosts as $post)
                            <article
                                wire:key="blog-post-{{ $post->id }}"
                                wire:transition="blog-post-{{ $post->id }}"
                                data-public-reveal-item
                                class="journal-card">
                                <a
                                    href="{{ route(
                                        'blog.show',
                                        $post,
                                    ) }}"
                                    class="journal-card__media block">
                                    @if ($post->image_url)
                                        <img
                                            src="{{ $post->responsiveImageUrl() }}"
                                            @if ($post->responsiveImageSrcset())
                                                srcset="{{ $post->responsiveImageSrcset() }}"
                                                sizes="(min-width: 1280px)
                                                    33vw,
                                                    (min-width: 768px)
                                                    50vw,
                                                    100vw"
                                            @endif
                                            alt="{{ $post->image_alt_text
                                                ?: $post->title }}"
                                            loading="lazy">
                                    @endif
                                </a>

                                <div class="p-7">
                                    @if ($post->published_at)
                                        <time
                                            datetime="{{ $post->published_at->toDateString() }}"
                                            class="text-xs font-semibold
                                                uppercase tracking-[0.16em]
                                                text-coral-deep">
                                            {{ $post->published_at->format(
                                                'F j, Y',
                                            ) }}
                                        </time>
                                    @endif

                                    <h3
                                        class="mt-4 font-display text-3xl
                                            leading-tight text-primary-deep">
                                        <a
                                            href="{{ route(
                                                'blog.show',
                                                $post,
                                            ) }}"
                                            class="transition
                                                hover:text-coral-deep">
                                            {{ $post->title }}
                                        </a>
                                    </h3>

                                    @if ($post->excerpt)
                                        <p
                                            class="mt-5 text-sm leading-7
                                                text-muted">
                                            {{ $post->excerpt }}
                                        </p>
                                    @endif

                                    <a
                                        href="{{ route(
                                            'blog.show',
                                            $post,
                                        ) }}"
                                        class="mt-6 inline-flex font-semibold
                                            text-coral-deep transition
                                            hover:text-primary-deep">
                                        Read the Story

                                        <span
                                            class="ml-2"
                                            aria-hidden="true">
                                            &rarr;
                                        </span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($hasMore)
                        <div
                            wire:key="blog-load-more-{{ $visiblePosts }}"
                            wire:intersect.once.margin.500px="loadMore"
                            class="mt-12 flex min-h-20 items-center
                                justify-center"
                            aria-live="polite">
                            <div
                                wire:loading.remove
                                wire:target="loadMore"
                                class="flex items-center gap-3 text-xs
                                    font-semibold uppercase
                                    tracking-[0.16em] text-muted">
                                <span
                                    class="size-2 rounded-full
                                        bg-coral-deep"
                                    aria-hidden="true">
                                </span>

                                More stories below
                            </div>

                            <div
                                wire:loading.flex
                                wire:target="loadMore"
                                class="items-center gap-3 text-sm
                                    font-semibold text-primary-deep"
                                role="status">
                                <svg
                                    class="size-5 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true">
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4">
                                    </circle>

                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0
                                            0 5.373 0 12h4zm2 5.291A7.962
                                            7.962 0 014 12H0c0 3.042 1.135
                                            5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>

                                <span>
                                    Loading more stories…
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    @endif
</div>
