@props([
    'page' => null,
    'image' => null,
])

<section
    id="menu-hero"
    data-public-hero
    data-menu-hero
    aria-labelledby="menu-hero-heading"
    class="relative isolate flex min-h-[100svh] items-center
        overflow-hidden bg-primary-deep text-white">
    @if ($image?->image_url)
        <div
            data-menu-hero-depth
            data-gsap="hero-image"
            class="absolute inset-0 -z-30 overflow-hidden">
            <x-public.responsive-image
                :image="$image"
                :alt="$image->image_alt_text
                    ?: $image->name
                    ?: 'Caribbean dish prepared by Coast and Cay'"
                variant="hero"
                sizes="100vw"
                width="1920"
                height="1280"
                loading="eager"
                fetchpriority="high"
                img-class="size-full object-cover object-center" />
        </div>
    @else
        <div
            data-menu-hero-depth
            data-gsap="hero-image"
            class="absolute inset-0 -z-30
                bg-[radial-gradient(circle_at_72%_24%,rgba(242,199,107,0.26),transparent_28%),linear-gradient(135deg,#206f7c,#0c342b_68%)]"
            aria-hidden="true">
        </div>
    @endif

    <div
        class="absolute inset-0 -z-20
            bg-[linear-gradient(90deg,rgba(4,21,17,0.94)_0%,rgba(5,27,22,0.82)_42%,rgba(5,27,22,0.34)_76%,rgba(5,27,22,0.18)_100%)]"
        aria-hidden="true">
    </div>

    <div
        class="absolute inset-x-0 bottom-0 -z-20 h-64
            bg-gradient-to-t from-primary-deep to-transparent"
        aria-hidden="true">
    </div>

    <div
        class="public-container relative z-10 pb-24 pt-36
            sm:pb-28 sm:pt-40 lg:pb-32 lg:pt-44">
        <div
            data-gsap="hero-content"
            class="max-w-4xl">
            <p
                data-menu-hero-item
                class="text-xs font-semibold uppercase tracking-[0.28em]
                    text-sun">
                From our kitchen
            </p>

            <h1
                id="menu-hero-heading"
                data-menu-hero-item
                class="mt-5 max-w-[12ch] font-display text-5xl
                    leading-[0.94] tracking-[-0.02em] text-white
                    sm:text-6xl lg:text-7xl xl:text-[5.4rem]">
                {{ $page?->title
                    ?: 'Island Favorites, Made to Gather Around' }}
            </h1>

            <p
                data-menu-hero-item
                class="mt-7 max-w-2xl text-base leading-8 text-white/80
                    sm:text-lg">
                {{ $page?->excerpt
                    ?: 'Explore colorful starters, generous mains, sweet finishes, and drinks made for slow afternoons and lively evenings.' }}
            </p>

            <div
                data-menu-hero-item
                class="mt-9 flex flex-col gap-3 sm:flex-row">
                <a
                    href="#menu-catalog"
                    class="public-button-primary">
                    Explore the selections

                    <span class="ml-2" aria-hidden="true">
                        &darr;
                    </span>
                </a>

                <a
                    href="{{ route('cart.index') }}"
                    class="inline-flex min-h-12 items-center justify-center
                        rounded-xl border border-white/50 px-6 text-xs
                        font-semibold uppercase tracking-[0.14em]
                        text-white transition hover:bg-white
                        hover:text-primary-deep">
                    Review Your Cart
                </a>
            </div>
        </div>
    </div>
</section>
