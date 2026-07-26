<x-layouts.public
    :title="$page->meta_title ?: $page->title"
    :description="$page->meta_description ?: $page->excerpt">
    <section
        data-public-hero
        class="relative isolate flex min-h-[65svh] items-end overflow-hidden
            bg-brand-palm-dark pb-20 pt-36 text-white lg:pb-24">
        <div
            class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_80%_22%,rgba(242,199,107,0.30),transparent_28%),radial-gradient(circle_at_15%_75%,rgba(32,111,124,0.34),transparent_32%)]"></div>

        <div
            class="absolute inset-0 -z-10 bg-gradient-to-br
                from-brand-palm-dark/80 to-brand-palm"></div>

        <div class="public-container">
            <p
                class="text-xs font-semibold uppercase tracking-[0.3em]
                    text-brand-sun">
                Coast & Cay
            </p>

            <h1
                class="mt-5 max-w-4xl font-display text-5xl leading-[0.96]
                    sm:text-7xl">
                {{ $page->title }}
            </h1>

            @if ($page->excerpt)
            <p class="mt-7 max-w-2xl text-base leading-8 text-white/75 sm:text-lg">
                {{ $page->excerpt }}
            </p>
            @endif
        </div>
    </section>

    <section class="public-island-pattern bg-brand-cream py-20 lg:py-28">
        <div class="public-container">
            <article
                class="mx-auto max-w-3xl rounded-island bg-white p-7
                    shadow-island sm:p-10 lg:p-14">
                @if ($page->content)
                <div
                    class="space-y-6 text-base leading-8 text-brand-muted
                            [&_a]:font-semibold [&_a]:text-brand-coral-dark
                            [&_a]:underline [&_a]:underline-offset-4
                            [&_blockquote]:border-l-4
                            [&_blockquote]:border-brand-coral
                            [&_blockquote]:pl-6
                            [&_h2]:mt-10 [&_h2]:font-display
                            [&_h2]:text-3xl [&_h2]:text-brand-palm
                            [&_h3]:mt-8 [&_h3]:font-display
                            [&_h3]:text-2xl [&_h3]:text-brand-palm
                            [&_ol]:list-decimal [&_ol]:space-y-3 [&_ol]:pl-6
                            [&_strong]:text-brand-forest
                            [&_ul]:list-disc [&_ul]:space-y-3 [&_ul]:pl-6">
                    {!! str($page->content)->sanitizeHtml() !!}
                </div>
                @else
                <p class="text-base leading-8 text-brand-muted">
                    Our full story is coming soon.
                </p>
                @endif

                <div class="mt-10 flex flex-wrap gap-4 border-t border-brand-palm/10 pt-8">
                    <a
                        href="{{ route('menu') }}"
                        class="public-button-primary">
                        Explore the Menu
                    </a>

                    <a
                        href="{{ route('contact.create') }}"
                        class="public-button-secondary text-brand-palm">
                        Contact Us
                    </a>
                </div>
            </article>
        </div>
    </section>
</x-layouts.public>