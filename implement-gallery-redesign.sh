#!/usr/bin/env bash
set -euo pipefail

# Run this script from the Carribean repository root in WSL.
git checkout develop
git pull --ff-only origin develop

mkdir -p resources/css resources/js tests/Feature/PublicSite tests/Browser

cat > app/Http/Controllers/PublicSite/GalleryController.php <<'EOF_APP_HTTP_CONTROLLERS_PUBLICSITE_GALLERYCONTROLLER_PHP'
<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    private const IMAGES_PER_PAGE = 12;

    /**
     * Display the public gallery with server-rendered category filtering.
     */
    public function index(Request $request): View
    {
        $page = Page::query()
            ->where('slug', 'gallery')
            ->where('is_published', true)
            ->first();

        $categoryCounts = GalleryImage::query()
            ->visible()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->selectRaw('category, COUNT(*) AS image_count')
            ->groupBy('category')
            ->orderBy('category')
            ->pluck('image_count', 'category')
            ->map(
                fn (mixed $count): int => (int) $count,
            );

        $requestedCategory = $request
            ->string('category')
            ->trim()
            ->toString();

        $selectedCategory = $requestedCategory !== ''
            && $categoryCounts->has($requestedCategory)
                ? $requestedCategory
                : null;

        $heroImage = GalleryImage::query()
            ->visible()
            ->ordered()
            ->first();

        $highlightImagesQuery = GalleryImage::query()
            ->visible()
            ->ordered();

        if ($heroImage !== null) {
            $highlightImagesQuery->where(
                'id',
                '!=',
                $heroImage->getKey(),
            );
        }

        $highlightImages = $highlightImagesQuery
            ->limit(3)
            ->get();

        $galleryImagesQuery = GalleryImage::query()
            ->visible()
            ->ordered();

        if ($selectedCategory !== null) {
            $galleryImagesQuery->where(
                'category',
                $selectedCategory,
            );
        }

        $galleryImages = $galleryImagesQuery
            ->simplePaginate(self::IMAGES_PER_PAGE)
            ->withQueryString()
            ->fragment('gallery-collection');

        return view('pages.gallery', [
            'page' => $page,
            'heroImage' => $heroImage,
            'highlightImages' => $highlightImages,
            'galleryImages' => $galleryImages,
            'categoryCounts' => $categoryCounts,
            'selectedCategory' => $selectedCategory,
            'totalImageCount' => GalleryImage::query()
                ->visible()
                ->count(),
        ]);
    }
}
EOF_APP_HTTP_CONTROLLERS_PUBLICSITE_GALLERYCONTROLLER_PHP

cat > resources/views/pages/gallery.blade.php <<'EOF_RESOURCES_VIEWS_PAGES_GALLERY_BLADE_PHP'
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
    @endphp

    <div
        data-gallery-page
        data-gallery-motion-state="loading"
        class="gallery-page">
        {{-- Cinematic full-screen gallery introduction. --}}
        <section
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
                            $stackImage = $highlightImages->get($stackIndex);
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
EOF_RESOURCES_VIEWS_PAGES_GALLERY_BLADE_PHP

cat > resources/views/components/public/gallery-card.blade.php <<'EOF_RESOURCES_VIEWS_COMPONENTS_PUBLIC_GALLERY-CARD_BLADE_PHP'
@props([
    'image',
    'variant' => 'default',
    'index' => null,
])

@php
    /*
     * Keep legacy variants reusable while adding the gallery contact-sheet card.
     */
    $isContactSheet = $variant === 'contact-sheet';
    $isEditorial = $variant === 'editorial';
    $displayTitle = $image->title ?: 'Coast & Cay moment';
    $displayCategory = filled($image->category)
        ? str($image->category)->headline()->toString()
        : 'Coast & Cay';
    $largeImageUrl = $image->responsiveImageUrl('large');
    $sourceSet = $image->responsiveImageSrcset();
@endphp

