<x-layouts.public
    :title="$page?->meta_title ?: 'Menu'"
    :description="$page?->meta_description ?: 'Explore Caribbean-inspired dishes crafted with island soul, fresh ingredients, and coastal ease.'">
    <div
        data-menu-page
        class="min-h-screen bg-canvas">
        <section
            data-menu-hero
            class="menu-hero public-paper-texture relative isolate
                overflow-hidden border-b border-primary/10">
            <div
                data-menu-hero-depth
                class="absolute -right-28 -top-36 -z-10 size-[30rem]
        rounded-full bg-ocean/10 blur-3xl"
                aria-hidden="true">
            </div>

            <div
                data-menu-hero-depth
                class="absolute -left-36 bottom-[-15rem] -z-10 size-[26rem]
        rounded-full bg-coral/10 blur-3xl"
                aria-hidden="true">
            </div>

            <div
                data-menu-hero-depth
                class="absolute right-0 top-0 -z-10 hidden h-full w-[42%]
        bg-[linear-gradient(145deg,transparent_12%,rgb(32_111_124_/_7%),rgb(242_199_107_/_12%))]
        lg:block"
                aria-hidden="true">
            </div>

            <div
                class="public-container grid items-center gap-10 py-16
                    lg:grid-cols-[minmax(0,0.72fr)_minmax(20rem,0.28fr)]
                    lg:py-20">
                <div class="max-w-4xl">
                    <p
                        data-menu-hero-item
                        class="public-eyebrow">
                        Our menu
                    </p>

                    <h1
                        data-menu-hero-item
                        class="mt-4 max-w-4xl font-display text-5xl
                            leading-[0.96] text-primary-deep
                            sm:text-6xl lg:text-7xl">
                        {{ $page?->title
                            ?: 'Island Favorites, Made to Gather Around' }}
                    </h1>

                    <p
                        data-menu-hero-item
                        class="mt-6 max-w-2xl text-base leading-8
                            text-muted">
                        {{ $page?->excerpt
                            ?: 'Explore colorful starters, generous mains, fresh seafood, and drinks made for slow afternoons and lively evenings.' }}
                    </p>

                    <div
                        data-menu-hero-item
                        class="mt-7 flex items-center gap-3 text-primary"
                        aria-hidden="true">
                        <span class="h-px w-12 bg-primary/45"></span>

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

                        <span class="h-px w-12 bg-primary/45"></span>
                    </div>

                    <a
                        data-menu-hero-item
                        href="#menu-catalog"
                        class="mt-8 inline-flex items-center gap-3 text-xs
                            font-semibold uppercase tracking-[0.15em]
                            text-primary transition hover:text-coral">
                        Explore the selections

                        <svg
                            class="size-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m6 9 6 6 6-6" />
                        </svg>
                    </a>
                </div>

                <div
                    data-menu-hero-item
                    class="menu-glass-panel hidden rounded-panel p-7
                        lg:block">
                    <p
                        class="text-[0.65rem] font-semibold uppercase
                            tracking-[0.2em] text-coral-deep">
                        Caribbean warmth
                    </p>

                    <p
                        class="mt-4 font-display text-2xl leading-snug
                            text-primary-deep">
                        Bright ingredients, generous portions, and familiar
                        island comfort.
                    </p>

                    <p class="mt-5 text-sm leading-7 text-muted">
                        Browse one collection at a time, customize your dish,
                        and continue exploring without losing your place.
                    </p>

                    <div
                        class="mt-7 border-t border-primary/10 pt-6">
                        <p
                            class="text-[0.62rem] font-semibold uppercase
                                tracking-[0.14em] text-muted">
                            Ordering experience
                        </p>

                        <p class="mt-2 text-sm leading-6 text-ink">
                            Select any dish to view options, quantity, dietary
                            information, and its calculated total.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <livewire:menu.catalog />

        <livewire:menu.product-modal />
    </div>
</x-layouts.public>
