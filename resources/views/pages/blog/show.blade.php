@php
    $imageUrl = $blogPost->responsiveImageUrl(
        \App\Services\ResponsiveImageManager::VARIANT_HERO,
    );

    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'mainEntityOfPage' => route(
            'blog.show',
            $blogPost,
        ),
        'headline' => $blogPost->title,
        'description' => $blogPost->meta_description
            ?: $blogPost->excerpt,
        'datePublished' => $blogPost->published_at?->toIso8601String(),
        'dateModified' => $blogPost->updated_at?->toIso8601String(),
        'author' => [
            '@type' => 'Organization',
            'name' => $settings['restaurant_name']
                ?? config('app.name'),
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => $settings['restaurant_name']
                ?? config('app.name'),
        ],
    ];

    if ($imageUrl) {
        $structuredData['image'] = $imageUrl;
    }
@endphp

<x-layouts.public
    :title="$blogPost->meta_title ?: $blogPost->title"
    :description="$blogPost->meta_description ?: $blogPost->excerpt"
    :canonical="route('blog.show', $blogPost)"
    :image="$imageUrl"
    type="article"
    :structured-data="$structuredData"
    :header-overlay="true">
    <article
        data-public-page-motion
        data-public-editorial-page>
        <header
            data-public-fullscreen-hero
            class="public-fullscreen-hero journal-article-hero">
            @if ($imageUrl)
                <img
                    data-public-hero-media
                    data-public-parallax
                    src="{{ $imageUrl }}"
                    @if ($blogPost->responsiveImageSrcset())
                        srcset="{{ $blogPost->responsiveImageSrcset() }}"
                        sizes="100vw"
                    @endif
                    alt="{{ $blogPost->image_alt_text
                        ?: $blogPost->title }}"
                    fetchpriority="high"
                    class="journal-article-hero__image">

                <div
                    class="journal-article-hero__overlay"
                    aria-hidden="true">
                </div>
            @endif

            <div class="public-container relative z-10">
                <div
                    data-public-hero-copy
                    class="max-w-5xl">
                    <a
                        href="{{ route('blog.index') }}"
                        class="inline-flex items-center text-xs
                            font-semibold uppercase tracking-[0.18em]
                            text-brand-sun transition hover:text-white">
                        <span
                            class="mr-3"
                            aria-hidden="true">
                            &larr;
                        </span>

                        Back to Journal
                    </a>

                    @if ($blogPost->published_at)
                        <time
                            datetime="{{ $blogPost->published_at->toDateString() }}"
                            class="mt-10 block text-xs font-semibold
                                uppercase tracking-[0.2em]
                                text-white/62">
                            {{ $blogPost->published_at->format(
                                'F j, Y',
                            ) }}
                        </time>
                    @endif

                    <h1
                        class="mt-6 max-w-5xl font-display text-5xl
                            leading-[0.92] text-white sm:text-7xl
                            lg:text-[6.25rem]">
                        {{ $blogPost->title }}
                    </h1>

                    @if ($blogPost->excerpt)
                        <p
                            class="mt-8 max-w-3xl text-base leading-8
                                text-white/76 sm:text-xl sm:leading-9">
                            {{ $blogPost->excerpt }}
                        </p>
                    @endif

                    <div class="mt-12">
                        <span class="public-hero-scroll-cue">
                            Continue reading
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <section
            data-public-reveal-group
            class="journal-article-shell py-20 lg:py-28">
            <div class="public-container">
                <div
                    class="mx-auto grid max-w-6xl gap-10
                        lg:grid-cols-[14rem_minmax(0,48rem)]
                        lg:items-start lg:justify-center lg:gap-16">
                    <aside
                        data-public-reveal-item
                        class="lg:sticky lg:top-28">
                        <p class="public-eyebrow">
                            Coast &amp; Cay
                        </p>

                        <p
                            class="mt-5 text-sm leading-7
                                text-muted">
                            Stories about the cooking, traditions, and
                            hospitality that shape our Caribbean table.
                        </p>

                        <div class="mt-7 h-px bg-line"></div>

                        @if ($blogPost->published_at)
                            <dl class="mt-7 space-y-5 text-sm">
                                <div>
                                    <dt class="text-muted">
                                        Published
                                    </dt>

                                    <dd
                                        class="mt-1 font-semibold
                                            text-primary-deep">
                                        {{ $blogPost->published_at->format(
                                            'F j, Y',
                                        ) }}
                                    </dd>
                                </div>
                            </dl>
                        @endif

                        <a
                            href="{{ route('blog.index') }}"
                            class="public-button-secondary mt-8
                                text-primary">
                            More Stories
                        </a>
                    </aside>

                    <div
                        data-public-reveal-item
                        class="journal-article-card
                            p-7 sm:p-10 lg:p-14">
                        <div class="journal-article-body">
                            {!! str($blogPost->body)->sanitizeHtml() !!}
                        </div>

                        <div
                            class="mt-14 border-t border-line pt-9">
                            <p
                                class="font-display text-3xl
                                    text-primary-deep">
                                Continue around the table.
                            </p>

                            <p
                                class="mt-4 max-w-xl leading-7
                                    text-muted">
                                Explore the current menu or return to the
                                journal for more stories from Coast &amp; Cay.
                            </p>

                            <div class="mt-7 flex flex-wrap gap-3">
                                <a
                                    href="{{ route('menu') }}"
                                    class="public-button-primary">
                                    Explore the Menu
                                </a>

                                <a
                                    href="{{ route('blog.index') }}"
                                    class="public-button-secondary
                                        text-primary">
                                    More Stories
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </article>
</x-layouts.public>
