@php
    /*
     * Continue the collage pattern across paginated requests by starting from
     * the paginator's global first-item position rather than the local loop.
     */
    $firstDisplayIndex = $galleryImages->firstItem() ?? 1;
@endphp

@foreach ($galleryImages as $image)
    @php
        /*
         * Assign a deliberate editorial shape to each image.
         *
         * CSS controls the actual responsive dimensions. The sequence balances
         * large landscapes, portraits, squares, and occasional feature frames.
         */
        $displayIndex = $firstDisplayIndex + $loop->index;

        $layout = match (($displayIndex - 1) % 10) {
            0 => 'feature',
            1, 5, 8 => 'portrait',
            2, 4, 7 => 'square',
            default => 'landscape',
        };
    @endphp

    <x-public.gallery-card
        :image="$image"
        :index="$displayIndex"
        :layout="$layout"
        variant="collage"
    />
@endforeach
