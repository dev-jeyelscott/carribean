<x-layouts.public
    :title="$page?->meta_title ?: 'Menu'"
    :description="$page?->meta_description ?: 'Explore Caribbean-inspired dishes crafted with island soul, fresh ingredients, and coastal ease.'">
    <div
        data-menu-page
        class="min-h-screen bg-canvas">
        <section
            data-menu-intro
            class="public-paper-texture relative isolate overflow-hidden
                border-b border-primary/10">
            <div
                class="absolute -right-28 -top-36 -z-10 size-[30rem]
                    rounded-full bg-ocean/10 blur-3xl"
                aria-hidden="true"></div>

            <div
                class="absolute -left-36 bottom-[-15rem] -z-10 size-[26rem]
                    rounded-full bg-coral/10 blur-3xl"
                aria-hidden="true"></div>

            <div
                class="absolute right-0 top-0 -z-10 hidden h-full w-[38%]
                    bg-[linear-gradient(145deg,transparent_12%,rgb(32_111_124_/_7%),rgb(242_199_107_/_12%))]
                    lg:block"
                aria-hidden="true"></div>

            <div
                class="public-container grid items-center gap-8 py-14
                    sm:py-16 lg:grid-cols-[minmax(0,0.72fr)_minmax(18rem,0.28fr)]
                    lg:py-20">
                <div class="max-w-3xl">
                    <p class="public-eyebrow">
                        Our menu
                    </p>

                    <h1
                        class="mt-3 font-display text-5xl leading-[0.98]
                            text-primary-deep sm:text-6xl lg:text-7xl">
                        {{ $page?->title ?: 'Explore Our Menu' }}
                    </h1>

                    <p
                        class="mt-5 max-w-2xl text-base leading-8 text-muted">
                        {{ $page?->excerpt ?: 'Caribbean-inspired dishes crafted with island soul, fresh ingredients, and a touch of coastal ease.' }}
                    </p>

                    <div
                        class="mt-6 flex items-center gap-3 text-primary"
                        aria-hidden="true">
                        <span class="h-px w-10 bg-primary/45"></span>

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

                        <span class="h-px w-10 bg-primary/45"></span>
                    </div>
                </div>

                <div
                    class="menu-glass-panel hidden rounded-panel p-6
                        lg:block">
                    <p
                        class="text-[0.65rem] font-semibold uppercase
                            tracking-[0.2em] text-coral-deep">
                        Caribbean warmth
                    </p>

                    <p
                        class="mt-3 font-display text-2xl leading-snug
                            text-primary-deep">
                        Bright ingredients, generous portions, and familiar
                        island comfort.
                    </p>

                    <p class="mt-4 text-sm leading-7 text-muted">
                        Browse by category, choose a quantity, and add simple
                        items directly to your cart.
                    </p>
                </div>
            </div>
        </section>

        <livewire:menu.catalog />
    </div>
</x-layouts.public>
