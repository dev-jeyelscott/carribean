<x-layouts.public
    :title="$page?->meta_title ?: 'Gallery | Coast & Cay'"
    :description="$page?->meta_description
        ?: 'Explore the food, atmosphere, and coastal hospitality of Coast & Cay.'"
    :image="$coverImage?->image_url"
    :header-overlay="false"
    :header-transparent="false"
>
    @php
        /*
         * Read optional CMS-managed Gallery copy while retaining useful
         * restaurant-specific defaults when structured content is absent.
         */
        $sections = is_array($page?->sections)
            ? $page->sections
            : [];

        $eyebrow = data_get(
            $sections,
            'gallery.eyebrow',
            'Coastal moments',
        );

        $heading = data_get(
            $sections,
            'gallery.heading',
            'Gallery',
        );

        $description = data_get(
            $sections,
            'gallery.description',
            filled($page?->excerpt)
                ? $page->excerpt
                : 'A visual journal of Caribbean flavor, coastal ease, and the warm hospitality shared around every table.',
        );
    @endphp

    <div
        data-gallery-page
        data-gallery-motion-state="idle"
        class="gallery-page"
    >
        {{-- The Gallery page intentionally contains one semantic section. --}}
        <section
            id="gallery-collection"
            data-gallery-section
            class="gallery-collage"
            aria-labelledby="gallery-heading"
        >
            <div class="gallery-collage__glow" aria-hidden="true"></div>

            <div class="public-container gallery-collage__inner">
                <header class="gallery-collage__header">
                    <div class="gallery-collage__heading-group">
                        <p
                            data-gallery-heading-reveal
                            class="gallery-collage__eyebrow"
                        >
                            {{ $eyebrow }}
                        </p>

                        <h1
                            id="gallery-heading"
                            data-gallery-heading-reveal
                            class="gallery-collage__title"
                        >
                            {{ $heading }}
                        </h1>

                        <span
                            data-gallery-heading-reveal
                            class="gallery-collage__mark"
                            aria-hidden="true"
                        >
                            ✦
                        </span>
                    </div>

                    <div class="gallery-collage__introduction">
                        <p
                            id="gallery-description"
                            data-gallery-heading-reveal
                            class="gallery-collage__description"
                        >
                            {{ $description }}
                        </p>

                        <p
                            data-gallery-heading-reveal
                            class="gallery-collage__count"
                        >
                            <span>{{ $totalImageCount }}</span>
                            {{ str('moment')->plural($totalImageCount) }}
                        </p>
                    </div>
                </header>

                @if ($galleryImages->isNotEmpty())
                    <div
                        id="gallery-grid"
                        data-gallery-grid
                        class="gallery-collage__grid"
                        aria-describedby="gallery-description"
                    >
                        @include(
                            'partials.public.gallery-items',
                            [
                                'galleryImages' => $galleryImages,
                            ]
                        )
                    </div>

                    @if ($galleryImages->hasMorePages())
                        <div
                            data-gallery-load-more-shell
                            class="gallery-load-more"
                        >
                            <a
                                data-gallery-load-more
                                href="{{ $galleryImages->nextPageUrl() }}"
                                class="gallery-load-more__button"
                            >
                                <span data-gallery-load-more-label>
                                    Load more moments
                                </span>

                                <span
                                    class="gallery-load-more__icon"
                                    aria-hidden="true"
                                >
                                    ✦
                                </span>
                            </a>

                            <p
                                data-gallery-load-more-status
                                class="gallery-load-more__status"
                                aria-live="polite"
                            ></p>
                        </div>
                    @endif
                @else
                    <div class="gallery-collage__empty">
                        <p class="gallery-collage__empty-eyebrow">
                            New moments are coming
                        </p>

                        <h2 class="gallery-collage__empty-title">
                            The next chapter is being prepared.
                        </h2>

                        <p class="gallery-collage__empty-copy">
                            Our food, dining room, and coastal gatherings will
                            appear here soon.
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{--
            One native dialog provides fullscreen viewing for every current and
            subsequently loaded Gallery card.
        --}}
        <dialog
            data-gallery-dialog
            data-gallery-image-state="idle"
            class="gallery-lightbox"
            aria-labelledby="gallery-dialog-title"
            aria-describedby="gallery-dialog-category"
        >
            <div
                data-gallery-dialog-panel
                class="gallery-lightbox__panel"
            >
                <div class="gallery-lightbox__toolbar">
                    <p
                        data-gallery-dialog-counter
                        class="gallery-lightbox__counter"
                    >
                        01 / 01
                    </p>

                    <button
                        type="button"
                        data-gallery-close
                        class="gallery-lightbox__close"
                        aria-label="Close fullscreen image"
                        autofocus
                    >
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <button
                    type="button"
                    data-gallery-previous
                    class="gallery-lightbox__navigation
                        gallery-lightbox__navigation--previous"
                    aria-label="View previous image"
                >
                    <span aria-hidden="true">←</span>
                </button>

                <figure class="gallery-lightbox__media">
                    <div
                        data-gallery-dialog-stage
                        class="gallery-lightbox__stage"
                    >
                        <span
                            class="gallery-lightbox__loader"
                            aria-hidden="true"
                        ></span>

                        <img
                            data-gallery-dialog-image
                            class="gallery-lightbox__image"
                            width="1800"
                            height="1350"
                            alt=""
                        >
                    </div>

                    <figcaption class="gallery-lightbox__caption">
                        <p
                            id="gallery-dialog-category"
                            data-gallery-dialog-category
                            class="gallery-lightbox__category"
                        >
                            Coast & Cay
                        </p>

                        <h2
                            id="gallery-dialog-title"
                            data-gallery-dialog-title
                            class="gallery-lightbox__title"
                        >
                            Coast & Cay moment
                        </h2>
                    </figcaption>
                </figure>

                <button
                    type="button"
                    data-gallery-next
                    class="gallery-lightbox__navigation
                        gallery-lightbox__navigation--next"
                    aria-label="View next image"
                >
                    <span aria-hidden="true">→</span>
                </button>

                <p
                    data-gallery-dialog-status
                    class="sr-only"
                    aria-live="polite"
                ></p>
            </div>
        </dialog>
    </div>
</x-layouts.public>
