<x-layouts.public
    title="Journal"
    description="Stories, restaurant updates, Caribbean food, and warm hospitality from Coast & Cay."
    :canonical="route('blog.index')">
    <section
        class="relative isolate overflow-hidden bg-brand-palm-dark
            pb-20 pt-36 text-white lg:pb-24">
        <div
            class="absolute inset-0 -z-10
                bg-[radial-gradient(circle_at_78%_20%,rgba(242,199,107,0.28),transparent_28%),radial-gradient(circle_at_12%_75%,rgba(32,111,124,0.34),transparent_34%)]">
        </div>

        <div class="public-container">
            <p class="public-eyebrow text-brand-sun">
                Coast &amp; Cay Journal
            </p>

            <h1
                class="mt-5 max-w-4xl font-display text-5xl
                    leading-[0.96] sm:text-7xl">
                Stories from the kitchen, island, and table.
            </h1>

            <p
                class="mt-7 max-w-2xl text-base leading-8
                    text-white/75 sm:text-lg">
                Restaurant news, Caribbean inspiration, hospitality,
                and the stories behind the dishes we share.
            </p>
        </div>
    </section>

    <section
        class="public-island-pattern bg-brand-cream
            py-20 lg:py-28">
        <div class="public-container">
            @if ($posts->isEmpty())
                <div
                    class="mx-auto max-w-2xl rounded-island bg-white
                        p-10 text-center shadow-island">
                    <h2
                        class="font-display text-3xl
                            text-brand-palm-dark">
                        Our first story is coming soon.
                    </h2>

                    <p class="mt-5 leading-8 text-brand-muted">
                        In the meantime, explore the current restaurant
                        menu and discover what is being prepared.
                    </p>

                    <a
                        href="{{ route('menu') }}"
                        class="public-button-primary mt-8">
                        Explore the Menu
                    </a>
                </div>
            @else
                <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($posts as $post)
                        <article
                            class="group overflow-hidden rounded-island
                                bg-white shadow-island">
                            @if ($post->image_url)
                                <a
                                    href="{{ route('blog.show', $post) }}"
                                    class="block overflow-hidden">
                                    <img
                                        src="{{ $post->responsiveImageUrl() }}"
                                        @if ($post->responsiveImageSrcset())
                                            srcset="{{ $post->responsiveImageSrcset() }}"
                                            sizes="(min-width: 1280px) 33vw,
                                                (min-width: 768px) 50vw,
                                                100vw"
                                        @endif
                                        alt="{{ $post->image_alt_text
                                            ?: $post->title }}"
                                        loading="lazy"
                                        class="aspect-[16/10] w-full
                                            object-cover transition
                                            duration-500 ease-island
                                            group-hover:scale-[1.03]
                                            motion-reduce:transform-none
                                            motion-reduce:transition-none">
                                </a>
                            @endif

                            <div class="p-7">
                                @if ($post->published_at)
                                    <time
                                        datetime="{{ $post->published_at->toDateString() }}"
                                        class="text-xs font-semibold
                                            uppercase tracking-[0.18em]
                                            text-brand-coral-dark">
                                        {{ $post->published_at->format('F j, Y') }}
                                    </time>
                                @endif

                                <h2
                                    class="mt-4 font-display text-3xl
                                        leading-tight text-brand-palm-dark">
                                    <a
                                        href="{{ route('blog.show', $post) }}"
                                        class="transition
                                            hover:text-brand-coral-dark">
                                        {{ $post->title }}
                                    </a>
                                </h2>

                                @if ($post->excerpt)
                                    <p
                                        class="mt-5 text-sm leading-7
                                            text-brand-muted">
                                        {{ $post->excerpt }}
                                    </p>
                                @endif

                                <a
                                    href="{{ route('blog.show', $post) }}"
                                    class="mt-6 inline-flex font-semibold
                                        text-brand-coral-dark transition
                                        hover:text-brand-palm-dark">
                                    Read the Story
                                    <span class="ml-2" aria-hidden="true">
                                        &rarr;
                                    </span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
