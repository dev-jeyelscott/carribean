<div
    id="menu-catalog"
    data-menu-catalog
    class="relative">
    @if ($categories->isNotEmpty())
        <div
            class="sticky top-[5.5rem] z-40 border-y border-primary/10
                bg-canvas/82 backdrop-blur-xl lg:hidden"
            data-menu-mobile-categories>
            <nav
                class="mx-auto flex max-w-full gap-2 overflow-x-auto
                    px-5 py-3 [scrollbar-width:none]
                    [&::-webkit-scrollbar]:hidden"
                aria-label="Menu categories"
                data-menu-category-scroller>
                @foreach ($categories as $category)
                    <a
                        href="#category-{{ $category->slug }}"
                        data-menu-category-link
                        data-menu-navigation-position="mobile"
                        @if ($loop->first) aria-current="true" @endif
                        class="menu-category-link inline-flex min-h-11 shrink-0
                            items-center gap-2 rounded-full border
                            border-primary/10 bg-surface/75 px-4
                            text-xs font-semibold text-primary shadow-sm
                            transition duration-200">
                        <span
                            class="menu-category-dot size-1.5 rounded-full
                                bg-primary/30"
                            aria-hidden="true"></span>

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
            Connection interrupted. Already loaded menu items remain available.
            Retry loading when the connection returns.
        </div>
    </div>

    <div
        class="public-container grid gap-10 py-12
            lg:grid-cols-[13rem_minmax(0,1fr)] lg:items-start
            lg:gap-12 lg:py-16 xl:grid-cols-[14rem_minmax(0,1fr)]">
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
                                    border-transparent px-3.5 py-2.5
                                    text-sm font-medium text-ink
                                    transition duration-200">
                                <span
                                    class="flex size-8 shrink-0 items-center
                                        justify-center rounded-full
                                        bg-primary/8 text-xs font-semibold
                                        text-primary">
                                    {{ str_pad(
                                        (string) $loop->iteration,
                                        2,
                                        '0',
                                        STR_PAD_LEFT,
                                    ) }}
                                </span>

                                <span class="min-w-0 flex-1 truncate">
                                    {{ $category->name }}
                                </span>

                                <span
                                    class="menu-category-dot size-1.5
                                        shrink-0 rounded-full bg-primary/20"
                                    aria-hidden="true"></span>
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

                    $loadedItemCount = $category->menuItems->count();
                    $hasMoreItems = $loadedItemCount < $totalItemCount;
                @endphp

                <section
                    id="category-{{ $category->slug }}"
                    wire:key="menu-category-{{ $category->id }}"
                    data-menu-section
                    data-menu-category="{{ $category->slug }}"
                    class="scroll-mt-[10.5rem] pb-16 last:pb-4
                        lg:scroll-mt-28 lg:pb-20">
                    <header
                        class="mb-6 flex items-end gap-4
                            border-b border-primary/12 pb-3">
                        <div class="min-w-0">
                            <p
                                class="text-[0.64rem] font-semibold uppercase
                                    tracking-[0.21em] text-primary">
                                {{ str_pad(
                                    (string) $loop->iteration,
                                    2,
                                    '0',
                                    STR_PAD_LEFT,
                                ) }}
                                · Menu selection
                            </p>

                            <h2
                                class="mt-1 font-display text-3xl leading-tight
                                    text-primary-deep sm:text-4xl">
                                {{ $category->name }}
                            </h2>
                        </div>

                        <div
                            class="mb-2 h-px min-w-8 flex-1 bg-line"
                            aria-hidden="true"></div>

                        <p
                            class="mb-1 shrink-0 text-xs font-medium
                                tabular-nums text-muted">
                            {{ $totalItemCount }}
                            {{ str('item')->plural($totalItemCount) }}
                        </p>
                    </header>

                    @if ($category->description)
                        <p
                            class="-mt-2 mb-6 max-w-2xl text-sm leading-7
                                text-muted">
                            {{ $category->description }}
                        </p>
                    @endif

                    <div
                        id="category-items-{{ $category->id }}"
                        class="grid grid-cols-1 gap-4 min-[560px]:grid-cols-2
                            xl:grid-cols-3 2xl:grid-cols-4"
                        data-menu-card-grid>
                        @forelse ($category->menuItems as $item)
                            @php
                                $quantity = $quantities[$item->id] ?? 1;
                            @endphp

                            <x-public.menu-order-card
                                wire:key="menu-item-{{ $item->id }}"
                                :item="$item"
                                :quantity="$quantity" />
                        @empty
                            <x-public.alert
                                type="warning"
                                class="min-[560px]:col-span-2
                                    xl:col-span-3 2xl:col-span-4">
                                This selection is currently being prepared.
                            </x-public.alert>
                        @endforelse
                    </div>

                    @if ($hasMoreItems)
                        <div class="mt-8 flex justify-center">
                            <button
                                type="button"
                                wire:key="load-more-{{ $category->id }}-{{ $loadedItemCount }}"
                                wire:click.preserve-scroll="loadMore({{ $category->id }})"
                                data-menu-load-more
                                aria-controls="category-items-{{ $category->id }}"
                                class="inline-flex min-h-11 items-center
                                    justify-center rounded-full border
                                    border-primary/15 bg-surface px-5
                                    text-xs font-semibold uppercase
                                    tracking-[0.12em] text-primary shadow-card
                                    transition hover:border-primary/30
                                    hover:bg-surface-soft
                                    data-loading:pointer-events-none
                                    data-loading:opacity-65">
                                <span
                                    class="inline-flex items-center gap-2
                                        data-loading:hidden">
                                    Load more island flavors

                                    <svg
                                        class="size-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m7 10 5 5 5-5" />
                                    </svg>
                                </span>

                                <span
                                    class="hidden items-center gap-2
                                        data-loading:inline-flex">
                                    <svg
                                        class="size-4 animate-spin
                                            motion-reduce:animate-none"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        aria-hidden="true">
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="9"
                                            stroke="currentColor"
                                            stroke-width="2" />

                                        <path
                                            d="M21 12a9 9 0 0 0-9-9"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round" />
                                    </svg>

                                    Loading menu items
                                </span>
                            </button>
                        </div>
                    @endif
                </section>
            @empty
                <x-public.alert type="warning">
                    Our latest menu is currently being prepared.
                </x-public.alert>
            @endforelse

            @if ($categories->isNotEmpty())
                <div
                    class="flex flex-col items-center border-t border-primary/10
                        pb-8 pt-10 text-center">
                    <svg
                        class="size-7 text-primary"
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
                        class="mt-3 font-display text-xl
                            text-primary-deep">
                        That’s all for now.
                    </p>

                    <p class="mt-1 text-sm text-muted">
                        More seasonal specials are always on the horizon.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
