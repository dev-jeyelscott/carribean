<x-layouts.public
    :title="$page?->meta_title ?: 'Menu'"
    :description="$page?->meta_description ?: 'Explore Caribbean-inspired dishes, shared plates, desserts, and drinks at Coast & Cay.'">
    @php
    $restaurantName = $settings['restaurant_name']
    ?? config('app.name');

    $menuItems = $categories->flatMap->menuItems;

    $heroItem = $menuItems->first(
    fn ($item) => filled($item->image_url),
    );
    @endphp

    <div data-home-motion data-public-motion="menu">
        <section
            data-public-hero
            data-menu-motion="hero"
            class="public-hero-viewport relative isolate flex items-center
                overflow-hidden bg-brand-palm-dark">
            @if ($heroItem?->image_url)
            <div
                data-gsap="hero-image"
                data-menu-motion="hero-image"
                class="absolute inset-0 -z-30 overflow-hidden">
                <x-public.responsive-image
                    :image="$heroItem"
                    alt=""
                    variant="hero"
                    sizes="100vw"
                    width="1920"
                    height="1280"
                    loading="eager"
                    fetchpriority="high"
                    img-class="absolute inset-0 h-full w-full object-cover" />
            </div>
            @else
            <div
                data-gsap="hero-image"
                data-menu-motion="hero-image"
                class="absolute inset-0 -z-30 bg-[radial-gradient(circle_at_72%_28%,rgba(242,199,107,0.28),transparent_25%),linear-gradient(135deg,#206f7c,#0c342b_62%)]"></div>
            @endif

            <div
                class="absolute inset-0 -z-20 bg-[linear-gradient(to_bottom,rgba(12,52,43,0.45),rgba(12,52,43,0.64)_45%,rgba(12,52,43,0.98))]"></div>

            <div
                class="absolute inset-0 -z-10 bg-gradient-to-r
                    from-brand-palm-dark/95 via-brand-palm-dark/55
                    to-brand-palm-dark/15"></div>

            <div class="public-container pb-16 pt-32 sm:pb-24 sm:pt-40 lg:pb-28 lg:pt-48">
                <div
                    data-gsap="hero-content"
                    data-menu-motion="hero-content"
                    class="max-w-4xl">
                    <p
                        data-gsap-reveal
                        data-menu-motion="hero-item"
                        class="text-xs font-semibold uppercase tracking-[0.34em]
                            text-brand-sun">
                        Our Menu
                    </p>

                    <h1
                        data-gsap-reveal
                        data-menu-motion="hero-item"
                        class="mt-6 max-w-4xl font-display text-5xl
                            leading-[0.95] text-white sm:text-7xl lg:text-8xl">
                        {{ $page?->title ?: 'Island Favorites, Made to Gather Around' }}
                    </h1>

                    <p
                        data-gsap-reveal
                        data-menu-motion="hero-item"
                        class="mt-7 max-w-2xl text-base leading-8
                            text-white/80 sm:text-lg">
                        {{ $page?->excerpt ?: 'Explore colorful starters, generous mains, sweet finishes, and drinks made for slow afternoons and lively evenings.' }}
                    </p>

                    <div
                        data-gsap-reveal
                        data-menu-motion="hero-item"
                        class="mt-10 flex flex-col gap-4 sm:flex-row">
                        <a href="#menu-selections" class="public-button-primary">
                            Explore the Menu
                        </a>
                    </div>
                </div>
            </div>
        </section>

        @if ($categories->isNotEmpty())
        <nav
            data-menu-motion="category-nav"
            class="border-y border-brand-palm/10 bg-brand-cream"
            aria-label="Menu categories">
            <div
                class="relative mx-auto flex max-w-7xl gap-7 overflow-x-auto
                        px-5 py-5 sm:px-6 lg:justify-center lg:px-10">
                @foreach ($categories as $category)
                <a
                    href="#category-{{ $category->slug }}"
                    data-menu-category-link
                    @if ($loop->first) aria-current="true" @endif
                    class="shrink-0 text-[0.68rem] font-semibold
                    uppercase tracking-[0.2em] text-brand-palm
                    transition hover:text-brand-coral-dark"
                    >
                    {{ $category->name }}
                </a>
                @endforeach

                <span
                    data-menu-category-indicator
                    class="pointer-events-none absolute bottom-0 left-0
                            h-0.5 w-0 bg-brand-coral"
                    aria-hidden="true"></span>
            </div>
        </nav>
        @endif

        <section
            id="menu-selections"
            class="public-island-pattern bg-brand-palm-dark py-24 lg:py-32">
            <div class="public-container">
                <x-public.section-heading
                    data-menu-motion="full-heading"
                    eyebrow="From Our Kitchen"
                    title="The {{ $restaurantName }} Menu"
                    :description="$page?->content ?: 'Food made with warmth, color, and a generous sense of hospitality.'" />

                <div class="mt-20 divide-y divide-white/15">
                    @forelse ($categories as $category)
                    <section
                        id="category-{{ $category->slug }}"
                        data-menu-motion="course"
                        class="scroll-mt-28 py-16 first:pt-0 last:pb-0 lg:py-24">
                        <div
                            class="grid gap-12 lg:grid-cols-[0.32fr_0.68fr]
                                    lg:gap-16">
                            <div>
                                <p
                                    data-menu-motion="course-number"
                                    class="text-[0.68rem] font-semibold
                                            uppercase tracking-[0.28em]
                                            text-brand-sun">
                                    Selection
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </p>

                                <h2
                                    data-menu-motion="course-title"
                                    class="mt-4 font-display text-4xl
                                            leading-tight text-brand-cream
                                            sm:text-5xl">
                                    {{ $category->name }}
                                </h2>

                                @if ($category->description)
                                <p
                                    data-menu-motion="course-description"
                                    class="mt-5 max-w-md text-sm leading-7
                                                text-brand-cream/60">
                                    {{ $category->description }}
                                </p>
                                @endif

                                <div
                                    data-menu-motion="course-rule"
                                    class="mt-8 h-0.5 w-16 origin-left
                                            scale-x-0 bg-brand-coral"
                                    aria-hidden="true"></div>
                            </div>

                            <div class="grid gap-x-8 gap-y-12 sm:grid-cols-2">
                                @forelse ($category->menuItems as $item)
                                <x-public.menu-card
                                    :item="$item"
                                    variant="luxury" />
                                @empty
                                <x-public.alert
                                    type="warning"
                                    class="sm:col-span-2">
                                    This selection is currently being prepared.
                                </x-public.alert>
                                @endforelse
                            </div>
                        </div>
                    </section>
                    @empty
                    <x-public.alert type="warning">
                        Our latest menu is currently being prepared.
                    </x-public.alert>
                    @endforelse
                </div>
            </div>
        </section>

        <section
            data-menu-motion="closing-cta"
            class="relative isolate overflow-hidden bg-brand-sand-soft py-24
                text-brand-forest lg:py-28">
            <div
                class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_85%_20%,rgba(230,110,80,0.18),transparent_28%)]"></div>

            <div
                class="public-container grid items-center gap-12
                    lg:grid-cols-[1fr_auto]">
                <div data-menu-motion="closing-copy">
                    <p
                        data-menu-motion="closing-item"
                        class="public-eyebrow">
                        Join Us
                    </p>

                    <h2
                        data-menu-motion="closing-item"
                        class="mt-5 max-w-3xl font-display text-4xl
                            leading-tight sm:text-5xl">
                        Good food tastes even better around a shared table.
                    </h2>

                    <p
                        data-menu-motion="closing-item"
                        class="mt-6 max-w-2xl text-base leading-8 text-brand-muted">
                        Request a table or contact the restaurant team with
                        questions about the current menu.
                    </p>
                </div>

                <div
                    data-menu-motion="closing-actions"
                    class="flex flex-col gap-4 sm:flex-row lg:flex-col">

                    <a
                        href="{{ route('contact.create') }}"
                        class="public-button-secondary text-brand-palm">
                        Contact Us
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>