@if ($isContactSheet)
    <article
        data-gallery-item
        {{ $attributes->class(['gallery-contact-card']) }}>
        @if ($largeImageUrl)
            <a
                data-gallery-open
                data-gallery-src="{{ $largeImageUrl }}"
                data-gallery-srcset="{{ $sourceSet }}"
                data-gallery-alt="{{ $image->alt_text ?: $displayTitle }}"
                data-gallery-title="{{ $displayTitle }}"
                data-gallery-category="{{ $displayCategory }}"
                href="{{ $largeImageUrl }}"
                class="gallery-contact-card__button"
                aria-label="Open {{ $displayTitle }} in the gallery viewer">
                <x-public.responsive-image
                    :image="$image"
                    :alt="$image->alt_text ?: $displayTitle"
                    variant="large"
                    sizes="(min-width: 1280px) 38vw, (min-width: 768px) 48vw, 100vw"
                    width="1200"
                    height="900"
                    img-class="gallery-contact-card__image" />

                <span class="gallery-contact-card__veil" aria-hidden="true"></span>

                <span class="gallery-contact-card__meta" aria-hidden="true">
                    <span class="gallery-contact-card__number">
                        {{ str_pad((string) ($index ?? 1), 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <span class="gallery-contact-card__category">
                        {{ $displayCategory }}
                    </span>
                </span>

                <span class="gallery-contact-card__content">
                    <span class="gallery-contact-card__title">
                        {{ $displayTitle }}
                    </span>
                    <span class="gallery-contact-card__open" aria-hidden="true">
                        Open frame ↗
                    </span>
                </span>
            </a>
        @else
            <div class="gallery-contact-card__fallback">
                <span>{{ $displayTitle }}</span>
            </div>
        @endif
    </article>
@elseif ($isEditorial)
    <article data-gsap="tile" {{ $attributes->class([
        'group relative isolate min-h-72 overflow-hidden bg-brand-ink-soft',
    ]) }}>
        @if ($image->image_url)
            <x-public.responsive-image
                :image="$image"
                :alt="$image->alt_text ?: $image->title ?: 'Restaurant gallery image'"
                variant="large"
                sizes="(min-width: 1024px) 66vw, (min-width: 768px) 50vw, 100vw"
                width="1200"
                height="900"
                img-class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105" />
        @else
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_28%_22%,rgba(201,164,93,0.3),transparent_32%),linear-gradient(145deg,#4d4437,#171916)]"></div>
        @endif

        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-ink/95 via-brand-ink/10 to-transparent opacity-85 transition duration-500 group-hover:opacity-100"></div>
        <div class="absolute inset-0 -z-10 ring-1 ring-inset ring-white/10 transition duration-500 group-hover:ring-brand-gold/55"></div>

        @if ($image->title || $image->category)
            <div data-gsap-reveal class="absolute inset-x-0 bottom-0 p-6 sm:p-7">
                @if ($image->category)
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.26em] text-brand-gold">
                        {{ $image->category }}
                    </p>
                @endif

                @if ($image->title)
                    <h3 class="mt-2 max-w-xl font-display text-2xl leading-tight text-white sm:text-3xl">
                        {{ $image->title }}
                    </h3>
                @endif
            </div>
        @endif
    </article>
@else
    <article {{ $attributes->class([
        'group overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03]',
    ]) }}>
        @if ($image->image_url)
            <div class="overflow-hidden">
                <x-public.responsive-image
                    :image="$image"
                    :alt="$image->alt_text ?: $image->title ?: 'Restaurant gallery image'"
                    variant="card"
                    sizes="(min-width: 1024px) 30vw, (min-width: 768px) 45vw, 100vw"
                    width="960"
                    height="720"
                    img-class="h-64 w-full object-cover transition duration-500 group-hover:scale-105" />
            </div>
        @endif

        @if ($image->title || $image->category)
            <div class="p-4">
                @if ($image->title)
                    <h3 class="font-semibold text-white">{{ $image->title }}</h3>
                @endif

                @if ($image->category)
                    <p class="mt-1 text-xs uppercase tracking-widest text-amber-300">
                        {{ $image->category }}
                    </p>
                @endif
            </div>
        @endif
    </article>
@endif
EOF_RESOURCES_VIEWS_COMPONENTS_PUBLIC_GALLERY-CARD_BLADE_PHP

touch resources/css/gallery.css

cat > resources/css/gallery.css <<'EOF_RESOURCES_CSS_GALLERY_CSS'
/*
 * Gallery page editorial experience
 *
 * Content remains server-rendered and readable without JavaScript. GSAP and
 * the native dialog viewer progressively enhance the page.
 */

html.gallery-dialog-open,
html.gallery-dialog-open body {
    overflow: hidden;
}

.gallery-page {
    position: relative;
    isolation: isolate;
    background: var(--color-canvas);
}

/* Cinematic hero */
.gallery-hero {
    position: relative;
    display: flex;
    min-height: 100vh;
    min-height: 100svh;
    align-items: center;
    overflow: hidden;
    background: var(--color-primary-deep);
    color: var(--color-canvas);
}

.gallery-hero__background,
.gallery-hero__background::after,
.gallery-hero__fallback,
.gallery-hero__grain {
    position: absolute;
    inset: 0;
}

.gallery-hero__background::after {
    background:
        linear-gradient(
            90deg,
            rgb(3 19 16 / 94%) 0%,
            rgb(3 19 16 / 76%) 42%,
            rgb(3 19 16 / 24%) 73%,
            rgb(3 19 16 / 58%) 100%
        ),
        linear-gradient(
            0deg,
            rgb(3 19 16 / 88%) 0%,
            transparent 48%,
            rgb(3 19 16 / 32%) 100%
        );
    content: "";
}

.gallery-hero__background img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-hero__fallback {
    background:
        radial-gradient(
            circle at 72% 24%,
            rgb(242 199 107 / 28%),
            transparent 31%
        ),
        radial-gradient(
            circle at 24% 78%,
            rgb(230 110 80 / 22%),
            transparent 34%
        ),
        linear-gradient(145deg, #206f7c, #0c342b 62%, #031310);
}

.gallery-hero__grain {
    opacity: 0.15;
    pointer-events: none;
    background-image:
        radial-gradient(rgb(255 255 255 / 17%) 0.55px, transparent 0.55px),
        radial-gradient(rgb(242 199 107 / 13%) 0.55px, transparent 0.55px);
    background-position:
        0 0,
        9px 9px;
    background-size: 18px 18px;
}

.gallery-hero__grid {
    position: relative;
    z-index: 1;
    display: grid;
    width: 100%;
    min-height: 100svh;
    align-items: center;
    gap: clamp(3rem, 7vw, 7rem);
    padding-block: clamp(8.5rem, 14vw, 11rem) 5rem;
}

.gallery-hero__copy {
    max-width: 47rem;
}

.gallery-hero__kicker,
.gallery-signature__kicker,
.gallery-collection__kicker,
.gallery-cta__kicker {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.3em;
    text-transform: uppercase;
}

.gallery-hero__kicker,
.gallery-cta__kicker {
    color: var(--color-sun);
}

.gallery-hero__title {
    max-width: 10ch;
    margin-top: 1.35rem;
    font-family: var(--font-display);
    font-size: clamp(3.75rem, 9vw, 8.75rem);
    line-height: 0.88;
    letter-spacing: -0.045em;
    text-wrap: balance;
}

.gallery-hero__accent {
    display: block;
    margin-top: 0.55rem;
    color: var(--color-coral);
    font-size: clamp(2rem, 4.2vw, 4.4rem);
    font-style: italic;
    font-weight: 400;
    letter-spacing: -0.025em;
    line-height: 1.05;
}

.gallery-hero__description {
    max-width: 39rem;
    margin-top: 1.8rem;
    color: rgb(255 255 255 / 76%);
    font-size: clamp(1rem, 1.3vw, 1.15rem);
    line-height: 1.85;
}

.gallery-hero__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.9rem;
    margin-top: 2.25rem;
}

.gallery-hero__stack {
    position: relative;
    min-height: clamp(29rem, 58vw, 43rem);
}

.gallery-stack-card {
    position: absolute;
    width: min(72%, 23rem);
    overflow: hidden;
    border: 0.55rem solid rgb(255 249 240 / 96%);
    border-bottom-width: 3rem;
    border-radius: 0.35rem;
    background: var(--color-surface);
    box-shadow: var(--shadow-elevated);
    color: var(--color-ink);
    transform-origin: center;
}

.gallery-stack-card--one {
    top: 3%;
    right: 12%;
    z-index: 1;
    transform: rotate(5deg);
}

.gallery-stack-card--two {
    top: 28%;
    left: 2%;
    z-index: 3;
    width: min(66%, 20rem);
    transform: rotate(-7deg);
}

.gallery-stack-card--three {
    right: 2%;
    bottom: 2%;
    z-index: 2;
    width: min(62%, 19rem);
    transform: rotate(3deg);
}

.gallery-stack-card__media {
    aspect-ratio: 4 / 5;
    overflow: hidden;
    background: var(--color-primary);
}

.gallery-stack-card__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-stack-card__caption {
    position: absolute;
    right: 0.75rem;
    bottom: 0.8rem;
    left: 0.75rem;
    overflow: hidden;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-overflow: ellipsis;
    text-transform: uppercase;
    white-space: nowrap;
}

.gallery-stack-card--placeholder {
    display: grid;
    aspect-ratio: 4 / 5;
    place-items: center;
    background:
        radial-gradient(
            circle at 72% 18%,
            rgb(242 199 107 / 30%),
            transparent 30%
        ),
        linear-gradient(145deg, var(--color-ocean), var(--color-primary-deep));
    color: white;
}

.gallery-stack-card--placeholder span {
    max-width: 10ch;
    text-align: center;
    font-family: var(--font-display);
    font-size: 2rem;
    line-height: 1;
}

.gallery-hero__scroll {
    position: absolute;
    z-index: 2;
    bottom: 1.5rem;
    left: 50%;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    color: rgb(255 255 255 / 68%);
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    transform: translateX(-50%);
}

.gallery-hero__scroll::before {
    width: 2.75rem;
    height: 1px;
    background: var(--color-coral);
    content: "";
}

/* Editorial story bridge */
.gallery-signature {
    display: flex;
    min-height: 100vh;
    min-height: 100svh;
    align-items: center;
    overflow: hidden;
    padding-block: clamp(5.5rem, 10vw, 9rem);
    background: var(--color-canvas);
    color: var(--color-ink);
}

.gallery-signature__grid {
    display: grid;
    gap: clamp(3rem, 8vw, 8rem);
}

.gallery-signature__kicker,
.gallery-collection__kicker {
    color: var(--color-coral-deep);
}

.gallery-signature__title,
.gallery-collection__title,
.gallery-cta__title {
    font-family: var(--font-display);
    letter-spacing: -0.035em;
    text-wrap: balance;
}

.gallery-signature__title {
    max-width: 12ch;
    margin-top: 1rem;
    font-size: clamp(3rem, 6vw, 6.25rem);
    line-height: 0.98;
}

.gallery-signature__copy {
    max-width: 42rem;
    color: var(--color-muted);
    font-size: clamp(1.05rem, 1.6vw, 1.3rem);
    line-height: 1.85;
}

.gallery-chapters {
    display: grid;
    margin-top: 3rem;
    border-top: 1px solid var(--color-line);
}

.gallery-chapter {
    display: grid;
    grid-template-columns: 3.25rem minmax(0, 1fr) auto;
    align-items: center;
    gap: 1rem;
    padding-block: 1.25rem;
    border-bottom: 1px solid var(--color-line);
}

.gallery-chapter__number,
.gallery-chapter__count {
    color: var(--color-coral-deep);
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.gallery-chapter__label {
    font-family: var(--font-display);
    font-size: clamp(1.35rem, 2vw, 1.8rem);
}

.gallery-chapter__count {
    color: var(--color-muted);
}

/* Film-index collection */
.gallery-collection {
    position: relative;
    min-height: 100vh;
    min-height: 100svh;
    scroll-margin-top: 5rem;
    overflow: hidden;
    padding-block: clamp(5.5rem, 10vw, 9rem);
    background: var(--color-primary-deep);
    color: white;
}

.gallery-collection::before {
    position: absolute;
    inset: 0;
    opacity: 0.22;
    background:
        radial-gradient(circle at 88% 12%, rgb(32 111 124 / 42%), transparent 30%),
        radial-gradient(circle at 8% 82%, rgb(230 110 80 / 20%), transparent 30%);
    content: "";
    pointer-events: none;
}

.gallery-collection__inner {
    position: relative;
}

.gallery-collection__header {
    display: grid;
    align-items: end;
    gap: 2rem;
    padding-bottom: 3.5rem;
    border-bottom: 1px solid rgb(255 255 255 / 12%);
}

.gallery-collection__kicker {
    color: var(--color-sun);
}

.gallery-collection__title {
    max-width: 14ch;
    margin-top: 1rem;
    font-size: clamp(3rem, 6vw, 6.5rem);
    line-height: 0.95;
}

.gallery-collection__description {
    max-width: 32rem;
    color: rgb(255 255 255 / 65%);
    line-height: 1.8;
}

.gallery-collection__layout {
    display: grid;
    gap: clamp(2.5rem, 5vw, 5rem);
    padding-top: 3rem;
}

.gallery-film-index {
    align-self: start;
    border-top: 1px solid rgb(255 255 255 / 16%);
}

.gallery-film-index__header {
    display: flex;
    justify-content: space-between;
    padding-block: 1rem;
    color: var(--color-sun);
}

.gallery-film-index__title {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.24em;
    text-transform: uppercase;
}

.gallery-filter-list {
    display: grid;
    border-top: 1px solid rgb(255 255 255 / 10%);
}

.gallery-filter-link {
    display: grid;
    min-height: 3.75rem;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 1rem;
    border-bottom: 1px solid rgb(255 255 255 / 10%);
    color: rgb(255 255 255 / 66%);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.13em;
    text-transform: uppercase;
    transition:
        color 240ms var(--ease-island),
        padding 240ms var(--ease-island),
        background 240ms var(--ease-island);
}

.gallery-filter-link:hover {
    padding-inline: 0.75rem;
    color: white;
}

.gallery-filter-link[aria-current="true"] {
    padding-inline: 0.75rem;
    background: var(--color-coral);
    color: white;
}

.gallery-filter-link__count {
    display: grid;
    min-width: 1.8rem;
    min-height: 1.8rem;
    place-items: center;
    border: 1px solid currentColor;
    border-radius: var(--radius-pill);
    font-size: 0.62rem;
}

.gallery-contact-sheet {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    grid-auto-flow: dense;
    gap: 0.85rem;
}

.gallery-contact-card {
    position: relative;
    min-height: 19rem;
    grid-column: span 12;
    overflow: hidden;
    background: var(--color-primary);
}

.gallery-contact-card__button,
.gallery-contact-card__fallback {
    position: relative;
    display: block;
    width: 100%;
    height: 100%;
    min-height: inherit;
    overflow: hidden;
    color: white;
}

.gallery-contact-card__image {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 700ms var(--ease-island);
}

.gallery-contact-card__veil {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgb(3 19 16 / 92%),
        rgb(3 19 16 / 9%) 58%,
        rgb(3 19 16 / 20%)
    );
}

.gallery-contact-card__meta {
    position: absolute;
    top: 1rem;
    right: 1rem;
    left: 1rem;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}

.gallery-contact-card__number,
.gallery-contact-card__category {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}

.gallery-contact-card__number {
    color: var(--color-sun);
}

.gallery-contact-card__category {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.gallery-contact-card__content {
    position: absolute;
    right: 1.25rem;
    bottom: 1.25rem;
    left: 1.25rem;
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 1rem;
}

.gallery-contact-card__title {
    max-width: 17ch;
    font-family: var(--font-display);
    font-size: clamp(1.65rem, 3vw, 2.5rem);
    line-height: 1;
}

.gallery-contact-card__open {
    flex: none;
    font-size: 0.64rem;
    font-weight: 700;
    letter-spacing: 0.16em;
    opacity: 0;
    text-transform: uppercase;
    transform: translateY(0.5rem);
    transition:
        opacity 240ms var(--ease-island),
        transform 240ms var(--ease-island);
}

.gallery-contact-card__button:hover .gallery-contact-card__image,
.gallery-contact-card__button:focus-visible .gallery-contact-card__image {
    transform: scale(1.045);
}

.gallery-contact-card__button:hover .gallery-contact-card__open,
.gallery-contact-card__button:focus-visible .gallery-contact-card__open {
    opacity: 1;
    transform: translateY(0);
}

.gallery-contact-card__button:focus-visible {
    outline: 4px solid var(--color-sun);
    outline-offset: -4px;
}

.gallery-contact-card__fallback {
    display: grid;
    place-items: end start;
    padding: 1.5rem;
    background:
        radial-gradient(circle at 70% 20%, rgb(242 199 107 / 32%), transparent 30%),
        linear-gradient(145deg, var(--color-ocean), var(--color-primary));
}

.gallery-contact-card__fallback span {
    max-width: 12ch;
    font-family: var(--font-display);
    font-size: 2rem;
    line-height: 1;
}

.gallery-pagination {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 1rem;
    margin-top: 3rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgb(255 255 255 / 12%);
}

.gallery-pagination__control {
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    transition: color 200ms ease;
}

.gallery-pagination__control:last-child {
    text-align: right;
}

.gallery-pagination__control:hover {
    color: var(--color-sun);
}

.gallery-pagination__control[aria-disabled="true"] {
    color: rgb(255 255 255 / 28%);
}

.gallery-pagination__status {
    color: rgb(255 255 255 / 48%);
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

/* Closing invitation */
.gallery-cta {
    position: relative;
    display: flex;
    min-height: 100vh;
    min-height: 100svh;
    align-items: center;
    overflow: hidden;
    background: var(--color-primary-deep);
    color: white;
}

.gallery-cta__background,
.gallery-cta__overlay {
    position: absolute;
    inset: 0;
}

.gallery-cta__background img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-cta__overlay {
    background:
        linear-gradient(90deg, rgb(3 19 16 / 92%), rgb(3 19 16 / 55%) 58%, rgb(3 19 16 / 72%)),
        linear-gradient(0deg, rgb(3 19 16 / 60%), transparent 55%);
}

.gallery-cta__copy {
    position: relative;
    padding-block: 7rem;
}

.gallery-cta__title {
    max-width: 13ch;
    margin-top: 1rem;
    font-size: clamp(3.25rem, 7vw, 7rem);
    line-height: 0.94;
}

.gallery-cta__description {
    max-width: 38rem;
    margin-top: 1.5rem;
    color: rgb(255 255 255 / 70%);
    line-height: 1.8;
}

/* Native dialog image viewer */
.gallery-lightbox {
    width: min(94vw, 96rem);
    max-width: none;
    height: min(92dvh, 62rem);
    max-height: none;
    margin: auto;
    padding: 0;
    overflow: visible;
    border: 0;
    background: transparent;
    color: white;
    opacity: 0;
    transform: scale(0.975);
    transition:
        opacity 220ms var(--ease-island),
        transform 220ms var(--ease-island),
        overlay 220ms allow-discrete,
        display 220ms allow-discrete;
}

.gallery-lightbox::backdrop {
    background: rgb(1 11 9 / 90%);
    backdrop-filter: blur(12px);
}

.gallery-lightbox[open] {
    opacity: 1;
    transform: scale(1);
}

.gallery-lightbox.is-closing {
    opacity: 0;
    transform: scale(0.975);
}

.gallery-lightbox__panel {
    position: relative;
    width: 100%;
    height: 100%;
}

.gallery-lightbox__media {
    display: grid;
    width: 100%;
    height: 100%;
    grid-template-rows: minmax(0, 1fr) auto;
    overflow: hidden;
    border: 1px solid rgb(255 255 255 / 12%);
    background: rgb(3 19 16 / 98%);
    box-shadow: var(--shadow-elevated);
}

.gallery-lightbox__image-shell {
    position: relative;
    display: grid;
    min-height: 0;
    place-items: center;
    overflow: hidden;
    background: rgb(1 11 9);
}

.gallery-lightbox__image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    opacity: 0;
    transform: scale(1.015);
    transition:
        opacity 260ms ease,
        transform 500ms var(--ease-island);
}

.gallery-lightbox[data-gallery-image-state="loaded"]
    .gallery-lightbox__image {
    opacity: 1;
    transform: scale(1);
}

.gallery-lightbox__caption {
    display: grid;
    gap: 0.35rem;
    padding: 1rem 4.5rem 1.15rem 1.25rem;
    border-top: 1px solid rgb(255 255 255 / 10%);
}

.gallery-lightbox__category {
    color: var(--color-sun);
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.22em;
    text-transform: uppercase;
}

.gallery-lightbox__title {
    font-family: var(--font-display);
    font-size: clamp(1.4rem, 2.4vw, 2.25rem);
    line-height: 1.1;
}

.gallery-lightbox__close,
.gallery-lightbox__navigation {
    position: absolute;
    z-index: 3;
    display: grid;
    width: 3rem;
    height: 3rem;
    place-items: center;
    border: 1px solid rgb(255 255 255 / 20%);
    border-radius: var(--radius-pill);
    background: rgb(3 19 16 / 78%);
    color: white;
    font-size: 1.25rem;
    backdrop-filter: blur(12px);
    transition:
        background 200ms ease,
        border-color 200ms ease,
        transform 200ms var(--ease-island);
}

.gallery-lightbox__close:hover,
.gallery-lightbox__navigation:hover {
    border-color: var(--color-coral);
    background: var(--color-coral);
}

.gallery-lightbox__close {
    top: 1rem;
    right: 1rem;
}

.gallery-lightbox__navigation {
    top: 50%;
    transform: translateY(-50%);
}

.gallery-lightbox__navigation:hover {
    transform: translateY(-50%) scale(1.05);
}

.gallery-lightbox__navigation--previous {
    left: 1rem;
}

.gallery-lightbox__navigation--next {
    right: 1rem;
}

.gallery-lightbox__navigation:disabled {
    display: none;
}

@starting-style {
    .gallery-lightbox[open] {
        opacity: 0;
        transform: scale(0.975);
    }
}

@media (min-width: 768px) {
    .gallery-collection__header {
        grid-template-columns: minmax(0, 1fr) minmax(18rem, 0.55fr);
    }

    .gallery-contact-card {
        min-height: 23rem;
        grid-column: span 6;
    }

    .gallery-contact-card--wide {
        grid-column: span 12;
        min-height: 30rem;
    }

    .gallery-contact-card--portrait,
    .gallery-contact-card--tall {
        min-height: 32rem;
    }
}

@media (min-width: 1024px) {
    .gallery-hero__grid {
        grid-template-columns: minmax(0, 1fr) minmax(26rem, 0.76fr);
    }

    .gallery-signature__grid {
        grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
    }

    .gallery-collection__layout {
        grid-template-columns: minmax(13rem, 0.28fr) minmax(0, 1fr);
    }

    .gallery-film-index {
        position: sticky;
        top: 7rem;
    }

    .gallery-contact-card {
        grid-column: span 6;
    }

    .gallery-contact-card--wide {
        grid-column: span 8;
    }

    .gallery-contact-card--portrait,
    .gallery-contact-card--tall {
        grid-column: span 4;
    }
}

@media (max-width: 1023px) {
    .gallery-hero__copy {
        max-width: 42rem;
    }

    .gallery-hero__stack {
        width: min(100%, 38rem);
        margin-inline: auto;
    }
}

@media (max-width: 639px) {
    .gallery-hero__grid {
        padding-top: 8rem;
    }

    .gallery-hero__title {
        font-size: clamp(3.25rem, 17vw, 5rem);
    }

    .gallery-hero__stack {
        min-height: 26rem;
    }

    .gallery-stack-card {
        border-width: 0.4rem;
        border-bottom-width: 2.35rem;
    }

    .gallery-hero__scroll {
        display: none;
    }

    .gallery-chapter {
        grid-template-columns: 2.5rem minmax(0, 1fr);
    }

    .gallery-chapter__count {
        grid-column: 2;
    }

    .gallery-contact-card {
        min-height: 21rem;
    }

    .gallery-contact-card__open {
        display: none;
    }

    .gallery-pagination {
        grid-template-columns: 1fr 1fr;
    }

    .gallery-pagination__status {
        grid-column: 1 / -1;
        grid-row: 1;
        text-align: center;
    }

    .gallery-lightbox {
        width: 100vw;
        height: 100dvh;
    }

    .gallery-lightbox__media {
        border: 0;
    }

    .gallery-lightbox__navigation {
        top: auto;
        bottom: 5.4rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .gallery-stack-card,
    .gallery-contact-card__image,
    .gallery-contact-card__open,
    .gallery-filter-link,
    .gallery-lightbox,
    .gallery-lightbox__image,
    .gallery-lightbox__close,
    .gallery-lightbox__navigation {
        transition: none;
    }

    .gallery-contact-card__button:hover .gallery-contact-card__image,
    .gallery-contact-card__button:focus-visible .gallery-contact-card__image,
    .gallery-lightbox,
    .gallery-lightbox[open],
    .gallery-lightbox.is-closing,
    .gallery-lightbox__image {
        opacity: 1;
        transform: none;
    }

    [data-gallery-reveal],
    [data-gallery-stack-card],
    [data-gallery-item] {
        opacity: 1 !important;
        visibility: visible !important;
        clip-path: none !important;
        transform: none !important;
    }
}
EOF_RESOURCES_CSS_GALLERY_CSS

cat > resources/css/theme.css <<'EOF_RESOURCES_CSS_THEME_CSS'
@import "./brand-tokens.css";
@import "./menu.css";
@import "./menu-page-enhancements.css";
@import "./about.css";
@import "./gallery.css";

@custom-variant dark (&:where(.dark, .dark *));

@layer theme {
    .dark {
        --color-accent: var(--color-brand-cream);
        --color-accent-content: var(--color-brand-cream);
        --color-accent-foreground: var(--color-brand-palm-dark);
    }
}

@layer base {
    *,
    ::after,
    ::before,
    ::backdrop,
    ::file-selector-button {
        border-color: var(--color-gray-200, currentColor);
    }

    html {
        scroll-behavior: smooth;
        scroll-padding-top: 6rem;
    }

    body {
        background: var(--color-brand-cream);
        color: var(--color-brand-forest);
    }

    ::selection {
        background: color-mix(
            in srgb,
            var(--color-brand-coral) 35%,
            transparent
        );
        color: var(--color-brand-palm-dark);
    }

    :where(a, button, input, select, textarea):focus-visible {
        outline: 3px solid var(--color-brand-ocean);
        outline-offset: 3px;
    }
}

@layer components {
    /*
     * Reusable translucent panel for category navigation and compact
     * editorial content.
     */
    .menu-glass-panel {
        border: 1px solid rgb(22 83 68 / 12%);
        background: rgb(255 253 249 / 94%);
        box-shadow:
            0 24px 70px rgb(12 52 43 / 11%),
            0 4px 16px rgb(12 52 43 / 6%);
    }

    @supports (backdrop-filter: blur(1rem)) {
        .menu-glass-panel {
            background: rgb(255 253 249 / 76%);
            backdrop-filter: blur(1.1rem) saturate(1.08);
        }
    }

    /*
     * Both desktop and mobile category navigation use aria-current as the
     * active-state source of truth.
     */
    .menu-category-link[aria-current="true"] {
        border-color: rgb(255 255 255 / 18%);
        background: var(--color-primary);
        color: rgb(255 255 255);
        box-shadow:
            0 12px 30px rgb(12 52 43 / 16%),
            0 2px 8px rgb(12 52 43 / 8%);
    }

    .menu-category-link[aria-current="true"] .menu-category-label {
        color: rgb(255 255 255);
    }

    .menu-category-link[aria-current="true"] .menu-category-index {
        background: rgb(255 255 255 / 16%);
        color: rgb(255 255 255);
    }

    .menu-category-link[aria-current="true"] .menu-category-dot {
        background: var(--color-coral);
        box-shadow: 0 0 0 3px rgb(255 255 255 / 15%);
    }

    /*
     * Shared menu cards preserve restrained transitions even when they appear
     * outside the main menu page.
     */
    [data-menu-card] {
        transition:
            opacity 360ms var(--ease-island),
            transform 360ms var(--ease-island),
            border-color 300ms var(--ease-island),
            box-shadow 300ms var(--ease-island);
    }

    [data-menu-card].is-menu-card-entering {
        opacity: 0;
        transform: translateY(0.75rem);
    }
}

[x-cloak] {
    display: none !important;
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }

    [data-menu-card],
    [data-menu-card].is-menu-card-entering {
        opacity: 1;
        transform: none;
    }
}
EOF_RESOURCES_CSS_THEME_CSS

touch resources/js/gallery-experience.js

cat > resources/js/gallery-experience.js <<'EOF_RESOURCES_JS_GALLERY-EXPERIENCE_JS'
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

const reducedMotionQuery = "(prefers-reduced-motion: reduce)";
const desktopQuery = "(min-width: 1024px) and (pointer: fine)";
const dialogCloseDuration = 220;

/**
 * Populate the shared lightbox from one server-rendered gallery link.
 */
function setDialogContent(dialog, opener) {
    const image = dialog.querySelector("[data-gallery-dialog-image]");
    const title = dialog.querySelector("[data-gallery-dialog-title]");
    const category = dialog.querySelector("[data-gallery-dialog-category]");

    if (!(image instanceof HTMLImageElement)) {
        return;
    }

    dialog.dataset.galleryImageState = "loading";
    image.src = opener.dataset.gallerySrc ?? opener.href;
    image.alt = opener.dataset.galleryAlt ?? "";
    image.sizes = "min(88vw, 1440px)";

    if (opener.dataset.gallerySrcset) {
        image.srcset = opener.dataset.gallerySrcset;
    } else {
        image.removeAttribute("srcset");
    }

    if (title) {
        title.textContent = opener.dataset.galleryTitle ?? "Coast & Cay moment";
    }

    if (category) {
        category.textContent = opener.dataset.galleryCategory ?? "Coast & Cay";
    }

    if (image.complete) {
        dialog.dataset.galleryImageState = "loaded";
    }
}

/**
 * Initialize the native dialog viewer, keyboard navigation, and focus return.
 */
function initializeGalleryDialog(root) {
    const dialog = root.querySelector("[data-gallery-dialog]");
    const openers = [...root.querySelectorAll("[data-gallery-open]")].filter(
        (opener) => opener instanceof HTMLAnchorElement,
    );

    if (!(dialog instanceof HTMLDialogElement) || openers.length === 0) {
        return () => {};
    }

    const image = dialog.querySelector("[data-gallery-dialog-image]");
    const previousButton = dialog.querySelector("[data-gallery-previous]");
    const nextButton = dialog.querySelector("[data-gallery-next]");

    let activeIndex = 0;
    let activeOpener = null;
    let closeTimer = null;

    /**
     * Render one image and wrap navigation within the current result page.
     */
    const showImage = (requestedIndex) => {
        activeIndex = (requestedIndex + openers.length) % openers.length;
        setDialogContent(dialog, openers[activeIndex]);
    };

    /**
     * Open the viewer from one gallery link and remember its focus origin.
     */
    const openDialog = (opener) => {
        const openerIndex = openers.indexOf(opener);

        if (openerIndex < 0) {
            return;
        }

        activeOpener = opener;
        showImage(openerIndex);
        dialog.classList.remove("is-closing");
        document.documentElement.classList.add("gallery-dialog-open");

        const navigationDisabled = openers.length < 2;

        if (previousButton instanceof HTMLButtonElement) {
            previousButton.disabled = navigationDisabled;
        }

        if (nextButton instanceof HTMLButtonElement) {
            nextButton.disabled = navigationDisabled;
        }

        if (!dialog.open) {
            dialog.showModal();
        }
    };

    /**
     * Complete closure after the optional CSS exit transition.
     */
    const finalizeClose = () => {
        if (closeTimer !== null) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }

        if (dialog.open) {
            dialog.close();
        }
    };

    /**
     * Close the viewer without delaying reduced-motion users.
     */
    const closeDialog = () => {
        if (!dialog.open || dialog.classList.contains("is-closing")) {
            return;
        }

        if (window.matchMedia(reducedMotionQuery).matches) {
            finalizeClose();

            return;
        }

        dialog.classList.add("is-closing");
        closeTimer = window.setTimeout(finalizeClose, dialogCloseDuration);
    };

    /**
     * Intercept gallery links while preserving their no-JavaScript fallback URL.
     */
    const handleRootClick = (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const opener = target?.closest("[data-gallery-open]");

        if (!(opener instanceof HTMLAnchorElement)) {
            return;
        }

        event.preventDefault();
        openDialog(opener);
    };

    /**
     * Handle close, previous, next, and backdrop interactions.
     */
    const handleDialogClick = (event) => {
        if (event.target === dialog) {
            closeDialog();

            return;
        }

        const target = event.target instanceof Element ? event.target : null;

        if (target?.closest("[data-gallery-close]")) {
            closeDialog();
        } else if (target?.closest("[data-gallery-previous]")) {
            showImage(activeIndex - 1);
        } else if (target?.closest("[data-gallery-next]")) {
            showImage(activeIndex + 1);
        }
    };

    /**
     * Preserve Escape-to-close while allowing the exit transition to finish.
     */
    const handleDialogCancel = (event) => {
        event.preventDefault();
        closeDialog();
    };

    /**
     * Support familiar arrow-key image navigation.
     */
    const handleDialogKeydown = (event) => {
        if (event.key === "ArrowLeft") {
            event.preventDefault();
            showImage(activeIndex - 1);
        } else if (event.key === "ArrowRight") {
            event.preventDefault();
            showImage(activeIndex + 1);
        }
    };

    /**
     * Reveal the current image only after it has loaded.
     */
    const handleImageLoad = () => {
        dialog.dataset.galleryImageState = "loaded";
    };

    /**
     * Reset media state and restore focus after native dialog closure.
     */
    const handleDialogClose = () => {
        dialog.classList.remove("is-closing");
        dialog.dataset.galleryImageState = "idle";
        document.documentElement.classList.remove("gallery-dialog-open");

        if (image instanceof HTMLImageElement) {
            image.removeAttribute("src");
            image.removeAttribute("srcset");
            image.alt = "";
        }

        activeOpener?.focus({ preventScroll: true });
        activeOpener = null;
    };

    root.addEventListener("click", handleRootClick);
    dialog.addEventListener("click", handleDialogClick);
    dialog.addEventListener("cancel", handleDialogCancel);
    dialog.addEventListener("keydown", handleDialogKeydown);
    dialog.addEventListener("close", handleDialogClose);
    image?.addEventListener("load", handleImageLoad);

    return () => {
        root.removeEventListener("click", handleRootClick);
        dialog.removeEventListener("click", handleDialogClick);
        dialog.removeEventListener("cancel", handleDialogCancel);
        dialog.removeEventListener("keydown", handleDialogKeydown);
        dialog.removeEventListener("close", handleDialogClose);
        image?.removeEventListener("load", handleImageLoad);

        if (closeTimer !== null) {
            window.clearTimeout(closeTimer);
        }

        document.documentElement.classList.remove("gallery-dialog-open");

        if (dialog.open) {
            dialog.close();
        }
    };
}

/**
 * Restore the final readable state for reduced-motion users.
 */
function setReducedMotionState(root) {
    root.dataset.galleryMotionState = "reduced";

    gsap.set(
        root.querySelectorAll(
            "[data-gallery-reveal], [data-gallery-stack-card], [data-gallery-item]",
        ),
        {
            autoAlpha: 1,
            clearProps: "transform,opacity,visibility",
        },
    );
}

/**
 * Initialize restrained hero, section, parallax, and contact-sheet motion.
 */
function initializeGalleryMotion(root) {
    const media = gsap.matchMedia();

    media.add(
        {
            desktop: desktopQuery,
            reducedMotion: reducedMotionQuery,
        },
        (context) => {
            const { desktop = false, reducedMotion = false } =
                context.conditions;

            if (reducedMotion) {
                setReducedMotionState(root);

                return;
            }

            root.dataset.galleryMotionState = "ready";

            const hero = root.querySelector("[data-gallery-hero]");
            const heroImage = root.querySelector("[data-gallery-hero-image] img");
            const heroCopy = root.querySelectorAll(
                "[data-gallery-hero-copy] [data-gallery-reveal]",
            );
            const heroCards = root.querySelectorAll(
                "[data-gallery-stack-card]",
            );

            const heroTimeline = gsap.timeline({
                defaults: { ease: "power4.out" },
            });

            if (heroImage instanceof HTMLImageElement) {
                heroTimeline.fromTo(
                    heroImage,
                    { scale: 1.06 },
                    { duration: 1.6, scale: 1 },
                    0,
                );
            }

            heroTimeline
                .fromTo(
                    heroCopy,
                    { autoAlpha: 0, y: 32 },
                    {
                        autoAlpha: 1,
                        duration: 1,
                        stagger: 0.1,
                        y: 0,
                    },
                    0.08,
                )
                .fromTo(
                    heroCards,
                    { autoAlpha: 0, y: 72 },
                    {
                        autoAlpha: 1,
                        duration: 1.05,
                        stagger: 0.12,
                        y: 0,
                    },
                    0.2,
                );

            if (
                hero instanceof HTMLElement
                && heroImage instanceof HTMLImageElement
            ) {
                gsap.fromTo(
                    heroImage,
                    { yPercent: 0 },
                    {
                        ease: "none",
                        scrollTrigger: {
                            id: "gallery-hero-parallax",
                            trigger: hero,
                            start: "top top",
                            end: "bottom top",
                            scrub: true,
                            invalidateOnRefresh: true,
                        },
                        yPercent: desktop ? 7 : 3,
                    },
                );
            }

            const revealTargets = [
                ...root.querySelectorAll("[data-gallery-reveal]"),
            ].filter((target) => !target.closest("[data-gallery-hero]"));

            revealTargets.forEach((target) => {
                gsap.fromTo(
                    target,
                    { autoAlpha: 0, y: 34 },
                    {
                        autoAlpha: 1,
                        duration: 0.82,
                        ease: "power3.out",
                        scrollTrigger: {
                            trigger: target,
                            start: "top 86%",
                            once: true,
                        },
                        y: 0,
                    },
                );
            });

            const galleryItems = root.querySelectorAll("[data-gallery-item]");

            if (galleryItems.length > 0) {
                gsap.set(galleryItems, { autoAlpha: 0, y: 46 });

                ScrollTrigger.batch(galleryItems, {
                    interval: 0.08,
                    once: true,
                    start: "top 90%",
                    onEnter: (batch) => {
                        gsap.to(batch, {
                            autoAlpha: 1,
                            duration: 0.8,
                            ease: "power3.out",
                            stagger: 0.08,
                            y: 0,
                        });
                    },
                });
            }
        },
    );

    /**
     * Recalculate trigger positions after responsive media has settled.
     */
    const refreshTriggers = () => {
        ScrollTrigger.refresh();
    };

    if (document.readyState === "complete") {
        refreshTriggers();
    } else {
        window.addEventListener("load", refreshTriggers, { once: true });
    }

    return () => {
        window.removeEventListener("load", refreshTriggers);
        media.revert();
    };
}

/**
 * Initialize the complete gallery experience and return one cleanup callback.
 */
export function initGalleryExperience(
    root = document.querySelector("[data-gallery-page]"),
) {
    if (!root) {
        return () => {};
    }

    const cleanupDialog = initializeGalleryDialog(root);
    const cleanupMotion = initializeGalleryMotion(root);

    const cleanup = () => {
        cleanupDialog();
        cleanupMotion();
    };

    if (import.meta.hot) {
        import.meta.hot.dispose(cleanup);
    }

    return cleanup;
}
EOF_RESOURCES_JS_GALLERY-EXPERIENCE_JS

cat > resources/js/app.js <<'EOF_RESOURCES_JS_APP_JS'
import {
    Alpine,
    Livewire,
} from "../../vendor/livewire/livewire/dist/livewire.esm";
import contactForm from "./forms/contact-form";

window.Alpine = Alpine;

Alpine.data("contactForm", contactForm);

Livewire.start();

const publicHeader = document.querySelector(
    "[data-public-header-shell] > header",
);

if (publicHeader) {
    import("./public-header")
        .then(({ initPublicHeader }) => {
            initPublicHeader(publicHeader);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize the shared public header.",
                error,
            );
        });
}

if (document.querySelector("[data-reveal]")) {
    import("./public-reveals")
        .then(({ initPublicReveals }) => {
            initPublicReveals();
        })
        .catch((error) => {
            console.error(
                "Unable to initialize public reveals.",
                error,
            );
        });
}

const publicMotionRoot = document.querySelector(
    "[data-home-motion]",
);

if (publicMotionRoot) {
    import("./public-animations")
        .then(({ initPublicAnimations }) => {
            initPublicAnimations(publicMotionRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize public animations.",
                error,
            );
        });
}

const homepagePagerRoot = document.querySelector(
    "[data-home-section-pager]",
);

if (homepagePagerRoot) {
    import("./homepage-section-navigation")
        .then(({ initHomepageSectionNavigation }) => {
            initHomepageSectionNavigation(
                homepagePagerRoot,
            );
        })
        .catch((error) => {
            console.error(
                "Unable to initialize homepage section navigation.",
                error,
            );
        });
}

const menuPageRoot = document.querySelector(
    "[data-menu-page]",
);

if (menuPageRoot) {
    import("./menu-experience")
        .then(({ initMenuExperience }) => {
            initMenuExperience(menuPageRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize the menu experience.",
                error,
            );
        });

    import("./menu-category-scroll")
        .then(({ initMenuCategoryScroll }) => {
            initMenuCategoryScroll(menuPageRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize menu category navigation.",
                error,
            );
        });
}

const aboutPageRoot = document.querySelector(
    "[data-about-page]",
);

if (aboutPageRoot) {
    import("./about-experience")
        .then(({ initAboutExperience }) => {
            initAboutExperience(aboutPageRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize the About page experience.",
                error,
            );
        });
}

const galleryPageRoot = document.querySelector(
    "[data-gallery-page]",
);

if (galleryPageRoot) {
    import("./gallery-experience")
        .then(({ initGalleryExperience }) => {
            initGalleryExperience(galleryPageRoot);
        })
        .catch((error) => {
            console.error(
                "Unable to initialize the Gallery page experience.",
                error,
            );
        });
}
EOF_RESOURCES_JS_APP_JS

touch tests/Feature/PublicSite/GalleryPageTest.php

cat > tests/Feature/PublicSite/GalleryPageTest.php <<'EOF_TESTS_FEATURE_PUBLICSITE_GALLERYPAGETEST_PHP'
<?php

use App\Models\GalleryImage;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create one deterministic gallery record without invoking image processing.
 *
 * @param  array<string, mixed>  $overrides
 */
function createGalleryPageImage(array $overrides = []): GalleryImage
{
    return GalleryImage::withoutEvents(
        fn (): GalleryImage => GalleryImage::query()->create([
            'title' => 'Island Supper',
            'alt_text' => 'A Caribbean-inspired dinner at Coast and Cay',
            'image_path' => 'gallery/island-supper.jpg',
            'category' => 'dish',
            'sort_order' => 1,
            'is_visible' => true,
            ...$overrides,
        ]),
    );
}

beforeEach(function (): void {
    Page::query()->create([
        'slug' => 'gallery',
        'title' => 'Gallery',
        'excerpt' => 'A visual journal of Coast and Cay.',
        'content' => 'Food, hospitality, and coastal moments.',
        'is_published' => true,
    ]);
});

test('the gallery renders the editorial experience with visible images', function (): void {
    createGalleryPageImage();

    createGalleryPageImage([
        'title' => 'Sunset Dining Room',
        'image_path' => 'gallery/sunset-room.jpg',
        'category' => 'ambiance',
        'sort_order' => 2,
    ]);

    createGalleryPageImage([
        'title' => 'Hidden Draft',
        'image_path' => 'gallery/hidden-draft.jpg',
        'is_visible' => false,
        'sort_order' => 3,
    ]);

    $response = $this->get(route('gallery'));

    $response
        ->assertOk()
        ->assertSee('data-gallery-page', false)
        ->assertSee('data-gallery-collection', false)
        ->assertSee('data-gallery-dialog', false)
        ->assertSeeText('Island Supper')
        ->assertSeeText('Sunset Dining Room')
        ->assertDontSeeText('Hidden Draft');
});

test('the gallery filters visible images by a valid category', function (): void {
    createGalleryPageImage();

    createGalleryPageImage([
        'title' => 'Ocean Room',
        'image_path' => 'gallery/ocean-room.jpg',
        'category' => 'ambiance',
        'sort_order' => 2,
    ]);

    $response = $this->get(route('gallery', [
        'category' => 'dish',
    ]));

    $response
        ->assertOk()
        ->assertSeeText('Island Supper')
        ->assertDontSeeText('Ocean Room')
        ->assertSee('data-gallery-category="dish"', false)
        ->assertSee('aria-current="true"', false);
});

test('an unknown category safely falls back to the complete collection', function (): void {
    createGalleryPageImage();

    createGalleryPageImage([
        'title' => 'Warm Welcome',
        'image_path' => 'gallery/warm-welcome.jpg',
        'category' => 'people',
        'sort_order' => 2,
    ]);

    $response = $this->get(route('gallery', [
        'category' => 'not-a-real-category',
    ]));

    $response
        ->assertOk()
        ->assertSeeText('Island Supper')
        ->assertSeeText('Warm Welcome')
        ->assertSeeText('The complete contact sheet');
});
EOF_TESTS_FEATURE_PUBLICSITE_GALLERYPAGETEST_PHP

touch tests/Browser/gallery.spec.ts

cat > tests/Browser/gallery.spec.ts <<'EOF_TESTS_BROWSER_GALLERY_SPEC_TS'
import { expect, test } from "@playwright/test";

test("gallery filtering and native image viewer remain usable", async ({
    page,
}) => {
    await page.goto("/gallery");

    const gallery = page.locator("[data-gallery-page]");
    const cards = page.locator("[data-gallery-open]");

    await expect(gallery).toBeVisible();
    await expect(gallery).toHaveAttribute(
        "data-gallery-motion-state",
        /ready|reduced/,
    );
    await expect(cards).toHaveCount(3);

    await cards.first().click();

    const dialog = page.locator("[data-gallery-dialog]");
    const dialogTitle = page.locator("[data-gallery-dialog-title]");
    const firstTitle = await dialogTitle.textContent();

    await expect(dialog).toBeVisible();
    await expect(dialogTitle).not.toBeEmpty();

    await page.keyboard.press("ArrowRight");

    await expect
        .poll(async () => dialogTitle.textContent())
        .not.toBe(firstTitle);

    await page.keyboard.press("Escape");
    await expect(dialog).not.toBeVisible();

    await page
        .locator('[data-gallery-filter][data-gallery-category="dish"]')
        .click();

    await expect(page).toHaveURL(/category=dish/);
    await expect(page.locator("[data-gallery-open]")).toHaveCount(2);
    await expect(
        page.locator(
            '[data-gallery-filter][data-gallery-category="dish"]',
        ),
    ).toHaveAttribute("aria-current", "true");
});
EOF_TESTS_BROWSER_GALLERY_SPEC_TS

# Format first, then run the complete repository quality gate and browser test.
./vendor/bin/sail composer lint
./vendor/bin/sail composer ci:check
./vendor/bin/sail npm run test:browser -- tests/Browser/gallery.spec.ts

git status --short
