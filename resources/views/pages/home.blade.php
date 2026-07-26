<x-layouts.public
    :title="$page?->meta_title ?: ($settings['meta_title'] ?? 'Home')"
    :description="$page?->meta_description ?: ($settings['meta_description'] ?? 'Caribbean food, warm hospitality, and California ease.')">
    @php
        $restaurantName = $settings['restaurant_name']
            ?? config('app.name');

        $phone = $settings['phone'] ?? null;
        $email = $settings['email'] ?? null;
        $address = $settings['address'] ?? null;
        $openingHours = $settings['opening_hours'] ?? null;
        $mapLink = $settings['map_link'] ?? null;

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
            eyebrow="Caribbean flavors, California ease."
            :title="$page?->title ?: 'Island hospitality, made for the California coast.'"
            :description="$page?->excerpt ?: 'Vibrant Caribbean flavors, fresh local ingredients, and genuine hospitality—welcome to your escape.'"
            :image="$heroImage"
            :image-alt="$heroImage?->alt_text ?: $heroImage?->title ?: 'Caribbean seafood and cocktails beside the California coast'"
            primary-label="Order Online"
            :primary-url="$orderUrl"
            secondary-label="Reserve a Table"
            :secondary-url="route('reservation-request.create')"
            :address="$address"
            :opening-hours="$openingHours"
            fulfillment-label="Pickup and local delivery available"
            :map-url="$mapLink" />

        {{-- Featured menu categories --}}
        <section
            data-gsap="section"
            class="public-island-pattern bg-canvas py-16 sm:py-20 lg:py-24">
            <div class="public-container">
                <x-public.section-heading
                    eyebrow="Explore Our Menu"
                    title="Flavors worth sharing"
                    theme="light" />

                <div
                    class="mt-10 grid gap-4 sm:grid-cols-2
                        lg:grid-cols-4 lg:gap-5">
                    @forelse ($featuredCategories as $category)
                        <x-public.home-category-card
                            :category="$category"
                            :item="$category->visibleMenuItems->first()" />
                    @empty
                        <x-public.alert
                            type="warning"
                            class="sm:col-span-2 lg:col-span-4">
                            Our menu categories are being prepared.
                        </x-public.alert>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Restaurant story --}}
        <section
            id="restaurant-story"
            data-gsap="section"
            class="relative isolate overflow-hidden bg-surface py-20
                lg:py-0">
            <div
                class="pointer-events-none absolute -right-40 top-1/2
                    -z-10 size-[34rem] -translate-y-1/2 rounded-full
                    border border-coral/10"
                aria-hidden="true">
            </div>

            <div
                class="grid items-stretch lg:min-h-[34rem]
                    lg:grid-cols-2">
                <div
                    data-gsap="image"
                    class="relative min-h-[28rem] overflow-hidden
                        bg-surface-soft sm:min-h-[34rem]">
                    @if ($storyImage?->image_url)
                        <x-public.responsive-image
                            :image="$storyImage"
                            :alt="$storyImage->alt_text ?: $storyImage->title ?: 'Warm Coast and Cay restaurant interior'"
                            variant="large"
                            sizes="(min-width: 1024px) 50vw, 100vw"
                            width="1200"
                            height="900"
                            img-class="absolute inset-0 h-full w-full
                                object-cover" />
                    @else
                        <div
                            class="absolute inset-0
                                bg-[radial-gradient(circle_at_30%_20%,rgba(242,199,107,0.30),transparent_32%),linear-gradient(145deg,#206f7c,#0c342b)]">
                        </div>
                    @endif
                </div>

                <div
                    class="flex items-center px-5 py-16 sm:px-10
                        lg:px-16 lg:py-20 xl:px-24">
                    <div class="max-w-xl">
                        <x-public.section-heading
                            eyebrow="Our Story"
                            title="Caribbean warmth. California ease."
                            :description="$page?->content ?: 'Coast & Cay brings the heart of the Caribbean to the California coast. We celebrate fresh ingredients, vibrant spices, and time-honored recipes in a relaxed, welcoming space where everyone feels at home.'"
                            align="left"
                            theme="light" />

                        <p class="mt-7 text-base leading-8 text-muted">
                            Good food. Good people. Good vibes. That is
                            island life.
                        </p>

                        <a
                            href="{{ route('about') }}"
                            class="public-button-secondary mt-8 text-primary">
                            Learn More About Us
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Featured menu dishes --}}
        <section
            data-gsap="menu"
            class="bg-canvas py-16 sm:py-20 lg:py-24">
            <div class="public-container">
                <x-public.section-heading
                    eyebrow="Chef's Picks"
                    title="Island favorites"
                    theme="light" />

                <div
                    class="mt-10 grid gap-5 sm:grid-cols-2
                        xl:grid-cols-4">
                    @forelse ($featuredMenuItems as $item)
                        <x-public.menu-card
                            :item="$item"
                            variant="default" />
                    @empty
                        <x-public.alert
                            type="warning"
                            class="sm:col-span-2 xl:col-span-4">
                            Our chef's selections are being prepared.
                        </x-public.alert>
                    @endforelse
                </div>

                <div class="mt-10 text-center">
                    <a
                        href="{{ route('menu') }}"
                        class="public-button-secondary text-primary">
                        Explore the Full Menu
                    </a>
                </div>
            </div>
        </section>

        {{-- Fulfillment and dining options --}}
        <section
            data-gsap="section"
            class="bg-surface py-16 sm:py-20 lg:py-24">
            <div class="public-container">
                <x-public.section-heading
                    eyebrow="Ways to Enjoy"
                    title="Dine your way"
                    theme="light" />

                <div class="mt-10 grid gap-5 lg:grid-cols-3">
                    <x-public.home-service-card
                        title="Dine In"
                        description="Relax in our coastal space and enjoy full-service dining."
                        icon="dine-in"
                        tone="primary" />

                    <x-public.home-service-card
                        title="Pickup"
                        description="Order ahead and we will have it ready when you arrive."
                        icon="pickup"
                        tone="ocean" />

                    <x-public.home-service-card
                        title="Local Delivery"
                        description="We deliver island flavor to your door—fast and fresh."
                        icon="delivery"
                        tone="coral" />
                </div>
            </div>
        </section>

        {{-- Editorial gallery mosaic --}}
        <section
            data-gsap="section"
            class="bg-canvas py-16 sm:py-20 lg:py-24">
            <div class="public-container">
                <x-public.section-heading
                    eyebrow="Gallery"
                    title="A taste of the island"
                    theme="light" />

                @if ($galleryImages->isNotEmpty())
                    <div
                        class="mt-10 grid gap-3
                            lg:grid-cols-[0.85fr_1.7fr_0.85fr]">
                        <x-public.gallery-tile
                            :image="$galleryImages->get(0)"
                            class="min-h-80 lg:min-h-[34rem]"
                            sizes="(min-width: 1024px) 24vw, 100vw" />

                        <div class="grid gap-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <x-public.gallery-tile
                                    :image="$galleryImages->get(1)"
                                    class="min-h-56"
                                    sizes="(min-width: 1024px) 23vw, 50vw" />

                                <x-public.gallery-tile
                                    :image="$galleryImages->get(2)"
                                    class="min-h-56"
                                    sizes="(min-width: 1024px) 23vw, 50vw" />
                            </div>

                            <div
                                class="grid gap-3 sm:grid-cols-3
                                    lg:min-h-[20.25rem]">
                                <x-public.gallery-tile
                                    :image="$galleryImages->get(3)"
                                    class="min-h-52"
                                    sizes="(min-width: 1024px) 15vw, 33vw" />

                                <x-public.gallery-tile
                                    :image="$galleryImages->get(4)"
                                    class="min-h-52"
                                    sizes="(min-width: 1024px) 15vw, 33vw" />

                                <x-public.gallery-tile
                                    :image="$galleryImages->get(5)"
                                    class="min-h-52"
                                    sizes="(min-width: 1024px) 15vw, 33vw" />
                            </div>
                        </div>

                        <x-public.gallery-tile
                            :image="$galleryImages->get(6)"
                            class="min-h-80 lg:min-h-[34rem]"
                            sizes="(min-width: 1024px) 24vw, 100vw" />
                    </div>

                    <div class="mt-10 text-center">
                        <a
                            href="{{ route('gallery') }}"
                            class="public-button-secondary text-primary">
                            View the Gallery
                        </a>
                    </div>
                @else
                    <x-public.alert type="warning" class="mt-10">
                        Restaurant photography is being prepared.
                    </x-public.alert>
                @endif
            </div>
        </section>

        {{-- Location and visiting information --}}
        <section
            data-gsap="section"
            class="bg-surface pb-0 pt-16 sm:pt-20 lg:pt-24">
            <div
                class="public-container grid overflow-hidden
                    rounded-t-panel lg:grid-cols-[0.94fr_1.06fr]">
                <article
                    data-gsap="panel"
                    class="bg-primary-deep px-7 py-12 text-white
                        sm:px-10 lg:px-14 lg:py-16">
                    <p
                        class="text-xs font-semibold uppercase
                            tracking-[0.26em] text-coral">
                        Visit Us
                    </p>

                    <h2
                        class="mt-5 max-w-md font-display text-4xl
                            leading-tight sm:text-5xl">
                        We can’t wait to welcome you
                    </h2>

                    <div
                        class="mt-8 grid gap-7 text-sm leading-7
                            text-white/72 sm:grid-cols-2">
                        <div class="space-y-4">
                            @if ($address)
                                <p>
                                    {{ $address }}
                                </p>
                            @endif

                            @if ($phoneTelTarget)
                                <a
                                    href="tel:{{ $phoneTelTarget }}"
                                    class="block transition hover:text-sun">
                                    {{ $phone }}
                                </a>
                            @endif

                            @if ($email)
                                <a
                                    href="mailto:{{ $email }}"
                                    class="block break-words transition
                                        hover:text-sun">
                                    {{ $email }}
                                </a>
                            @endif
                        </div>

                        @if ($openingHours)
                            <p class="whitespace-pre-line">
                                {{ $openingHours }}
                            </p>
                        @endif
                    </div>

                    <div
                        class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ $orderUrl }}"
                            class="public-button-primary">
                            Order Online
                        </a>

                        <a
                            href="{{ route('reservation-request.create') }}"
                            class="public-button-secondary text-white">
                            Reserve a Table
                        </a>
                    </div>
                </article>

                <a
                    href="{{ $mapLink ?: route('contact.create') }}"
                    @if ($mapLink)
                        target="_blank"
                        rel="noopener noreferrer"
                    @endif
                    data-gsap="panel"
                    class="group relative isolate min-h-[28rem]
                        overflow-hidden bg-[#e8eee7]"
                    aria-label="View {{ $restaurantName }} location">
                    <div
                        class="absolute inset-0 -z-20
                            bg-[linear-gradient(90deg,rgba(22,83,68,0.10)_1px,transparent_1px),linear-gradient(rgba(22,83,68,0.10)_1px,transparent_1px)]
                            bg-[size:3.5rem_3.5rem]">
                    </div>

                    <div
                        class="absolute -bottom-20 -left-16 -z-10
                            h-72 w-[130%] -rotate-6 rounded-[50%]
                            bg-ocean/18">
                    </div>

                    <div
                        class="absolute left-1/2 top-1/2 flex size-16
                            -translate-x-1/2 -translate-y-1/2
                            items-center justify-center rounded-full
                            bg-primary text-white shadow-panel transition
                            duration-300 ease-island group-hover:-translate-y-[55%]
                            group-hover:bg-coral motion-reduce:transform-none
                            motion-reduce:transition-none">
                        <svg
                            class="size-8"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 21s7-5.25 7-12a7 7 0 1 0-14 0c0 6.75 7 12 7 12Z" />

                            <circle cx="12" cy="9" r="2.25" />
                        </svg>
                    </div>

                    <span
                        class="absolute bottom-7 left-1/2
                            -translate-x-1/2 rounded-full bg-surface/90
                            px-5 py-2 text-xs font-semibold text-primary
                            shadow-card backdrop-blur">
                        Get Directions
                    </span>
                </a>
            </div>
        </section>

        {{-- Reservation call to action --}}
        <section
            data-gsap="section"
            class="relative isolate overflow-hidden bg-coral px-5
                py-16 text-center text-white sm:py-20 lg:py-24">
            <div
                class="pointer-events-none absolute inset-0 -z-10
                    bg-[radial-gradient(circle_at_18%_28%,rgba(255,255,255,0.14),transparent_30%),radial-gradient(circle_at_88%_78%,rgba(12,52,43,0.12),transparent_34%)]"
                aria-hidden="true">
            </div>

            <div class="mx-auto max-w-3xl">
                <p
                    data-gsap-reveal
                    class="text-xs font-semibold uppercase
                        tracking-[0.27em] text-white/75">
                    Reserve Your Table
                </p>

                <h2
                    data-gsap-reveal
                    class="mt-5 font-display text-4xl leading-tight
                        sm:text-5xl">
                    Good food. Good company. Good times.
                </h2>

                <p
                    data-gsap-reveal
                    class="mx-auto mt-5 max-w-xl text-base
                        leading-8 text-white/78">
                    Reserve your table and let us take care of the rest.
                </p>

                <a
                    data-gsap-reveal
                    href="{{ route('reservation-request.create') }}"
                    class="public-button-secondary mt-8 text-white">
                    Reserve a Table

                    <span class="ml-2" aria-hidden="true">
                        &rarr;
                    </span>
                </a>
            </div>
        </section>
    </div>
</x-layouts.public>
