@props([
    'category',
    'item' => null,
])

@php
    $description = $category->description
        ?: 'Explore generous Caribbean flavors prepared for sharing.';
@endphp

<a
    href="{{ route('menu') }}#category-{{ $category->slug }}"
    data-gsap-reveal
    class="group flex h-full flex-col items-center rounded-card border
        border-line bg-surface px-6 py-7 text-center shadow-card transition
        duration-300 ease-island hover:-translate-y-1
        hover:border-coral/35 hover:shadow-panel
        motion-reduce:transform-none motion-reduce:transition-none">
    <div
        class="relative size-32 overflow-hidden rounded-full bg-surface-soft
            ring-8 ring-canvas sm:size-36">
        @if ($item?->image_url)
            <x-public.responsive-image
                :image="$item"
                :alt="$item->image_alt_text ?: $item->name"
                variant="card"
                sizes="144px"
                width="288"
                height="288"
                img-class="h-full w-full object-cover transition duration-500
                    ease-island group-hover:scale-105
                    motion-reduce:transform-none
                    motion-reduce:transition-none" />
        @else
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_32%_28%,rgba(242,199,107,0.40),transparent_32%),linear-gradient(145deg,#206f7c,#0c342b)]">
            </div>

            <span
                class="absolute inset-0 flex items-center justify-center
                    font-display text-4xl text-white/80">
                {{ str($category->name)->substr(0, 1)->upper() }}
            </span>
        @endif
    </div>

    <h3
        class="mt-6 font-display text-2xl leading-tight text-ink transition
            duration-300 group-hover:text-coral-deep">
        {{ $category->name }}
    </h3>

    <p class="mt-3 flex-1 text-sm leading-6 text-muted">
        {{ str($description)->limit(95) }}
    </p>

    <span
        class="mt-5 inline-flex items-center gap-2 text-xs font-semibold
            text-primary">
        View Menu

        <span
            class="text-coral transition duration-300
                group-hover:translate-x-1 motion-reduce:transform-none"
            aria-hidden="true">
            &rarr;
        </span>
    </span>
</a>
