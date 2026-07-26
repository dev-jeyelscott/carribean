<x-layouts.public
    :title="$menuItem->name"
    :description="$menuItem->description ?: 'View this menu item from Coast & Cay.'">
    @php
    $formattedPrice = $menuItem->formattedPrice();
    $imageAlt = $menuItem->image_alt_text ?: $menuItem->name;
    @endphp

    <main class="bg-brand-cream pb-24 pt-32 sm:pt-40 lg:pb-32">
        <div class="public-container">
            <nav aria-label="Breadcrumb" class="mb-10">
                <ol
                    class="flex flex-wrap items-center gap-2 text-xs
                        font-semibold uppercase tracking-[0.16em]
                        text-brand-muted">
                    <li>
                        <a
                            href="{{ route('menu') }}"
                            class="transition hover:text-brand-coral-dark">
                            Menu
                        </a>
                    </li>

                    <li aria-hidden="true">/</li>

                    <li>
                        <a
                            href="{{ route('menu') }}#category-{{ $menuItem->menuCategory->slug }}"
                            class="transition hover:text-brand-coral-dark">
                            {{ $menuItem->menuCategory->name }}
                        </a>
                    </li>

                    <li aria-hidden="true">/</li>

                    <li aria-current="page" class="text-brand-forest">
                        {{ $menuItem->name }}
                    </li>
                </ol>
            </nav>

            <div class="grid items-start gap-12 lg:grid-cols-2 lg:gap-20">
                <div
                    class="relative aspect-[4/5] overflow-hidden
                        rounded-island bg-brand-palm shadow-island">
                    @if ($menuItem->image_url)
                    <x-public.responsive-image
                        :image="$menuItem"
                        :alt="$imageAlt"
                        variant="hero"
                        sizes="(min-width: 1024px) 50vw, 100vw"
                        width="1000"
                        height="1250"
                        loading="eager"
                        fetchpriority="high"
                        img-class="h-full w-full object-cover" />
                    @else
                    <div
                        class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(242,199,107,0.28),transparent_32%),linear-gradient(145deg,#206f7c,#0c342b)]"></div>

                    <p
                        class="absolute inset-x-8 bottom-8 border-t
                                border-white/20 pt-5 text-xs uppercase
                                tracking-[0.22em] text-white/65">
                        Image coming soon
                    </p>
                    @endif
                </div>

                <div>
                    <p class="public-eyebrow">
                        {{ $menuItem->menuCategory->name }}
                    </p>

                    <h1
                        class="mt-5 font-display text-5xl leading-tight
                            text-brand-forest sm:text-6xl">
                        {{ $menuItem->name }}
                    </h1>

                    @if ($formattedPrice)
                    <p class="mt-6 text-2xl font-semibold text-brand-palm">
                        {{ $formattedPrice }}
                    </p>
                    @endif

                    @if ($menuItem->description)
                    <p
                        class="mt-7 max-w-2xl text-base leading-8
                                text-brand-muted sm:text-lg">
                        {{ $menuItem->description }}
                    </p>
                    @endif

                    @if ($menuItem->dietary_labels)
                    <div class="mt-7 flex flex-wrap gap-2">
                        @foreach ($menuItem->dietary_labels as $label)
                        <span
                            class="rounded-full bg-brand-sand-soft
                                        px-4 py-2 text-xs font-semibold
                                        uppercase tracking-[0.12em]
                                        text-brand-forest">
                            {{ $label }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    @unless ($menuItem->is_available)
                    <x-public.alert type="warning" class="mt-8">
                        This item is currently unavailable.
                    </x-public.alert>
                    @endunless

                    @if ($menuItem->optionGroups->isNotEmpty())
                    <section class="mt-12" aria-labelledby="available-options">
                        <h2
                            id="available-options"
                            class="font-display text-3xl text-brand-forest">
                            Available options
                        </h2>

                        <div class="mt-7 space-y-6">
                            @foreach ($menuItem->optionGroups as $group)
                            <article
                                class="rounded-island border
                                            border-brand-palm/10 bg-white p-6
                                            shadow-island">
                                <div
                                    class="flex flex-wrap items-start
                                                justify-between gap-3">
                                    <h3
                                        class="font-display text-2xl
                                                    text-brand-forest">
                                        {{ $group->name }}
                                    </h3>

                                    <span
                                        class="rounded-full
                                                    bg-brand-cream px-3 py-1
                                                    text-xs font-semibold
                                                    text-brand-palm">
                                        @if ($group->is_required)
                                        Required
                                        @else
                                        Optional
                                        @endif
                                    </span>
                                </div>

                                <p class="mt-2 text-sm text-brand-muted">
                                    @if (
                                    $group->minimum_selections
                                    === $group->maximum_selections
                                    )
                                    Select
                                    {{ $group->minimum_selections }}.
                                    @else
                                    Select between
                                    {{ $group->minimum_selections }}
                                    and
                                    {{ $group->maximum_selections }}.
                                    @endif
                                </p>

                                <ul
                                    class="mt-5 divide-y
                                                divide-brand-palm/10">
                                    @foreach ($group->options as $option)
                                    <li
                                        class="flex items-center
                                                        justify-between gap-5 py-4">
                                        <span
                                            class="font-medium
                                                            text-brand-forest">
                                            {{ $option->name }}
                                        </span>

                                        @if (
                                        $option->formattedAdditionalPrice()
                                        )
                                        <span
                                            class="text-sm
                                                                font-semibold
                                                                text-brand-palm">
                                            +
                                            {{ $option->formattedAdditionalPrice() }}
                                        </span>
                                        @else
                                        <span
                                            class="text-sm
                                                                text-brand-muted">
                                            Included
                                        </span>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </article>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    @if ($menuItem->allergen_information)
                    <section
                        class="mt-10 border-t border-brand-palm/10 pt-8"
                        aria-labelledby="allergen-information">
                        <h2
                            id="allergen-information"
                            class="text-sm font-semibold uppercase
                                    tracking-[0.18em] text-brand-forest">
                            Allergen information
                        </h2>

                        <p class="mt-3 text-sm leading-7 text-brand-muted">
                            {{ $menuItem->allergen_information }}
                        </p>
                    </section>
                    @endif

                    <div class="mt-10">
                        @if (
                        $menuItem->is_available
                        && $menuItem->is_purchasable
                        )
                        <x-public.alert type="success">
                            Online ordering for this item will be available
                            soon. You can review its options now.
                        </x-public.alert>
                        @elseif ($menuItem->is_available)
                        <x-public.alert type="warning">
                            This item is currently displayed for the
                            restaurant menu but is not available for online
                            ordering.
                        </x-public.alert>
                        @endif
                    </div>

                    <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                        <a
                            href="{{ route('menu') }}"
                            class="public-button-primary">
                            Back to Menu
                        </a>

                        <a
                            href="{{ route('reservation-request.create') }}"
                            class="public-button-secondary text-brand-palm">
                            Reserve a Table
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-layouts.public>