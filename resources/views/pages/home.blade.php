<x-layouts.public
    :title="$page?->meta_title ?: ($settings['meta_title'] ?? 'Home')"
    :description="$page?->meta_description ?: ($settings['meta_description'] ?? 'Caribbean food, warm hospitality, and California ease.')">
    @php
    $restaurantName = $settings['restaurant_name']
    ?? config('app.name');

    $phone = $settings['phone'] ?? null;

    $phoneDigits = is_string($phone)
    ? preg_replace('/\D+/', '', $phone)
    : null;

    $phoneTelTarget = is_string($phone)
    && is_string($phoneDigits)
    && $phoneDigits !== ''
    ? (str_starts_with(ltrim($phone), '+') ? '+' : '').$phoneDigits
    : null;

    $orderUrl = \Illuminate\Support\Facades\Route::has('cart.index')
    ? route('cart.index')
    : route('menu');
    @endphp

    <div data-home-motion>
        <x-public.homepage-hero
            eyebrow="Caribbean flavor, California ease"
            title="{{ $page?->title ?: 'Island Hospitality, Made for the California Coast' }}"
            description="{{ $page?->excerpt ?: 'A warm gathering place for vibrant Caribbean dishes, thoughtful drinks, and relaxed hospitality.' }}"
            :image="$heroImage"
            :image-alt="$heroImage?->alt_text ?: $heroImage?->title ?: 'Warm Caribbean restaurant dining room'"
            primary-label="Order Online"
            :primary-url="$orderUrl"
            secondary-label="Reserve a Table"
            :secondary-url="route('reservation-request.create')" />

        <section
            data-gsap="section"
            class="public-island-pattern bg-brand-cream py-20 lg:py-28">
            <div class="public-container">
                <x-public.section-heading
                    eyebrow="Explore the Menu"
                    title="Flavors made for sharing"
                    description="Discover vibrant dishes, comforting favorites, and plates inspired by the generous spirit of the Caribbean."
                    theme="light" />

                <div class="mt-12 grid gap-5 md:grid-cols-3">
                    @forelse ($featuredCategories as $category)
                    <a
                        href="{{ route('menu') }}#category-{{ $category->slug }}"
                        data-gsap-reveal
                        class="group relative overflow-hidden rounded-island
                                border border-brand-palm/10 bg-white p-7
                                shadow-island transition duration-300
                                hover:-translate-y-1 hover:border-brand-coral/40">
                        <span
                            class="text-xs font-semibold uppercase
                                    tracking-[0.22em] text-brand-coral-dark">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <h2
                            class="mt-8 font-display text-3xl text-brand-palm
                                    transition group-hover:text-brand-coral-dark">
                            {{ $category->name }}
                        </h2>

                        @if ($category->description)
                        <p class="mt-4 text-sm leading-7 text-brand-muted">
                            {{ $category->description }}
                        </p>
                        @endif

                        <span
                            class="mt-8 inline-flex items-center gap-2 text-xs
                                    font-semibold uppercase tracking-[0.18em]
                                    text-brand-palm">
                            Browse dishes
                            <span
                                class="transition group-hover:translate-x-1"
                                aria-hidden="true">
                                &rarr;
                            </span>
                        </span>
                    </a>
                    @empty
                    <x-public.alert type="warning" class="md:col-span-3">
                        Our menu categories are being prepared.
                    </x-public.alert>
                    @endforelse
                </div>
            </div>
        </section>

        <section
            id="restaurant-story"
            data-gsap="section"
            class="overflow-hidden bg-white py-24 lg:py-32">
            <div class="public-container grid items-center gap-14 lg:grid-cols-2 lg:gap-20">
                <div class="relative mx-auto w-full max-w-xl lg:mx-0">
                    <div
                        data-gsap="frame"
                        class="absolute -bottom-7 -right-7 hidden h-2/3 w-2/3
                            rounded-island border border-brand-coral/45 lg:block"
                        aria-hidden="true"></div>

                    <div
                        data-gsap="image"
                        class="relative aspect-[4/5] overflow-hidden rounded-island
                            bg-brand-sand-soft shadow-island">
                        @if ($storyImage?->image_url)
                        <x-public.responsive-image
                            :image="$storyImage"
                            :alt="$storyImage->alt_text ?: $storyImage->title ?: 'Caribbean dish prepared with care'"
                            variant="large"
                            sizes="(min-width: 1024px) 45vw, 100vw"
                            width="960"
                            height="1200"
                            img-class="h-full w-full object-cover" />
                        @else
                        <div
                            class="absolute inset-0 bg-[radial-gradient(circle_at_30%_25%,rgba(242,199,107,0.34),transparent_32%),linear-gradient(145deg,#206f7c,#0c342b)]"></div>

                        <p
                            class="absolute inset-x-8 bottom-8 border-t
                                    border-white/35 pt-4 text-xs font-semibold
                                    uppercase tracking-[0.22em] text-white/80">
                            Restaurant photography coming soon
                        </p>
                        @endif
                    </div>
                </div>

                <div>
                    <x-public.section-heading
                        eyebrow="Our Story"
                        title="A table that feels like an island welcome"
                        :description="$page?->content ?: 'Coast & Cay brings Caribbean warmth to California through generous food, relaxed hospitality, and a dining room made for gathering.'"
                        align="left"
                        theme="light" />

                    <p class="mt-8 max-w-xl text-base leading-8 text-brand-muted">
                        Come for a quick lunch, stay for a long dinner, or gather
                        the people you love around dishes made with patience,
                        color, and care.
                    </p>

                    <a
                        href="{{ route('about') }}"
                        class="group mt-9 inline-flex items-center gap-3 text-xs
                            font-semibold uppercase tracking-[0.18em]
                            text-brand-palm transition hover:text-brand-coral-dark">
                        Read our story

                        <span
                            class="transition duration-300 group-hover:translate-x-2"
                            aria-hidden="true">
                            &rarr;
                        </span>
                    </a>
                </div>
            </div>
        </section>

        <section
            data-gsap="menu"
            class="public-island-pattern bg-brand-palm-dark py-24 lg:py-32">
            <div class="public-container">
                <x-public.section-heading
                    eyebrow="Featured Dishes"
                    title="A few favorites from the kitchen"
                    description="A preview of dishes selected for their color, comfort, and unmistakable character." />

                <div class="mt-14 grid gap-10 md:grid-cols-2 lg:grid-cols-3 lg:gap-8">
                    @forelse ($featuredMenuItems as $item)
                    <x-public.menu-card :item="$item" variant="luxury" />
                    @empty
                    <x-public.alert type="warning" class="md:col-span-2 lg:col-span-3">
                        Our latest menu selections are being prepared.
                    </x-public.alert>
                    @endforelse
                </div>

                <div class="mt-14 text-center">
                    <a
                        href="{{ route('menu') }}"
                        class="public-button-secondary text-brand-cream">
                        View Full Menu
                    </a>
                </div>
            </div>
        </section>

        <section class="grid lg:grid-cols-2">
            <article
                data-gsap="panel"
                class="relative isolate flex min-h-[31rem] items-center
                    overflow-hidden bg-brand-ocean px-6 py-20 sm:px-10 lg:px-16">
                <div
                    class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_82%_18%,rgba(242,199,107,0.25),transparent_30%)]"></div>

                <div class="mx-auto max-w-lg text-center text-white">
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.28em]
                            text-brand-sun">
                        Stay awhile
                    </p>

                    <h2 class="mt-5 font-display text-4xl leading-tight sm:text-5xl">
                        Settle in and enjoy the table
                    </h2>

                    <p class="mt-6 text-base leading-8 text-white/75">
                        Share your preferred date, time, and party size. Our
                        team will review the request and confirm availability.
                    </p>

                    <a
                        href="{{ route('reservation-request.create') }}"
                        class="public-button-primary mt-9">
                        Reserve a Table
                    </a>
                </div>
            </article>

            <article
                data-gsap="panel"
                class="relative isolate flex min-h-[31rem] items-center
                    overflow-hidden bg-brand-sand-soft px-6 py-20
                    text-brand-forest sm:px-10 lg:px-16">
                <div
                    class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_18%_20%,rgba(230,110,80,0.18),transparent_30%)]"></div>

                <div class="mx-auto max-w-lg text-center">
                    <p class="public-eyebrow">Bring it home</p>

                    <h2 class="mt-5 font-display text-4xl leading-tight sm:text-5xl">
                        Island comfort, ready when you are
                    </h2>

                    <p class="mt-6 text-base leading-8 text-brand-muted">
                        Browse the current menu today. Transactional ordering,
                        pickup, and local delivery will be connected in the
                        upcoming commerce phases.
                    </p>

                    <a
                        href="{{ $orderUrl }}"
                        class="public-button-secondary mt-9 text-brand-palm">
                        Browse the Menu
                    </a>
                </div>
            </article>
        </section>

        <section
            data-gsap="gallery"
            class="bg-brand-cream py-24 lg:py-32">
            <div class="public-container">
                <x-public.section-heading
                    eyebrow="From the Restaurant"
                    title="Food, color, and easy evenings"
                    description="A look at the plates, rooms, and warm details that shape the Coast & Cay experience."
                    theme="light" />

                <x-public.home-gallery-carousel :gallery-images="$galleryImages" />

                <div class="mt-12 text-center">
                    <a
                        href="{{ route('gallery') }}"
                        class="public-button-secondary text-brand-palm">
                        Discover the Gallery
                    </a>
                </div>
            </div>
        </section>

        <section
            data-gsap="section"
            class="bg-brand-coral py-20 text-white lg:py-24">
            <div
                class="public-container grid gap-12 lg:grid-cols-[1.15fr_0.85fr]
                    lg:items-center">
                <div>
                    <p
                        data-gsap-reveal
                        class="text-xs font-semibold uppercase
                            tracking-[0.28em] text-white/75">
                        Visit Coast & Cay
                    </p>

                    <h2
                        data-gsap-reveal
                        class="mt-5 max-w-3xl font-display text-4xl
                            leading-tight sm:text-5xl">
                        Come hungry. Leave feeling looked after.
                    </h2>

                    <p
                        data-gsap-reveal
                        class="mt-6 max-w-2xl text-base leading-8 text-white/80">
                        Explore the menu, request a table, or contact the
                        restaurant team about your upcoming visit.
                    </p>
                </div>

                <dl class="grid content-start gap-7 border-white/25 lg:border-l lg:pl-12">
                    @if ($settings['address'] ?? null)
                    <div data-gsap-reveal>
                        <dt
                            class="text-xs font-semibold uppercase
                                    tracking-[0.2em] text-white/65">
                            Location
                        </dt>

                        <dd class="mt-2 text-base leading-7">
                            {{ $settings['address'] }}
                        </dd>
                    </div>
                    @endif

                    @if ($settings['opening_hours'] ?? null)
                    <div data-gsap-reveal>
                        <dt
                            class="text-xs font-semibold uppercase
                                    tracking-[0.2em] text-white/65">
                            Opening Hours
                        </dt>

                        <dd class="mt-2 text-base leading-7">
                            {{ $settings['opening_hours'] }}
                        </dd>
                    </div>
                    @endif

                    @if ($phoneTelTarget !== null)
                    <div data-gsap-reveal>
                        <dt
                            class="text-xs font-semibold uppercase
                                    tracking-[0.2em] text-white/65">
                            Phone
                        </dt>

                        <dd class="mt-2">
                            <a href="tel:{{ $phoneTelTarget }}" class="hover:underline">
                                {{ $phone }}
                            </a>
                        </dd>
                    </div>
                    @endif

                    <div data-gsap-reveal class="flex flex-wrap gap-3 pt-2">
                        <a
                            href="{{ route('contact.create') }}"
                            class="inline-flex min-h-12 items-center justify-center
                                rounded-full bg-brand-palm-dark px-6 py-3
                                text-xs font-semibold uppercase tracking-[0.17em]
                                text-white transition hover:-translate-y-0.5">
                            Contact Us
                        </a>

                        <a
                            href="{{ route('reservation-request.create') }}"
                            class="inline-flex min-h-12 items-center justify-center
                                rounded-full border border-white/60 px-6 py-3
                                text-xs font-semibold uppercase tracking-[0.17em]
                                text-white transition hover:bg-white
                                hover:text-brand-coral-dark">
                            Reserve a Table
                        </a>
                    </div>
                </dl>
            </div>
        </section>
    </div>
</x-layouts.public>