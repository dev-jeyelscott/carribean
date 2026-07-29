<div
    id="menu-catalog"
    data-menu-catalog
    class="menu-catalog-shell relative">
    @if ($categories->isNotEmpty())
    <div
        class="sticky top-[5.5rem] z-40 border-y border-primary/10
                bg-canvas/88 backdrop-blur-xl lg:hidden"
        data-menu-mobile-categories>
        <nav
            class="mx-auto flex max-w-full gap-2 overflow-x-auto px-5
                    py-3 [scrollbar-width:none]
                    [&::-webkit-scrollbar]:hidden"
            aria-label="Menu categories"
            data-menu-category-scroller>
            @foreach ($categories as $category)
            <a
                href="#category-{{ $category->slug }}"
                data-menu-category-link
                data-menu-navigation-position="mobile"
                @if ($loop->first) aria-current="true" @endif
                class="menu-category-link inline-flex min-h-11
                shrink-0 items-center gap-2 rounded-full border
                border-primary/10 bg-surface/80 px-4 text-xs
                font-semibold text-primary shadow-sm
                transition duration-200">
                <span
                    class="menu-category-dot size-1.5 rounded-full
                                bg-primary/30"
                    aria-hidden="true">
                </span>

                {{ $category->name }}
            </a>
            @endforeach
        </nav>
    </div>
    @endif

    <div
        wire:offline
        class="public-container pt-6">
        <div
            class="rounded-card border border-coral/20 bg-surface px-5 py-4
                text-sm text-ink shadow-card"
            role="status">
            Connection interrupted. The loaded menu remains available, but
            product customization requires a connection.
        </div>
    </div>

    <div
        class="public-container grid gap-10
            lg:grid-cols-[13rem_minmax(0,1fr)] lg:items-start lg:gap-12
            xl:grid-cols-[14rem_minmax(0,1fr)]">
        @if ($categories->isNotEmpty())
        <aside
            class="hidden lg:block"
            data-menu-sidebar>
            <div
                class="menu-glass-panel sticky top-28 max-h-[calc(100svh-8rem)]
                        overflow-y-auto rounded-panel p-4">
                <p
                    class="px-3 pb-3 text-[0.65rem] font-semibold uppercase
                            tracking-[0.2em] text-muted">
                    Menu categories
                </p>

                <nav
                    class="space-y-1.5"
                    aria-label="Menu categories">
                    @foreach ($categories as $category)
                    <a
                        href="#category-{{ $category->slug }}"
                        data-menu-category-link
                        data-menu-navigation-position="desktop"
                        @if ($loop->first) aria-current="true" @endif
                        class="menu-category-link flex min-h-12
                        items-center gap-3 rounded-xl border
                        border-transparent px-3.5 py-2.5 text-sm
                        font-medium text-ink transition
                        duration-200">
                        <span
                            class="menu-category-index flex size-8 shrink-0 items-center
        justify-center rounded-full bg-primary/8 text-xs
        font-semibold text-primary">
                            {{ str_pad(
                                        (string) $loop->iteration,
                                        2,
                                        '0',
                                        STR_PAD_LEFT,
                                    ) }}
                        </span>

                        <span
                            class="menu-category-label min-w-0 flex-1
        whitespace-normal break-words leading-5">
                            {{ $category->name }}
                        </span>

                        <span
                            class="menu-category-dot size-1.5
                                        shrink-0 rounded-full bg-primary/20"
                            aria-hidden="true">
                        </span>
                    </a>
                    @endforeach
                </nav>

                <div
                    class="mx-3 mt-8 border-t border-primary/10 pt-7
                            text-center">
                    <svg
                        class="mx-auto size-6 text-primary"
                        viewBox="0 0 32 32"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            d="M16 28V12M16 12c-1-5-6-7-11-5 4 1 7 3 9 6M16 12c2-5 7-7 12-4-5 0-8 2-11 6M16 12c-4-3-8-3-12 0 5-1 8 1 11 4M17 14c4-3 8-2 11 1-5-1-8 0-11 3" />
                    </svg>

                    <p class="mt-3 text-xs leading-5 text-muted">
                        Island flavors.<br>
                        Coastal comfort.
                    </p>
                </div>
            </div>
        </aside>
        @endif

        <div class="min-w-0">
            @forelse ($categories as $category)
            @php
            $totalItemCount = (int) $category->getAttribute(
            'visible_menu_items_count',
            );

            $carouselId = 'menu-carousel-'.$category->id;

            $initialVisibleCount = min(
            4,
            $totalItemCount,
            );

            $carouselStatus = $totalItemCount > 0
            ? sprintf(
            '1–%d of %d',
            $initialVisibleCount,
            $totalItemCount,
            )
            : '0 items';
            @endphp

            <section
                id="category-{{ $category->slug }}"
                wire:key="menu-category-{{ $category->id }}"
                data-menu-section
                data-menu-category="{{ $category->slug }}"
                class="menu-section">
                <div class="menu-section-content w-full">
                    <header
                        class="mb-8"
                        data-menu-section-heading>
                        <div
                            class="flex flex-col gap-6
                                    xl:flex-row xl:items-end
                                    xl:justify-between">
                            <div class="max-w-3xl">
                                <p
                                    class="text-[0.64rem] font-semibold
                                            uppercase tracking-[0.21em]
                                            text-primary">
                                    {{ str_pad(
                                            (string) $loop->iteration,
                                            2,
                                            '0',
                                            STR_PAD_LEFT,
                                        ) }}
                                    · Menu selection
                                </p>

                                <h2
                                    class="mt-2 font-display text-4xl
                                            leading-tight text-primary-deep
                                            sm:text-5xl">
                                    {{ $category->name }}
                                </h2>

                                @if ($category->description)
                                <p
                                    class="mt-4 max-w-2xl text-sm
                                                leading-7 text-muted">
                                    {{ $category->description }}
                                </p>
                                @endif
                            </div>

                            <div
                                class="flex shrink-0 items-center gap-3"
                                data-menu-carousel-controls>
                                <p
                                    class="mr-2 text-xs font-medium tabular-nums text-muted"
                                    data-menu-carousel-status
                                    aria-live="polite">
                                    {{ $carouselStatus }}
                                </p>

                                <button
                                    type="button"
                                    data-menu-carousel-previous
                                    aria-label="Show previous {{ $category->name }} items"
                                    aria-controls="{{ $carouselId }}"
                                    disabled
                                    class="inline-flex size-11 items-center
                                            justify-center rounded-full border
                                            border-primary/15 bg-surface
                                            text-primary shadow-card
                                            transition hover:border-primary/30
                                            hover:bg-surface-soft
                                            disabled:cursor-not-allowed
                                            disabled:opacity-35">
                                    <svg
                                        class="size-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m15 18-6-6 6-6" />
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    data-menu-carousel-next
                                    aria-label="Show next {{ $category->name }} items"
                                    aria-controls="{{ $carouselId }}"
                                    @disabled($totalItemCount <=4)
                                    class="inline-flex size-11 items-center
                                            justify-center rounded-full border
                                            border-primary/15 bg-primary
                                            text-canvas shadow-card transition
                                            hover:-translate-y-0.5
                                            hover:bg-primary-deep
                                            hover:shadow-panel
                                            disabled:cursor-not-allowed
                                            disabled:opacity-35
                                            motion-reduce:transform-none">
                                    <svg
                                        class="size-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div
                            class="mt-6 h-px w-full origin-left bg-line"
                            data-menu-section-rule
                            aria-hidden="true">
                        </div>
                    </header>

                    @if ($category->menuItems->isNotEmpty())
                    <div
                        data-menu-carousel
                        class="menu-carousel">
                        <ul
                            id="{{ $carouselId }}"
                            data-menu-carousel-track
                            tabindex="0"
                            role="list"
                            aria-label="{{ $category->name }} menu items"
                            class="menu-carousel-track">
                            @foreach ($category->menuItems as $item)
                            <li
                                wire:key="menu-item-{{ $item->id }}"
                                data-menu-carousel-item
                                class="menu-carousel-item">
                                <x-public.menu-card
                                    :item="$item"
                                    :eager="$loop->parent->first
                                                    && $loop->iteration <= 4"
                                    :priority="$loop->parent->first
                                                    && $loop->first" />
                            </li>
                            @endforeach
                        </ul>

                        <div
                            class="mt-2 h-0.5 overflow-hidden
                                        rounded-full bg-line"
                            aria-hidden="true">
                            <span
                                class="block h-full origin-left
                                            bg-coral"
                                data-menu-carousel-progress>
                            </span>
                        </div>
                    </div>
                    @else
                    <x-public.alert type="warning">
                        This menu selection is currently being prepared.
                    </x-public.alert>
                    @endif
                </div>
            </section>
            @empty
            <div class="py-24">
                <x-public.alert type="warning">
                    Our latest menu is currently being prepared.
                </x-public.alert>
            </div>
            @endforelse

            @if ($categories->isNotEmpty())
            <section
                class="flex min-h-[55svh] flex-col items-center
                        justify-center border-t border-primary/10 py-20
                        text-center"
                data-menu-closing>
                <svg
                    class="size-8 text-primary"
                    viewBox="0 0 32 32"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        d="M16 28V12M16 12c-1-5-6-7-11-5 4 1 7 3 9 6M16 12c2-5 7-7 12-4-5 0-8 2-11 6M16 12c-4-3-8-3-12 0 5-1 8 1 11 4M17 14c4-3 8-2 11 1-5-1-8 0-11 3" />
                </svg>

                <p
                    class="mt-4 font-display text-3xl
                            text-primary-deep">
                    That’s all for now.
                </p>

                <p class="mt-3 max-w-md text-sm leading-7 text-muted">
                    More seasonal specials and Caribbean favorites are
                    always on the horizon.
                </p>

                <a
                    href="{{ route('cart.index') }}"
                    class="public-button-primary mt-8">
                    Review Your Cart
                </a>
            </section>
            @endif
        </div>
    </div>
</div>