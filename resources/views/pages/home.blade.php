<x-layouts.public
    :title="$page?->meta_title ?: ($settings['meta_title'] ?? 'Home')"
    :description="$page?->meta_description ?: ($settings['meta_description'] ?? 'Caribbean food, warm hospitality, and California ease.')">
    @php
        /*
         * Resolve editable restaurant content and safe development fallbacks.
         */
        $restaurantName = $settings['restaurant_name']
            ?? config('app.name');

        $storyCopy = filled($page?->content)
            ? str($page->content)->stripTags()->squish()
            : 'Coast & Cay brings the heart of the Caribbean to the California coast through vibrant flavors, thoughtful ingredients, and genuine hospitality.';

        $phone = $settings['phone'] ?? null;
        $address = $settings['address'] ?? null;
        $openingHours = $settings['opening_hours'] ?? null;
        $mapLink = $settings['map_link'] ?? null;

        $galleryPrimary = $galleryImages->get(0)
            ?? $storyImage
            ?? $heroImage;

        $gallerySecondary = $galleryImages->get(1)
            ?? $heroImage
            ?? $storyImage;

        $galleryTertiary = $galleryImages->get(2)
            ?? $storyImage
            ?? $heroImage;

        $menuBackdrop = $galleryImages->last()
            ?? $heroImage
            ?? $storyImage;

        $categoryTones = [
            'coral',
            'ocean',
            'sun',
            'primary',
            'coral',
            'ocean',
        ];
    @endphp

    <div
        data-home-motion
        data-home-section-pager
        class="home-section-pager relative">
        <x-public.homepage-hero
            eyebrow="Bold flavors. Warm hospitality."
            title="Taste the Caribbean."
            accent-title="Feel the Islands."
            :description="$page?->excerpt ?: 'A warm gathering place for vibrant Caribbean dishes, thoughtful drinks, and relaxed hospitality.'"
            :image="$heroImage"
            :image-alt="$heroImage?->alt_text ?: $heroImage?->title ?: 'Colorful Caribbean meal prepared by Coast and Cay'"
            primary-label="Explore Menu"
            :primary-url="route('menu')"
            secondary-label="Order Online"
            :secondary-url="route('menu')" />

        {{-- Featured dishes and restaurant benefits --}}
        <section
            id="featured"
            data-home-panel
            data-home-label="Featured dishes"
            aria-labelledby="featured-dishes-heading"
            class="home-panel overflow-hidden bg-canvas">
            <div class="public-container py-24 lg:py-28">
                <div
                    data-home-reveal
                    class="grid overflow-hidden border-y border-line
                        bg-primary-deep text-white sm:grid-cols-2
                        lg:grid-cols-4">
                    <article class="px-6 py-5 text-center lg:py-6">
                        <p class="font-display text-lg">
                            Freshly Prepared
                        </p>

                        <p class="mt-1 text-xs leading-5 text-white/65">
                            Made with care for every order.
                        </p>
                    </article>

                    <article
                        class="border-t border-white/10 px-6 py-5 text-center
                            sm:border-l sm:border-t-0 lg:py-6">
                        <p class="font-display text-lg">
                            Caribbean Inspired
                        </p>

                        <p class="mt-1 text-xs leading-5 text-white/65">
                            Bold, layered island flavors.
                        </p>
                    </article>

                    <article
                        class="border-t border-white/10 px-6 py-5 text-center
                            lg:border-l lg:border-t-0 lg:py-6">
                        <p class="font-display text-lg">
                            Pickup &amp; Delivery
                        </p>

                        <p class="mt-1 text-xs leading-5 text-white/65">
                            Flexible local ordering.
                        </p>
                    </article>

                    <article
                        class="border-t border-white/10 px-6 py-5 text-center
                            sm:border-l lg:border-t-0 lg:py-6">
                        <p class="font-display text-lg">
                            Easy Online Ordering
                        </p>

                        <p class="mt-1 text-xs leading-5 text-white/65">
                            Secure, server-verified checkout.
                        </p>
                    </article>
                </div>

                <div
                    class="mt-10 grid items-start gap-8
                        xl:grid-cols-[0.82fr_repeat(4,minmax(0,1fr))]">
                    <div data-home-reveal class="max-w-sm xl:pr-3">
                        <p class="public-eyebrow">
                            Chef's Favorites
                        </p>

                        <h2
                            id="featured-dishes-heading"
                            class="mt-3 font-display text-4xl leading-[1.02]
                                text-ink lg:text-5xl">
                            Featured<br>
                            Dishes
                        </h2>

                        <div
                            class="mt-5 h-px w-28 bg-coral"
                            aria-hidden="true">
                        </div>

                        <p class="mt-6 text-sm leading-7 text-muted">
                            Curated plates that bring the warmth, spice, and
                            heart of the Caribbean to your table.
                        </p>

                        <a
                            href="{{ route('menu') }}"
                            class="public-button-primary mt-7">
                            View Full Menu
                        </a>
                    </div>

                    @forelse ($featuredMenuItems as $item)
                        <x-public.home-menu-card :item="$item" />
                    @empty
                        <x-public.alert
                            type="warning"
                            class="xl:col-span-4">
                            Our chef's selections are being prepared.
                        </x-public.alert>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Restaurant story --}}
        <section
            id="story"
            data-home-panel
            data-home-label="Our story"
            aria-labelledby="home-story-heading"
            class="home-panel overflow-hidden bg-primary-deep text-white">
            <div class="grid min-h-[100svh] w-full lg:grid-cols-[44%_56%]">
                <div
                    class="flex items-center px-5 py-24 sm:px-8
                        lg:px-12 xl:px-[max(4rem,calc((100vw-86rem)/2+2.5rem))]">
                    <div data-home-reveal class="max-w-xl">
                        <p
                            class="text-xs font-semibold uppercase
                                tracking-[0.22em] text-coral">
                            Our Story
                        </p>

                        <h2
                            id="home-story-heading"
                            class="mt-5 font-display text-4xl leading-[1.05]
                                text-white sm:text-5xl lg:text-6xl">
                            Rooted in Tradition.<br>
                            Made for Today.
                        </h2>

                        <div
                            class="mt-5 h-px w-32 bg-sun"
                            aria-hidden="true">
                        </div>

                        <p class="mt-7 text-base leading-8 text-white/72">
                            {{ $storyCopy }}
                        </p>

                        <div class="mt-8 grid gap-5 sm:grid-cols-3">
                            <div>
                                <p class="font-display text-xl text-sun">
                                    Good food.
                                </p>

                                <p class="mt-2 text-xs leading-5 text-white/60">
                                    Flavor with character and care.
                                </p>
                            </div>

                            <div>
                                <p class="font-display text-xl text-sun">
                                    Good people.
                                </p>

                                <p class="mt-2 text-xs leading-5 text-white/60">
                                    Hospitality that feels genuine.
                                </p>
                            </div>

                            <div>
                                <p class="font-display text-xl text-sun">
                                    Good vibes.
                                </p>

                                <p class="mt-2 text-xs leading-5 text-white/60">
                                    Relaxed California coastal energy.
                                </p>
                            </div>
                        </div>

                        <div class="mt-9 flex flex-wrap gap-3">
                            <a
                                href="{{ route('about') }}"
                                class="public-button-primary">
                                Learn Our Story
                            </a>

                            <a
                                href="{{ route('gallery') }}"
                                class="inline-flex min-h-12 items-center
                                    justify-center rounded-xl border
                                    border-white/45 px-6 text-xs font-semibold
                                    uppercase tracking-[0.14em] text-white
                                    transition hover:bg-white
                                    hover:text-primary-deep">
                                View Gallery
                            </a>
                        </div>
                    </div>
                </div>

                <div class="relative min-h-[55svh] lg:min-h-[100svh]">
                    @if ($storyImage?->image_url)
                        <x-public.responsive-image
                            :image="$storyImage"
                            :alt="$storyImage->alt_text ?: $storyImage->title ?: 'Warm Coast and Cay restaurant atmosphere'"
                            variant="hero"
                            sizes="(min-width: 1024px) 56vw, 100vw"
                            width="1900"
                            height="1500"
                            img-class="absolute inset-0 size-full object-cover" />
                    @endif

                    <div
                        class="absolute inset-0 bg-gradient-to-r
                            from-primary-deep/35 via-transparent to-transparent"
                        aria-hidden="true">
                    </div>
                </div>
            </div>
        </section>

        {{-- Menu exploration and brand promises --}}
        <section
            id="menu-explorer"
            data-home-panel
            data-home-label="Explore menu"
            aria-labelledby="menu-explorer-heading"
            class="home-panel relative isolate overflow-hidden">
            @if ($menuBackdrop?->image_url)
                <div class="absolute inset-0 -z-30">
                    <x-public.responsive-image
                        :image="$menuBackdrop"
                        :alt="$menuBackdrop->alt_text ?: $menuBackdrop->title ?: 'Caribbean coast and island atmosphere'"
                        variant="hero"
                        sizes="100vw"
                        width="2000"
                        height="1400"
                        img-class="size-full object-cover" />
                </div>
            @endif

            <div
                class="absolute inset-0 -z-20 bg-canvas/82
                    backdrop-blur-[2px]"
                aria-hidden="true">
            </div>

            <div class="public-container py-24 lg:py-28">
                <div data-home-reveal class="mx-auto max-w-3xl text-center">
                    <p class="public-eyebrow">
                        Explore Our Menu
                    </p>

                    <h2
                        id="menu-explorer-heading"
                        class="mt-4 font-display text-4xl leading-tight
                            text-ink sm:text-5xl lg:text-6xl">
                        Find your next favorite
                    </h2>
                </div>

                <div
                    data-home-reveal
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

                <div
                    data-home-reveal
                    class="mt-14 grid overflow-hidden border border-line
                        bg-surface/90 shadow-panel backdrop-blur-md
                        lg:grid-cols-3">
                    <article class="p-8 lg:p-10">
                        <p class="text-xs font-semibold uppercase
                            tracking-[0.18em] text-coral">
                            Flavor
                        </p>

                        <h3 class="mt-4 font-display text-2xl text-ink">
                            Bold, Honest Flavor
                        </h3>

                        <p class="mt-4 text-sm leading-7 text-muted">
                            Layered seasoning, satisfying textures, and
                            generous portions inspired by Caribbean kitchens.
                        </p>
                    </article>

                    <article
                        class="border-t border-line p-8
                            lg:border-l lg:border-t-0 lg:p-10">
                        <p class="text-xs font-semibold uppercase
                            tracking-[0.18em] text-ocean">
                            Hospitality
                        </p>

                        <h3 class="mt-4 font-display text-2xl text-ink">
                            A Warm Welcome
                        </h3>

                        <p class="mt-4 text-sm leading-7 text-muted">
                            A relaxed dining experience made for everyday
                            meals, celebrations, and time shared together.
                        </p>
                    </article>

                    <article
                        class="border-t border-line p-8
                            lg:border-l lg:border-t-0 lg:p-10">
                        <p class="text-xs font-semibold uppercase
                            tracking-[0.18em] text-primary">
                            Ordering
                        </p>

                        <h3 class="mt-4 font-display text-2xl text-ink">
                            Easy From First Click
                        </h3>

                        <p class="mt-4 text-sm leading-7 text-muted">
                            Browse the menu, customize your dish, and select
                            pickup or eligible local delivery.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        {{-- Gallery and restaurant atmosphere --}}
        <section
            id="gallery-preview"
            data-home-panel
            data-home-label="Gallery"
            aria-labelledby="gallery-preview-heading"
            class="home-panel overflow-hidden bg-surface-soft">
            <div
                class="public-container grid items-center gap-10 py-24
                    lg:grid-cols-[0.72fr_1.28fr] lg:py-28">
                <div data-home-reveal class="max-w-md">
                    <p class="public-eyebrow">
                        The Coast &amp; Cay Experience
                    </p>

                    <h2
                        id="gallery-preview-heading"
                        class="mt-4 font-display text-4xl leading-[1.05]
                            text-ink sm:text-5xl lg:text-6xl">
                        Food, warmth, and coastal evenings.
                    </h2>

                    <p class="mt-6 text-base leading-8 text-muted">
                        Discover the colors, plates, spaces, and relaxed
                        hospitality that shape every Coast &amp; Cay visit.
                    </p>

                    <a
                        href="{{ route('gallery') }}"
                        class="public-button-primary mt-8">
                        View Gallery
                    </a>
                </div>

                <div
                    data-home-reveal
                    class="grid min-h-[32rem] grid-cols-2 gap-4
                        sm:grid-cols-[1.15fr_0.85fr]">
                    <div class="relative overflow-hidden">
                        @if ($galleryPrimary?->image_url)
                            <x-public.responsive-image
                                :image="$galleryPrimary"
                                :alt="$galleryPrimary->alt_text ?: $galleryPrimary->title ?: 'Caribbean dining experience'"
                                variant="large"
                                sizes="(min-width: 1024px) 38vw, 55vw"
                                width="1100"
                                height="1400"
                                img-class="absolute inset-0 size-full object-cover" />
                        @endif
                    </div>

                    <div class="grid gap-4">
                        <div class="relative overflow-hidden">
                            @if ($gallerySecondary?->image_url)
                                <x-public.responsive-image
                                    :image="$gallerySecondary"
                                    :alt="$gallerySecondary->alt_text ?: $gallerySecondary->title ?: 'Caribbean cuisine presentation'"
                                    variant="large"
                                    sizes="(min-width: 1024px) 25vw, 45vw"
                                    width="850"
                                    height="650"
                                    img-class="absolute inset-0 size-full object-cover" />
                            @endif
                        </div>

                        <div class="relative overflow-hidden">
                            @if ($galleryTertiary?->image_url)
                                <x-public.responsive-image
                                    :image="$galleryTertiary"
                                    :alt="$galleryTertiary->alt_text ?: $galleryTertiary->title ?: 'Warm restaurant hospitality'"
                                    variant="large"
                                    sizes="(min-width: 1024px) 25vw, 45vw"
                                    width="850"
                                    height="650"
                                    img-class="absolute inset-0 size-full object-cover" />
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Render only when published journal content exists. --}}
        <x-public.blog-preview />

        {{-- Final ordering and visit panel --}}
        <section
            id="visit"
            data-home-panel
            data-home-label="Visit"
            aria-labelledby="home-visit-heading"
            class="home-panel relative isolate overflow-hidden
                bg-primary-deep text-white">
            @if ($heroImage?->image_url)
                <div class="absolute inset-0 -z-30">
                    <x-public.responsive-image
                        :image="$heroImage"
                        :alt="$heroImage->alt_text ?: $heroImage->title ?: 'Caribbean food and coastal atmosphere'"
                        variant="hero"
                        sizes="100vw"
                        width="2000"
                        height="1300"
                        img-class="size-full object-cover" />
                </div>
            @endif

            <div
                class="absolute inset-0 -z-20
                    bg-[linear-gradient(90deg,rgba(4,38,34,0.97)_0%,rgba(4,38,34,0.92)_48%,rgba(4,38,34,0.70)_100%)]"
                aria-hidden="true">
            </div>

            <div
                class="public-container grid items-center gap-12 py-24
                    lg:grid-cols-[1.25fr_0.75fr] lg:py-28">
                <div data-home-reveal class="max-w-3xl">
                    <p
                        class="text-xs font-semibold uppercase
                            tracking-[0.22em] text-coral">
                        Come Hungry. Leave Happy.
                    </p>

                    <h2
                        id="home-visit-heading"
                        class="mt-5 font-display text-5xl leading-[1.02]
                            text-white sm:text-6xl">
                        Ready for good food and island vibes?
                    </h2>

                    <p class="mt-6 max-w-2xl text-base leading-8 text-white/72">
                        Explore the current menu and enjoy
                        {{ $restaurantName }} through pickup or eligible local
                        delivery.
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ route('menu') }}"
                            class="public-button-primary">
                            Explore Menu
                        </a>

                        <a
                            href="{{ route('menu') }}"
                            class="inline-flex min-h-12 items-center
                                justify-center rounded-xl border border-white/50
                                px-6 text-xs font-semibold uppercase
                                tracking-[0.14em] text-white transition
                                hover:bg-white hover:text-primary-deep">
                            Order Online
                        </a>

                        <a
                            href="{{ route('contact.create') }}"
                            class="inline-flex min-h-12 items-center
                                justify-center px-5 text-xs font-semibold
                                uppercase tracking-[0.14em] text-white/80
                                transition hover:text-white">
                            Contact Us
                        </a>
                    </div>
                </div>

                <aside
                    data-home-reveal
                    class="border border-white/15 bg-black/15 p-7
                        backdrop-blur-md sm:p-9"
                    aria-label="Restaurant visit information">
                    <h3 class="font-display text-3xl text-white">
                        Plan Your Visit
                    </h3>

                    @if ($address)
                        <div class="mt-7">
                            <p
                                class="text-xs font-semibold uppercase
                                    tracking-[0.18em] text-sun">
                                Location
                            </p>

                            <p class="mt-2 text-sm leading-7 text-white/72">
                                {{ $address }}
                            </p>

                            @if ($mapLink)
                                <a
                                    href="{{ $mapLink }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-2 inline-flex text-sm
                                        font-semibold text-coral transition
                                        hover:text-white">
                                    Get directions &rarr;
                                </a>
                            @endif
                        </div>
                    @endif

                    <div class="mt-7">
                        <p
                            class="text-xs font-semibold uppercase
                                tracking-[0.18em] text-sun">
                            Opening Hours
                        </p>

                        <p
                            class="mt-2 whitespace-pre-line text-sm
                                leading-7 text-white/72">
                            {{ $openingHours ?: 'Opening hours will be published soon.' }}
                        </p>
                    </div>

                    @if ($phone)
                        <div class="mt-7">
                            <p
                                class="text-xs font-semibold uppercase
                                    tracking-[0.18em] text-sun">
                                Call Us
                            </p>

                            <p class="mt-2 text-sm text-white/72">
                                {{ $phone }}
                            </p>
                        </div>
                    @endif
                </aside>
            </div>
        </section>
    </div>
</x-layouts.public>
