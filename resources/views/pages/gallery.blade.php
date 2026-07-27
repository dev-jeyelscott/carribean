<x-layouts.public
    :title="$page?->meta_title ?: 'Gallery | Coast & Cay'"
    :description="$page?->meta_description ?: 'Explore Coast & Cay food, drinks, hospitality, and relaxed island-inspired details.'">
    @php
    $heroImage = $galleryImages->first();
    $supportingImages = $galleryImages->skip(1)->take(2)->values();
    $categories = $galleryImages
    ->pluck('category')
    ->filter()
    ->unique()
    ->values();
    @endphp

    <div data-home-motion data-gallery-motion>
        <section
            data-public-hero
            class="public-hero-viewport relative isolate flex items-center
                overflow-hidden bg-brand-palm-dark">
            @if ($heroImage?->image_url)
            <div data-gsap="hero-image" class="absolute inset-0 -z-30">
                <x-public.responsive-image
                    :image="$heroImage"
                    :alt="$heroImage->alt_text ?: $heroImage->title ?: 'Coast and Cay restaurant gallery'"
                    variant="hero"
                    sizes="100vw"
                    width="1920"
                    height="1280"
                    loading="eager"
                    fetchpriority="high"
                    img-class="h-full w-full object-cover object-center" />
            </div>
            @else
            <div
                class="absolute inset-0 -z-30
                    bg-[radial-gradient(circle_at_70%_28%,rgba(242,199,107,0.25),transparent_34%),linear-gradient(145deg,#206f7c,#0c342b_68%)]">
            </div>
            @endif

            <div
                class="absolute inset-0 -z-20
                    bg-[linear-gradient(to_bottom,rgba(12,52,43,0.38),rgba(12,52,43,0.28)_32%,rgba(12,52,43,0.94))]">
            </div>

            <div
                class="absolute inset-0 -z-10 bg-gradient-to-r
                    from-brand-palm-dark/88 via-brand-palm-dark/35
                    to-transparent">
            </div>

            <div
                class="public-container pb-12 pt-28
                    sm:pb-20 sm:pt-36 lg:pb-28 lg:pt-44">
                <div data-gsap="hero-content" class="max-w-4xl">
                    <p
                        data-gsap-reveal
                        class="text-xs font-semibold uppercase tracking-[0.38em]
                            text-brand-sun sm:text-sm">
                        The Gallery
                    </p>

                    <h1
                        data-gsap-reveal
                        class="mt-6 max-w-4xl font-display text-5xl
                            leading-[0.98] text-white sm:text-6xl lg:text-8xl">
                        {{ $page?->title ?: 'Food, Color, and Easy Evenings' }}
                    </h1>

                    <p
                        data-gsap-reveal
                        class="mt-7 max-w-2xl text-base leading-8
                            text-white/78 sm:text-lg">
                        {{ $page?->excerpt ?: 'A look at the dishes, rooms, and warm details shaping the Coast & Cay experience.' }}
                    </p>

                    <div
                        data-gsap-reveal
                        class="mt-10 flex flex-col gap-4 sm:flex-row">
                        <a
                            href="#gallery-collection"
                            class="public-button-primary">
                            View the Gallery
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section
            data-gsap="section"
            class="overflow-hidden bg-brand-cream py-24
                text-brand-forest lg:py-32">
            <div
                class="public-container grid items-center gap-16
                    lg:grid-cols-[0.9fr_1.1fr]">
                <div>
                    <x-public.section-heading
                        eyebrow="The Experience"
                        title="A taste of the island"
                        :description="$page?->content ?: 'From vibrant plates to a warm dining room, every image reflects generous Caribbean hospitality and California ease.'"
                        align="left"
                        theme="light" />

                    <div
                        data-gsap-reveal
                        class="mt-10 grid max-w-xl grid-cols-2 gap-8
                            border-t border-brand-palm/15 pt-8">
                        <div
                            data-gsap-counter
                            data-count-value="{{ $galleryImages->count() }}">
                            <p
                                data-gsap-count
                                class="font-display text-4xl text-brand-forest">
                                {{ $galleryImages->count() }}
                            </p>
                            <p
                                class="mt-2 text-xs font-semibold uppercase
                                    tracking-[0.2em] text-brand-coral-dark">
                                Moments on this page
                            </p>
                        </div>

                        <div
                            data-gsap-counter
                            data-count-value="{{ $categories->count() }}">
                            <p
                                data-gsap-count
                                class="font-display text-4xl text-brand-forest">
                                {{ $categories->count() }}
                            </p>
                            <p
                                class="mt-2 text-xs font-semibold uppercase
                                    tracking-[0.2em] text-brand-coral-dark">
                                Categories on this page
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative min-h-[27rem] sm:min-h-[34rem]">
                    <div
                        data-gsap="frame"
                        class="absolute left-0 top-0 h-[82%] w-[78%]
                            border border-brand-coral/45"
                        aria-hidden="true">
                    </div>

                    @if ($supportingImages->count() >= 2)
                    @foreach ($supportingImages as $image)
                    <figure
                        data-gsap="image"
                        @class([
                            'absolute overflow-hidden rounded-island bg-white shadow-island',
                            'left-5 top-5 h-[72%] w-[72%]' => $loop->first,
                            'bottom-0 right-0 h-[52%] w-[52%] border-8 border-brand-cream' => $loop->last,
                        ])>
                        @if ($image->image_url)
                        <x-public.responsive-image
                            :image="$image"
                            :alt="$image->alt_text ?: $image->title ?: 'Restaurant gallery image'"
                            variant="large"
                            sizes="(min-width: 1024px) 40vw, 75vw"
                            width="1200"
                            height="900"
                            img-class="h-full w-full object-cover" />
                        @endif
                    </figure>
                    @endforeach
                    @elseif ($heroImage?->image_url)
                    <figure
                        data-gsap="image"
                        class="absolute bottom-0 right-0 h-[88%] w-[88%]
                            overflow-hidden rounded-island bg-white
                            shadow-island">
                        <x-public.responsive-image
                            :image="$heroImage"
                            :alt="$heroImage->alt_text ?: $heroImage->title ?: 'Restaurant gallery image'"
                            variant="large"
                            sizes="(min-width: 1024px) 45vw, 88vw"
                            width="1200"
                            height="900"
                            img-class="h-full w-full object-cover" />
                    </figure>
                    @else
                    <div
                        class="absolute bottom-0 right-0 h-[88%] w-[88%]
                            rounded-island
                            bg-[radial-gradient(circle_at_65%_28%,rgba(242,199,107,0.3),transparent_34%),linear-gradient(145deg,#e8d6b8,#206f7c)]
                            shadow-island">
                    </div>
                    @endif
                </div>
            </div>
        </section>

        <section
            id="gallery-collection"
            data-gsap="gallery"
            class="scroll-mt-24 bg-brand-palm-dark py-24 lg:py-32">
            <div class="public-container">
                <x-public.section-heading
                    eyebrow="The Collection"
                    title="Food, hospitality, and coastal moments"
                    description="Explore colorful dishes, relaxed spaces, and the warm details that make Coast & Cay feel welcoming." />

                @if ($categories->isNotEmpty())
                <ul
                    class="mt-10 flex flex-wrap justify-center gap-3"
                    aria-label="Gallery categories">
                    @foreach ($categories as $category)
                    <li
                        data-gsap-reveal
                        class="rounded-full border border-white/15 px-4 py-2
                            text-[0.65rem] font-semibold uppercase
                            tracking-[0.22em] text-white/72">
                        {{ $category }}
                    </li>
                    @endforeach
                </ul>
                @endif

                @if ($galleryImages->isNotEmpty())
                <div
                    data-gsap="gallery-collection"
                    class="mt-14 grid gap-4 md:auto-rows-[18rem]
                        md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($galleryImages as $image)
                    @php
                    $patternIndex = $loop->index % 8;
                    $cardClass = match ($patternIndex) {
                    0 => 'md:col-span-2 md:row-span-2 lg:col-span-2',
                    3 => 'lg:row-span-2',
                    5 => 'md:col-span-2 lg:col-span-1',
                    6 => 'lg:col-span-2',
                    default => '',
                    };
                    @endphp

                    <x-public.gallery-card
                        :image="$image"
                        variant="editorial"
                        :class="$cardClass" />
                    @endforeach
                </div>

                @if ($galleryImages->hasPages())
                <div
                    data-gsap="section"
                    class="mt-14 border-t border-white/10 pt-10">
                    {{ $galleryImages->links() }}
                </div>
                @endif
                @else
                <x-public.alert type="warning" class="mt-14">
                    Our gallery is currently being curated. Please check back
                    soon for more Coast & Cay moments.
                </x-public.alert>
                @endif
            </div>
        </section>

        <section class="grid lg:grid-cols-2">
            <article
                data-gsap="panel"
                class="relative isolate flex min-h-[30rem] items-center
                    overflow-hidden bg-brand-ocean px-5 py-20 text-white
                    sm:px-10 lg:px-16">
                @if ($heroImage?->image_url)
                <div data-gsap="parallax" class="absolute inset-0 -z-20">
                    <x-public.responsive-image
                        :image="$heroImage"
                        alt=""
                        variant="large"
                        sizes="(min-width: 1024px) 50vw, 100vw"
                        width="1200"
                        height="900"
                        img-class="h-full w-full object-cover opacity-25" />
                </div>
                @endif

                <div class="absolute inset-0 -z-10 bg-brand-palm-dark/70"></div>

                <div data-gsap-reveal class="mx-auto max-w-lg text-center">
                    <p
                        class="text-xs font-semibold uppercase
                            tracking-[0.3em] text-brand-sun">
                        Island Hospitality
                    </p>
                    <h2
                        class="mt-5 font-display text-4xl leading-tight
                            sm:text-5xl">
                        Food made for sharing
                    </h2>
                    <p class="mt-6 text-base leading-8 text-white/72">
                        Coast & Cay brings vibrant food, thoughtful drinks,
                        and an easy welcome to every table.
                    </p>
                </div>
            </article>

            <article
                data-gsap="panel"
                class="relative isolate flex min-h-[30rem] items-center
                    overflow-hidden bg-brand-sand-soft px-5 py-20
                    text-brand-forest sm:px-10 lg:px-16">
                <div
                    class="absolute inset-0 -z-10
                        bg-[radial-gradient(circle_at_75%_22%,rgba(230,110,80,0.18),transparent_35%)]">
                </div>

                <div data-gsap-reveal class="mx-auto max-w-lg text-center">
                    <p
                        class="text-xs font-semibold uppercase
                            tracking-[0.3em] text-brand-coral-dark">
                        Need a Hand?
                    </p>
                    <h2
                        class="mt-5 font-display text-4xl leading-tight
                            sm:text-5xl">
                        Questions about the menu or an order?
                    </h2>
                    <p
                        class="mt-6 text-base leading-8 text-brand-muted">
                        Contact the restaurant team for menu questions,
                        online-order support, directions, or accessibility
                        information.
                    </p>
                    <a
                        href="{{ route('contact.create') }}"
                        class="public-button-primary mt-9">
                        Contact Us
                    </a>
                </div>
            </article>
        </section>
    </div>
</x-layouts.public>
