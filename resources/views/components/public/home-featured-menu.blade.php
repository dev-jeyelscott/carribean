@props([
    'items',
])

@php
    /*
     * Keep the homepage preview intentionally concise.
     *
     * The full Menu page owns the complete catalogue. Three dishes preserve
     * comfortable card widths and prevent the editorial column from becoming
     * compressed on common laptop and desktop viewports.
     */
    $menuItems = collect($items)
        ->take(3)
        ->values();
@endphp

<section
    id="featured"
    data-home-panel
    data-home-label="Featured dishes"
    aria-labelledby="featured-dishes-heading"
    class="home-panel home-featured-menu public-paper-texture
        relative isolate overflow-hidden bg-canvas">
    <div
        class="home-featured-menu__glow home-featured-menu__glow--coral"
        aria-hidden="true">
    </div>

    <div
        class="home-featured-menu__glow home-featured-menu__glow--ocean"
        aria-hidden="true">
    </div>

    <div
        class="public-container relative z-10 grid items-center gap-12
            pb-16 pt-28 sm:pt-32 lg:pb-20 lg:pt-32
            xl:grid-cols-[minmax(18rem,0.92fr)_minmax(0,3.08fr)]
            xl:gap-12">
        <aside
            data-home-reveal
            class="max-w-xl xl:max-w-md">
            <p class="public-eyebrow">
                Chef's favorites
            </p>

            <h2
                id="featured-dishes-heading"
                class="mt-4 max-w-[11ch] font-display text-5xl leading-[0.96]
                    text-ink sm:text-6xl xl:text-[4.15rem]">
                A little taste of the islands.
            </h2>

            <p class="mt-6 max-w-md text-sm leading-7 text-muted sm:text-base">
                Familiar Caribbean comfort, layered seasoning, and generous
                plates prepared for sharing.
            </p>

            <div
                class="mt-7 grid grid-cols-2 gap-5 border-y border-line py-5">
                <div>
                    <p class="font-display text-lg text-primary">
                        Made fresh
                    </p>

                    <p class="mt-1 text-xs leading-5 text-muted">
                        Prepared with care for every order.
                    </p>
                </div>

                <div>
                    <p class="font-display text-lg text-primary">
                        Easy ordering
                    </p>

                    <p class="mt-1 text-xs leading-5 text-muted">
                        Review every option before adding a dish.
                    </p>
                </div>
            </div>

            <a
                href="{{ route('menu') }}"
                class="public-button-primary mt-7">
                Explore Full Menu
            </a>
        </aside>

        @if ($menuItems->isNotEmpty())
            <div
                class="grid min-w-0 gap-5 sm:grid-cols-2
                    xl:grid-cols-3 xl:gap-5">
                @foreach ($menuItems as $item)
                    <div
                        data-home-reveal
                        data-home-product-card
                        class="min-w-0">
                        <x-public.menu-card
                            :item="$item"
                            :use-modal="false"
                            :show-category="true"
                            :eager="$loop->index < 2"
                            :priority="false" />
                    </div>
                @endforeach
            </div>
        @else
            <x-public.alert
                type="warning"
                class="xl:self-center">
                Our chef's selections are being prepared.
            </x-public.alert>
        @endif
    </div>
</section>
