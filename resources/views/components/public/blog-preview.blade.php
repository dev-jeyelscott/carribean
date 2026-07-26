<section
    data-gsap="section"
    class="public-island-pattern border-t border-brand-palm/10
        bg-brand-sand-soft py-20 lg:py-28">
    <div class="public-container">
        <div
            class="flex flex-col gap-6 lg:flex-row
                lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="public-eyebrow">
                    From the Journal
                </p>

                <h2
                    class="mt-4 font-display text-4xl leading-tight
                        text-brand-palm-dark sm:text-5xl">
                    Stories from our kitchen and table.
                </h2>
            </div>

            <a
                href="{{ route('blog.index') }}"
                class="public-button-secondary text-brand-palm">
                View All Stories
            </a>
        </div>

        <div class="mt-12 grid gap-7 lg:grid-cols-3">
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
                                    sizes="(min-width: 1024px) 33vw, 100vw"
                                @endif
                                alt="{{ $post->image_alt_text
                                    ?: $post->title }}"
                                loading="lazy"
                                class="aspect-[16/10] w-full object-cover
                                    transition duration-500 ease-island
                                    group-hover:scale-[1.03]
                                    motion-reduce:transform-none
                                    motion-reduce:transition-none">
                        </a>
                    @endif

                    <div class="p-7">
                        @if ($post->published_at)
                            <time
                                datetime="{{ $post->published_at->toDateString() }}"
                                class="text-xs font-semibold uppercase
                                    tracking-[0.18em] text-brand-coral-dark">
                                {{ $post->published_at->format('F j, Y') }}
                            </time>
                        @endif

                        <h3
                            class="mt-4 font-display text-2xl
                                leading-tight text-brand-palm-dark">
                            <a
                                href="{{ route('blog.show', $post) }}"
                                class="transition hover:text-brand-coral-dark">
                                {{ $post->title }}
                            </a>
                        </h3>

                        @if ($post->excerpt)
                            <p
                                class="mt-4 line-clamp-3 text-sm
                                    leading-7 text-brand-muted">
                                {{ $post->excerpt }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
