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
    :structured-data="$structuredData">
    <article>
        <header
            class="relative isolate overflow-hidden bg-brand-palm-dark
                pb-20 pt-36 text-white lg:pb-24">
            @if ($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    @if ($blogPost->responsiveImageSrcset())
                        srcset="{{ $blogPost->responsiveImageSrcset() }}"
                        sizes="100vw"
                    @endif
                    alt="{{ $blogPost->image_alt_text
                        ?: $blogPost->title }}"
                    class="absolute inset-0 -z-20 size-full object-cover">

                <div
                    class="absolute inset-0 -z-10
                        bg-gradient-to-t from-brand-palm-dark
                        via-brand-palm-dark/75
                        to-brand-palm-dark/45">
                </div>
            @else
                <div
                    class="absolute inset-0 -z-10
                        bg-[radial-gradient(circle_at_78%_20%,rgba(242,199,107,0.28),transparent_28%),radial-gradient(circle_at_12%_75%,rgba(32,111,124,0.34),transparent_34%)]">
                </div>
            @endif

            <div class="public-container">
                <a
                    href="{{ route('blog.index') }}"
                    class="text-xs font-semibold uppercase
                        tracking-[0.18em] text-brand-sun transition
                        hover:text-white">
                    &larr; Back to Journal
                </a>

                <h1
                    class="mt-7 max-w-4xl font-display text-5xl
                        leading-[0.98] sm:text-7xl">
                    {{ $blogPost->title }}
                </h1>

                @if ($blogPost->excerpt)
                    <p
                        class="mt-7 max-w-2xl text-base leading-8
                            text-white/78 sm:text-lg">
                        {{ $blogPost->excerpt }}
                    </p>
                @endif

                @if ($blogPost->published_at)
                    <time
                        datetime="{{ $blogPost->published_at->toDateString() }}"
                        class="mt-7 block text-xs font-semibold uppercase
                            tracking-[0.18em] text-white/65">
                        Published
                        {{ $blogPost->published_at->format('F j, Y') }}
                    </time>
                @endif
            </div>
        </header>

        <section
            class="public-island-pattern bg-brand-cream
                py-20 lg:py-28">
            <div class="public-container">
                <div
                    class="mx-auto max-w-3xl rounded-island bg-white
                        p-7 shadow-island sm:p-10 lg:p-14">
                    <div
                        class="space-y-6 text-base leading-8
                            text-brand-muted
                            [&_a]:font-semibold
                            [&_a]:text-brand-coral-dark
                            [&_a]:underline
                            [&_a]:underline-offset-4
                            [&_blockquote]:border-l-4
                            [&_blockquote]:border-brand-coral
                            [&_blockquote]:pl-6
                            [&_h2]:mt-10
                            [&_h2]:font-display
                            [&_h2]:text-3xl
                            [&_h2]:text-brand-palm
                            [&_h3]:mt-8
                            [&_h3]:font-display
                            [&_h3]:text-2xl
                            [&_h3]:text-brand-palm
                            [&_ol]:list-decimal
                            [&_ol]:space-y-3
                            [&_ol]:pl-6
                            [&_strong]:text-brand-forest
                            [&_ul]:list-disc
                            [&_ul]:space-y-3
                            [&_ul]:pl-6">
                        {!! str($blogPost->body)->sanitizeHtml() !!}
                    </div>

                    <div
                        class="mt-12 border-t border-brand-palm/10
                            pt-8">
                        <a
                            href="{{ route('blog.index') }}"
                            class="public-button-secondary
                                text-brand-palm">
                            More Stories
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </article>
</x-layouts.public>
