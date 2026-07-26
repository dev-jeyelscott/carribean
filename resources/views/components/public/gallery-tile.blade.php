@props([
    'image' => null,
    'sizes' => '100vw',
])

<figure
    {{ $attributes->class([
        'group relative isolate overflow-hidden rounded-card',
        'bg-surface-soft shadow-card',
    ]) }}
    data-gsap="image">
    @if ($image?->image_url)
        <x-public.responsive-image
            :image="$image"
            :alt="$image->alt_text ?: $image->title ?: 'Coast & Cay restaurant experience'"
            variant="large"
            :sizes="$sizes"
            width="960"
            height="960"
            img-class="h-full w-full object-cover transition duration-700
                ease-island group-hover:scale-105
                motion-reduce:transform-none
                motion-reduce:transition-none" />
    @else
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(242,199,107,0.28),transparent_32%),linear-gradient(145deg,#206f7c,#0c342b)]">
        </div>

        <p
            class="absolute inset-x-6 bottom-6 border-t border-white/25
                pt-4 text-xs font-semibold uppercase tracking-[0.2em]
                text-white/70">
            Restaurant photography coming soon
        </p>
    @endif

    <div
        class="pointer-events-none absolute inset-0 bg-gradient-to-t
            from-primary-deep/20 via-transparent to-transparent">
    </div>
</figure>
