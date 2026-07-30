<x-layouts.public
    :title="$page?->meta_title ?: 'Gallery | Coast & Cay'"
    :description="$page?->meta_description ?: 'Explore Coast & Cay food, hospitality, and coastal moments.'"
    :image="$heroImage?->image_url"
    :header-overlay="true">
    @php
    /*
    * Read optional structured page copy while preserving useful defaults.
    */
    $sections = is_array($page?->sections)
    ? $page->sections
    : [];

    $heroTitle = data_get(
    $sections,
    'hero.title',
    'An Island, Framed.',
    );

    $heroAccent = data_get(
    $sections,
    'hero.accent',
    'Every plate tells a story.',
    );

    $heroDescription = data_get(
    $sections,
    'hero.description',
    filled($page?->excerpt)
    ? $page->excerpt
    : 'A visual journal of bold plates, easy evenings, and the people who bring Caribbean warmth to the California coast.',
    );

    $signatureDescription = filled($page?->content)
    ? str($page->content)->stripTags()->squish()
    : 'This is not a catalog of perfect moments. It is a moving postcard from the kitchen, the dining room, and the coast beyond our doors.';

    $chapterEntries = $categoryCounts
    ->take(3)
    ->map(
    fn (int $count, string $category): array => [
    'label' => str($category)->headline()->toString(),
    'count' => $count,
    ],
    )
    ->values();

    if ($chapterEntries->isEmpty()) {
    $chapterEntries = collect([
    ['label' => 'The Food', 'count' => 0],
    ['label' => 'The Room', 'count' => 0],
    ['label' => 'The People', 'count' => 0],
    ]);
    }

    $activeCollectionTitle = $selectedCategory !== null
    ? str($selectedCategory)->headline()->toString()
    : 'The complete contact sheet';

    /*
    * Define the Gallery section pager independently from gallery content
    * categories. Each target maps to one complete editorial page section.
    */
    $galleryNavigation = [
    [
    'id' => 'gallery-hero',
    'label' => 'Introduction',
    ],
    [
    'id' => 'gallery-signature',
    'label' => 'Visual story',
    ],
    [
    'id' => 'gallery-collection',
    'label' => 'Collection',
    ],
    [
    'id' => 'gallery-invitation',
    'label' => 'Your visit',
    ],
    ];
    @endphp

    <div
        data-gallery-page
        data-gallery-motion-state="loading"
        class="gallery-page">
        <x-public.section-pager
            :items="$galleryNavigation"
            current="gallery-hero"
            label="Gallery page sections"
            context="gallery"
            enhancer="shared" />
        {{-- Cinematic full-screen gallery introduction. --}}
        <section
            id="gallery-hero"
            data-gallery-panel
            data-gallery-hero
            class="gallery-hero"
            aria-labelledby="gallery-hero-heading">
            @if ($heroImage?->image_url)
            <div
                data-gallery-hero-image
                class="gallery-hero__background">
                <x-public.responsive-image
                    :image="$heroImage"
                    :alt="$heroImage->alt_text ?: $heroImage->title ?: 'Coast and Cay restaurant atmosphere'"
                    variant="hero"
                    sizes="100vw"
                    width="2000"
                    height="1400"
                    loading="eager"
                    fetchpriority="high"
                    img-class="size-full object-cover" />
            </div>
            @else
            <div class="gallery-hero__fallback" aria-hidden="true"></div>
            @endif

            <div class="gallery-hero__grain" aria-hidden="true"></div>

            <div class="public-container gallery-hero__grid">
                <div data-gallery-hero-copy class="gallery-hero__copy">
                    <p data-gallery-reveal class="gallery-hero__kicker">
                        The Coast & Cay visual journal
                    </p>

                    <h1
                        id="gallery-hero-heading"
                        data-gallery-reveal
                        class="gallery-hero__title">
                        {{ $heroTitle }}
                        <span class="gallery-hero__accent">
                            {{ $heroAccent }}
                        </span>
                    </h1>

                    <p data-gallery-reveal class="gallery-hero__description">
                        {{ $heroDescription }}
                    </p>

                    <div data-gallery-reveal class="gallery-hero__actions">
                        <a
                            href="#gallery-collection"
                            class="public-button-primary">
                            Enter the Collection
                        </a>

                        <a
                            href="{{ route('menu') }}"
                            class="public-button-secondary text-white">
                            Explore the Menu
                        </a>
                    </div>
                </div>

                <div
                    data-gallery-stack
                    class="gallery-hero__stack"
                    aria-label="Featured Coast and Cay moments">
                    @for ($stackIndex = 0; $stackIndex < 3; $stackIndex++)
                        @php
                        $stackImage=$highlightImages->get($stackIndex);
                        $stackPosition = match ($stackIndex) {
                        0 => 'gallery-stack-card--one',
                        1 => 'gallery-stack-card--two',
                        default => 'gallery-stack-card--three',
                        };
                        @endphp

                        <figure
                            data-gallery-stack-card
                            class="gallery-stack-card {{ $stackPosition }}">
                            @if ($stackImage?->image_url)
                            <div class="gallery-stack-card__media">
                                <x-public.responsive-image
                                    :image="$stackImage"
                                    :alt="$stackImage->alt_text ?: $stackImage->title ?: 'Featured Coast and Cay gallery moment'"
                                    variant="large"
                                    sizes="(min-width: 1024px) 24vw, 42vw"
                                    width="900"
                                    height="1125"
                                    img-class="size-full object-cover" />
                            </div>

                            <figcaption class="gallery-stack-card__caption">
                                {{ $stackImage->title ?: 'Coast & Cay moment' }}
                            </figcaption>
                            @else
                            <div class="gallery-stack-card--placeholder">
                                <span>More island moments soon.</span>
                            </div>
                            @endif
                        </figure>
                        @endfor
                </div>
            </div>

            <a
                data-gallery-reveal
                href="#gallery-signature"
                class="gallery-hero__scroll">
                Scroll through the story
            </a>
        </section>

        {{-- Editorial bridge between the hero and the image collection. --}}
        <section
            id="gallery-signature"
            data-gallery-panel
            data-gallery-section
            class="gallery-signature"
            aria-labelledby="gallery-signature-heading">
            <div class="public-container gallery-signature__grid">
                <div>
                    <p data-gallery-reveal class="gallery-signature__kicker">
                        One table, many stories
                    </p>

                    <h2
                        id="gallery-signature-heading"
                        data-gallery-reveal
                        class="gallery-signature__title">
                        A visual rhythm of flavor, place, and welcome.
                    </h2>
                </div>

                <div>
                    <p data-gallery-reveal class="gallery-signature__copy">
                        {{ $signatureDescription }}
                    </p>

                    <div class="gallery-chapters" aria-label="Gallery chapters">
                        @foreach ($chapterEntries as $chapter)
                        <article data-gallery-reveal class="gallery-chapter">
                            <span class="gallery-chapter__number" aria-hidden="true">
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <h3 class="gallery-chapter__label">
                                {{ $chapter['label'] }}
                            </h3>
                            <p class="gallery-chapter__count">
                                {{ $chapter['count'] }}
                                {{ str('frame')->plural($chapter['count']) }}
                            </p>
                        </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Server-rendered filter and asymmetrical editorial contact sheet. --}}
        <section
            id="gallery-collection"
            data-gallery-panel
            data-gallery-collection
            class="gallery-collection"
            aria-labelledby="gallery-collection-heading">
            <div class="public-container gallery-collection__inner">
                <header class="gallery-collection__header">
                    <div>
                        <p data-gallery-reveal class="gallery-collection__kicker">
                            Volume 01 · Coast & Cay
                        </p>

                        <h2
                            id="gallery-collection-heading"
                            data-gallery-reveal
                            class="gallery-collection__title">
                            {{ $activeCollectionTitle }}
                        </h2>
                    </div>

                    <p data-gallery-reveal class="gallery-collection__description">
                        {{ $totalImageCount }} curated moments. Choose a chapter,
                        then open any frame for the full view.
                    </p>
                </header>

                <div class="gallery-collection__layout">
                    <aside
                        data-gallery-film-index
                        class="gallery-film-index"
                        aria-label="Filter the gallery by category">
                        <div class="gallery-film-index__header">
                            <p class="gallery-film-index__title">Film index</p>
                            <span>{{ str_pad((string) $totalImageCount, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>

                        <nav class="gallery-filter-list">
                            <a
                                data-gallery-filter
                                data-gallery-category="all"
                                href="{{ route('gallery') }}#gallery-collection"
                                class="gallery-filter-link"
                                aria-current="{{ $selectedCategory === null ? 'true' : 'false' }}">
                                <span>All moments</span>
                                <span class="gallery-filter-link__count">
                                    {{ $totalImageCount }}
                                </span>
                            </a>

                            @foreach ($categoryCounts as $category => $count)
                            <a
                                data-gallery-filter
                                data-gallery-category="{{ $category }}"
                                href="{{ route('gallery', ['category' => $category]) }}#gallery-collection"
                                class="gallery-filter-link"
                                aria-current="{{ $selectedCategory === $category ? 'true' : 'false' }}">
                                <span>{{ str($category)->headline() }}</span>
                                <span class="gallery-filter-link__count">
                                    {{ $count }}
                                </span>
                            </a>
                            @endforeach
                        </nav>
                    </aside>

                    <div>
                        @if ($galleryImages->isNotEmpty())
                        <div class="gallery-contact-sheet">
                            @foreach ($galleryImages as $image)
                            @php
                            $patternClass = match ($loop->index % 7) {
                            0 => 'gallery-contact-card--wide',
                            2 => 'gallery-contact-card--portrait',
                            5 => 'gallery-contact-card--tall',
                            default => '',
                            };

                            $displayIndex = ($galleryImages->firstItem() ?? 1)
                            + $loop->index;
                            @endphp

                            <x-public.gallery-card
                                :image="$image"
                                :index="$displayIndex"
                                variant="contact-sheet"
                                :class="$patternClass" />
                            @endforeach
                        </div>

                        @if ($galleryImages->hasPages())
                        <nav
                            class="gallery-pagination"
                            aria-label="Gallery pagination">
                            @if ($galleryImages->previousPageUrl())
                            <a
                                href="{{ $galleryImages->previousPageUrl() }}"
                                class="gallery-pagination__control"
                                rel="prev">
                                Previous volume
                            </a>
                            @else
                            <span
                                class="gallery-pagination__control"
                                aria-disabled="true">
                                Previous volume
                            </span>
                            @endif

                            <span class="gallery-pagination__status">
                                Volume {{ str_pad((string) $galleryImages->currentPage(), 2, '0', STR_PAD_LEFT) }}
                            </span>

                            @if ($galleryImages->nextPageUrl())
                            <a
                                href="{{ $galleryImages->nextPageUrl() }}"
                                class="gallery-pagination__control"
                                rel="next">
                                Next volume
                            </a>
                            @else
                            <span
                                class="gallery-pagination__control"
                                aria-disabled="true">
                                Next volume
                            </span>
                            @endif
                        </nav>
                        @endif
                        @else
                        <x-public.alert type="warning">
                            No visible gallery moments match this collection yet.
                        </x-public.alert>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Full-width invitation that keeps the gallery connected to conversion. --}}
        <section
            id="gallery-invitation"
            data-gallery-panel
            data-gallery-cta
            class="gallery-cta"
            aria-labelledby="gallery-cta-heading">
            @if ($heroImage?->image_url)
            <div class="gallery-cta__background" aria-hidden="true">
                <x-public.responsive-image
                    :image="$heroImage"
                    alt=""
                    variant="hero"
                    sizes="100vw"
                    width="2000"
                    height="1100"
                    img-class="size-full object-cover" />
            </div>
            @endif

            <div class="gallery-cta__overlay" aria-hidden="true"></div>

            <div class="public-container gallery-cta__copy">
                <p data-gallery-reveal class="gallery-cta__kicker">
                    The next frame is yours
                </p>

                <h2
                    id="gallery-cta-heading"
                    data-gallery-reveal
                    class="gallery-cta__title">
                    Come for the food.<br>
                    Leave with a favorite moment.
                </h2>

                <p data-gallery-reveal class="gallery-cta__description">
                    Explore the menu before your visit, or contact the team for
                    restaurant and accessibility information.
                </p>

                <div data-gallery-reveal class="gallery-hero__actions">
                    <a href="{{ route('menu') }}" class="public-button-primary">
                        Explore the Menu
                    </a>
                    <a
                        href="{{ route('contact.create') }}"
                        class="public-button-secondary text-white">
                        Contact the Team
                    </a>
                </div>
            </div>
        </section>

        {{-- One shared native dialog keeps the DOM small and keyboard behavior native. --}}
        <dialog
            data-gallery-dialog
            data-gallery-image-state="idle"
            class="gallery-lightbox"
            aria-labelledby="gallery-dialog-title">
            <div class="gallery-lightbox__panel">
                <button
                    type="button"
                    data-gallery-close
                    class="gallery-lightbox__close"
                    aria-label="Close gallery image"
                    autofocus>
                    <span aria-hidden="true">×</span>
                </button>

                <button
                    type="button"
                    data-gallery-previous
                    class="gallery-lightbox__navigation gallery-lightbox__navigation--previous"
                    aria-label="View previous gallery image">
                    <span aria-hidden="true">←</span>
                </button>

                <figure class="gallery-lightbox__media">
                    <div class="gallery-lightbox__image-shell">
                        <img
                            data-gallery-dialog-image
                            class="gallery-lightbox__image"
                            width="1600"
                            height="1200"
                            alt="">
                    </div>

                    <figcaption class="gallery-lightbox__caption">
                        <p
                            data-gallery-dialog-category
                            class="gallery-lightbox__category">
                            Coast & Cay
                        </p>
                        <h2
                            id="gallery-dialog-title"
                            data-gallery-dialog-title
                            class="gallery-lightbox__title">
                            Coast & Cay moment
                        </h2>
                    </figcaption>
                </figure>

                <button
                    type="button"
                    data-gallery-next
                    class="gallery-lightbox__navigation gallery-lightbox__navigation--next"
                    aria-label="View next gallery image">
                    <span aria-hidden="true">→</span>
                </button>
            </div>
        </dialog>
    </div>
</x-layouts.public>