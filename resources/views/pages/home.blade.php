<x-layouts.public
    :title="$page?->meta_title ?: ($settings['meta_title'] ?? 'Home')"
    :description="$page?->meta_description ?: ($settings['meta_description'] ?? 'Caribbean food, warm hospitality, and California ease.')">
    @php
    $restaurantName = $settings['restaurant_name']
        ?? config('app.name');

    $storyCopy = filled($page?->content)
        ? str($page->content)->stripTags()->squish()
        : 'Coast & Cay brings the heart of the Caribbean to the California coast through vibrant flavors, thoughtful ingredients, and genuine hospitality.';

    $secondaryStoryImage = $galleryImages->first()
        ?? $heroImage;

    $ctaImage = $galleryImages->last()
        ?? $storyImage
        ?? $heroImage;

    $categoryTones = [
        'coral',
        'ocean',
        'sun',
        'primary',
        'coral',
        'ocean',
    ];
    @endphp

    <div data-home-motion>
        <x-public.homepage-hero
            eyebrow="Bold flavors. Warm hospitality."
            title="Taste the Caribbean."
            accent-title="Feel the Islands."
            :description="$page?->excerpt ?: 'From our kitchen to your table, enjoy vibrant Caribbean dishes made with fresh ingredients, bold spices, and island soul.'"
            :image="$heroImage"
            :image-alt="$heroImage?->alt_text ?: $heroImage?->title ?: 'Colorful Caribbean meal prepared by Coast and Cay'"
            primary-label="Explore Menu"
            :primary-url="route('menu')"
            secondary-label="Order Online"
            :secondary-url="route('menu')" />

        {{-- Compact restaurant-value strip --}}
        <section
            class="relative z-10 -mt-10 px-5 sm:px-6 lg:px-10"
            aria-label="Restaurant highlights">
            <div
                class="mx-auto grid w-full max-w-6xl overflow-hidden
                    rounded-panel border border-line bg-surface shadow-panel
                    sm:grid-cols-2 lg:grid-cols-4">
                <article
                    class="flex items-center gap-4 border-b border-line
                        px-6 py-6 sm:border-r lg:border-b-0">
                    <span
                        class="flex size-11 shrink-0 items-center
                            justify-center rounded-full bg-primary/10
                            text-primary">
                        <svg
                            class="size-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                d="M12 20c4-3 6-7 6-11-4 0-8 2-10 6-1 2 0 4 4 5Zm0 0c-1-5 0-9 4-13" />
                        </svg>
                    </span>

                    <div>
                        <h2 class="font-display text-lg text-ink">
                            Freshly Prepared
                        </h2>

                        <p class="mt-1 text-xs leading-5 text-muted">
                            Made with care for every order.
                        </p>
                    </div>
                </article>

                <article
                    class="flex items-center gap-4 border-b border-line
                        px-6 py-6 lg:border-b-0 lg:border-r">
                    <span
                        class="flex size-11 shrink-0 items-center
                            justify-center rounded-full bg-coral/10 text-coral">
                        <svg
                            class="size-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                d="M12 21V9m0 0c-1-4-5-6-9-4 4 1 6 2 8 6m1-2c2-4 6-5 9-3-4 0-7 2-9 6" />
                        </svg>
                    </span>

                    <div>
                        <h2 class="font-display text-lg text-ink">
                            Caribbean Inspired
                        </h2>

                        <p class="mt-1 text-xs leading-5 text-muted">
                            Bold, layered island flavor.
                        </p>
                    </div>
                </article>

                <article
                    class="flex items-center gap-4 border-b border-line
                        px-6 py-6 sm:border-b-0 sm:border-r">
                    <span
                        class="flex size-11 shrink-0 items-center
                            justify-center rounded-full bg-ocean/10 text-ocean">
                        <svg
                            class="size-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 7h11v9H3V7Zm11 3h3l3 3v3h-6v-6ZM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
                        </svg>
                    </span>

                    <div>
                        <h2 class="font-display text-lg text-ink">
                            Pickup &amp; Delivery
                        </h2>

                        <p class="mt-1 text-xs leading-5 text-muted">
                            Flexible local ordering.
                        </p>
                    </div>
                </article>

                <article class="flex items-center gap-4 px-6 py-6">
                    <span
                        class="flex size-11 shrink-0 items-center
                            justify-center rounded-full bg-sun/20
                            text-coral-deep">
                        <svg
                            class="size-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 10V7a6 6 0 0 1 12 0v3M5 10h14l-1 10H6L5 10Z" />
                        </svg>
                    </span>

                    <div>
                        <h2 class="font-display text-lg text-ink">
                            Easy Online Ordering
                        </h2>

                        <p class="mt-1 text-xs leading-5 text-muted">
                            Secure, server-verified checkout.
                        </p>
                    </div>
                </article>
            </div>
        </section>

        {{-- Featured dishes --}}
        <section class="bg-canvas py-16 sm:py-20 lg:py-24">
            <div class="public-container">
                <div
                    data-reveal
                    class="flex flex-col gap-5 sm:flex-row
                        sm:items-end sm:justify-between">
                    <div>
                        <p class="public-eyebrow">
                            Chef's Favorites
                        </p>

                        <h2
                            class="mt-3 font-display text-4xl leading-tight
                                text-ink sm:text-5xl">
                            Featured Dishes
                        </h2>

                        <div
                            class="mt-3 h-1 w-24 -rotate-2 rounded-full
                                bg-ocean"
                            aria-hidden="true"></div>
                    </div>

                    <a
                        href="{{ route('menu') }}"
                        class="inline-flex items-center gap-2 text-sm
                            font-semibold text-primary transition
                            hover:text-coral">
                        View Full Menu

                        <span class="text-coral" aria-hidden="true">
                            &rarr;
                        </span>
                    </a>
                </div>

                <div
                    class="mt-10 grid gap-5 sm:grid-cols-2
                        xl:grid-cols-4">
                    @forelse ($featuredMenuItems as $item)
                        <x-public.home-menu-card :item="$item" />
                    @empty
                        <x-public.alert
                            type="warning"
                            class="sm:col-span-2 xl:col-span-4">
                            Our chef's selections are being prepared.
                        </x-public.alert>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Restaurant story and integrated gallery preview --}}
        <section
            class="public-paper-texture relative isolate overflow-hidden
                border-y border-line py-16 sm:py-20 lg:py-24">
            <div
                class="pointer-events-none absolute left-[38%] top-20 -z-10
                    size-48 rounded-full border border-primary/10"
                aria-hidden="true"></div>

            <div
                class="public-container grid items-center gap-12
                    lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
                <div data-reveal class="max-w-xl">
                    <p class="public-eyebrow">
                        Our Story
                    </p>

                    <h2
                        class="mt-4 font-display text-4xl leading-[1.05]
                            text-ink sm:text-5xl">
                        Rooted in Tradition.<br>
                        Made for Today.
                    </h2>

                    <div
                        class="mt-4 h-1 w-32 -rotate-2 rounded-full
                            bg-ocean"
                        aria-hidden="true"></div>

                    <p class="mt-7 text-base leading-8 text-muted">
                        {{ $storyCopy }}
                    </p>

                    <p class="mt-5 text-sm font-medium leading-7 text-primary">
                        Good food. Good people. Good vibes. That is island
                        life.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a
                            href="{{ route('about') }}"
                            class="public-button-primary">
                            Learn Our Story
                        </a>

                        <a
                            href="{{ route('gallery') }}"
                            class="public-button-secondary text-primary">
                            View Gallery
                        </a>
                    </div>
                </div>

                <div
                    data-reveal
                    class="relative mx-auto min-h-[31rem] w-full
                        max-w-3xl sm:min-h-[37rem]">
                    <div
                        class="absolute left-0 top-8 w-[58%] -rotate-3
                            bg-white p-3 shadow-panel">
                        <div
                            class="relative aspect-[4/3] overflow-hidden
                                bg-surface-soft">
                            @if ($secondaryStoryImage?->image_url)
                                <x-public.responsive-image
                                    :image="$secondaryStoryImage"
                                    :alt="$secondaryStoryImage->alt_text ?: $secondaryStoryImage->title ?: 'Coast and Cay food and hospitality'"
                                    variant="large"
                                    sizes="(min-width: 1024px) 35vw, 58vw"
                                    width="900"
                                    height="675"
                                    img-class="h-full w-full object-cover" />
                            @endif
                        </div>
                    </div>

                    <div
                        class="absolute right-0 top-0 w-[55%] rotate-3
                            bg-white p-3 shadow-panel">
                        <div
                            class="relative aspect-[4/5] overflow-hidden
                                bg-surface-soft">
                            @if ($storyImage?->image_url)
                                <x-public.responsive-image
                                    :image="$storyImage"
                                    :alt="$storyImage->alt_text ?: $storyImage->title ?: 'Warm Coast and Cay restaurant atmosphere'"
                                    variant="large"
                                    sizes="(min-width: 1024px) 34vw, 55vw"
                                    width="850"
                                    height="1060"
                                    img-class="h-full w-full object-cover" />
                            @endif
                        </div>
                    </div>

                    <div
                        class="absolute bottom-3 right-7 max-w-52 -rotate-3
                            bg-[#fffdf7] px-5 py-4 font-display text-lg
                            italic leading-6 text-primary shadow-card">
                        Made with island soul and a warm welcome.
                    </div>
                </div>
            </div>
        </section>

        {{-- Menu category pills --}}
        <section class="bg-canvas py-12 sm:py-14">
            <div class="public-container">
                <div data-reveal class="text-center">
                    <p class="public-eyebrow">
                        Explore by Category
                    </p>

                    <h2
                        class="mt-3 font-display text-3xl text-ink
                            sm:text-4xl">
                        Find your next favorite
                    </h2>
                </div>

                <div
                    class="mt-8 flex flex-wrap justify-center gap-3">
                    @forelse ($featuredCategories as $category)
                        <x-public.home-category-pill
                            :category="$category"
                            :tone="$categoryTones[
                                $loop->index % count($categoryTones)
                            ]" />
                    @empty
                        <x-public.alert type="warning">
                            Menu categories are being prepared.
                        </x-public.alert>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Truthful experience cards instead of fabricated testimonials --}}
        <section
            class="public-island-pattern bg-surface py-16 sm:py-20
                lg:py-24">
            <div class="public-container">
                <div data-reveal class="text-center">
                    <p class="public-eyebrow">
                        The {{ $restaurantName }} Experience
                    </p>

                    <h2
                        class="mt-3 font-display text-4xl leading-tight
                            text-ink sm:text-5xl">
                        Made for Good Company
                    </h2>

                    <div
                        class="mx-auto mt-4 h-1 w-32 -rotate-2
                            rounded-full bg-ocean"
                        aria-hidden="true"></div>
                </div>

                <div class="mt-10 grid gap-5 lg:grid-cols-3">
                    <article
                        data-reveal
                        class="rounded-card border border-line bg-surface
                            p-7 shadow-card">
                        <p class="text-2xl text-coral" aria-hidden="true">
                            ✦
                        </p>

                        <h3 class="mt-4 font-display text-2xl text-ink">
                            Bold, Honest Flavor
                        </h3>

                        <p class="mt-4 text-sm leading-7 text-muted">
                            Caribbean-inspired dishes built around layered
                            seasoning, satisfying textures, and generous
                            portions.
                        </p>
                    </article>

                    <article
                        data-reveal
                        class="rounded-card border border-line bg-surface
                            p-7 shadow-card">
                        <p class="text-2xl text-ocean" aria-hidden="true">
                            ✦
                        </p>

                        <h3 class="mt-4 font-display text-2xl text-ink">
                            Warm Hospitality
                        </h3>

                        <p class="mt-4 text-sm leading-7 text-muted">
                            A relaxed, welcoming experience designed for
                            everyday meals, celebrations, and time shared
                            together.
                        </p>
                    </article>

                    <article
                        data-reveal
                        class="rounded-card border border-line bg-surface
                            p-7 shadow-card">
                        <p class="text-2xl text-primary" aria-hidden="true">
                            ✦
                        </p>

                        <h3 class="mt-4 font-display text-2xl text-ink">
                            Easy From First Click
                        </h3>

                        <p class="mt-4 text-sm leading-7 text-muted">
                            Browse the menu, customize your dish, and choose
                            pickup or eligible local delivery from one simple
                            ordering flow.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        {{-- Conditional blog content remains part of the active scope. --}}
        <x-public.blog-preview />

        {{-- Homepage closing CTA --}}
        <section class="bg-canvas px-5 py-12 sm:px-6 lg:px-10">
            <div
                data-reveal
                class="relative isolate mx-auto grid min-h-64 w-full
                    max-w-[86rem] items-center overflow-hidden rounded-panel
                    bg-ocean px-7 py-12 text-white shadow-panel sm:px-10
                    lg:grid-cols-[1fr_auto] lg:px-16">
                @if ($ctaImage?->image_url)
                    <div class="absolute inset-0 -z-20">
                        <x-public.responsive-image
                            :image="$ctaImage"
                            :alt="$ctaImage->alt_text ?: $ctaImage->title ?: 'Coast and Cay dining atmosphere'"
                            variant="hero"
                            sizes="100vw"
                            width="1600"
                            height="600"
                            img-class="h-full w-full object-cover" />
                    </div>
                @endif

                <div
                    class="absolute inset-0 -z-10
                        bg-[linear-gradient(90deg,rgba(7,101,112,0.98)_0%,rgba(7,101,112,0.92)_48%,rgba(7,45,37,0.55)_100%)]">
                </div>

                <div class="max-w-2xl">
                    <p
                        class="text-xs font-semibold uppercase
                            tracking-[0.22em] text-sun">
                        Come Hungry. Leave Happy.
                    </p>

                    <h2
                        class="mt-4 font-display text-4xl leading-tight
                            sm:text-5xl">
                        Ready for Good Food and Island Vibes?
                    </h2>

                    <p class="mt-4 max-w-xl text-sm leading-7 text-white/80">
                        Explore the current menu and enjoy Coast &amp; Cay
                        through pickup or eligible local delivery.
                    </p>
                </div>

                <div
                    class="mt-8 flex flex-col gap-3 sm:flex-row
                        lg:mt-0 lg:pl-10">
                    <a
                        href="{{ route('menu') }}"
                        class="inline-flex min-h-12 items-center
                            justify-center rounded-xl bg-white px-6
                            text-xs font-semibold uppercase tracking-[0.14em]
                            text-ocean transition hover:-translate-y-0.5
                            hover:shadow-panel motion-reduce:transform-none">
                        Explore Menu
                    </a>

                    <a
                        href="{{ route('menu') }}"
                        class="inline-flex min-h-12 items-center
                            justify-center rounded-xl border border-white/55
                            bg-primary-deep/65 px-6 text-xs font-semibold
                            uppercase tracking-[0.14em] text-white transition
                            hover:-translate-y-0.5 hover:bg-primary-deep
                            motion-reduce:transform-none">
                        Order Online
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>
