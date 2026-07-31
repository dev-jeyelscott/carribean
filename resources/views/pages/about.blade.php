<x-layouts.public
    :title="$page->meta_title ?: $page->title"
    :description="$page->meta_description ?: $page->excerpt"
    :header-overlay="true">
    @php
    /*
    * Resolve structured editable content with safe Coast & Cay defaults.
    */
    $sections = is_array($page->sections)
    ? $page->sections
    : [];

    $heroTitle = data_get(
    $sections,
    'hero.title',
    'About Coast & Cay.',
    );

    $heroAccent = data_get(
    $sections,
    'hero.accent',
    'Rooted in the Caribbean. Inspired by California.',
    );

    $heroDescription = filled($page->excerpt)
    ? $page->excerpt
    : 'A meeting of cultures, a celebration of flavor, and a place where everyone feels at home.';

    $storyTitle = data_get(
    $sections,
    'story.title',
    'From island roots to the California coast.',
    );

    $storyDescription = data_get(
    $sections,
    'story.description',
    );

    if (! is_string($storyDescription) || blank($storyDescription)) {
    $storyDescription = filled($page->content)
    ? str($page->content)->stripTags()->squish()
    : 'Coast & Cay brings Caribbean warmth to the California coast through vibrant food, thoughtful ingredients, and genuine hospitality.';
    }

    $storyQuote = data_get(
    $sections,
    'story.quote',
    'Caribbean warmth. California ease.',
    );

    $defaultValues = collect([
    [
    'number' => '01',
    'title' => 'Bold Flavor',
    'description' => 'Vibrant, soulful flavors that celebrate the Caribbean spirit.',
    ],
    [
    'number' => '02',
    'title' => 'Genuine Hospitality',
    'description' => 'Warm welcomes, attentive service, and care in every detail.',
    ],
    [
    'number' => '03',
    'title' => 'California Ease',
    'description' => 'A relaxed, modern atmosphere where good food and good times flow.',
    ],
    [
    'number' => '04',
    'title' => 'Caribbean Roots',
    'description' => 'Traditions, ingredients, and stories that continue to inspire us.',
    ],
    ]);

    $values = collect(
    data_get($sections, 'values', []),
    )
    ->filter(fn (mixed $item): bool => is_array($item))
    ->take(4)
    ->values();

    if ($values->count() < 4) {
        $values=$values
        ->concat(
        $defaultValues->slice($values->count()),
        )
        ->take(4)
        ->values();
        }

        $defaultHeritageItems = collect([
        [
        'title' => 'Inspired by Islands',
        'description' => 'Caribbean traditions and time-honored recipes.',
        ],
        [
        'title' => 'Thoughtful Ingredients',
        'description' => 'Seasonal, sustainable, and locally sourced where possible.',
        ],
        [
        'title' => 'Made with Care',
        'description' => 'Prepared daily by people who love what they do.',
        ],
        ]);

        $heritageItems = collect(
        data_get($sections, 'heritage.items', []),
        )
        ->filter(fn (mixed $item): bool => is_array($item))
        ->take(3)
        ->values();

        if ($heritageItems->count() < 3) {
            $heritageItems=$heritageItems
            ->concat(
            $defaultHeritageItems->slice(
            $heritageItems->count(),
            ),
            )
            ->take(3)
            ->values();
            }

            $defaultExperienceItems = collect([
            ['text' => 'Warm welcomes from the moment you arrive.'],
            ['text' => 'Dishes that surprise and satisfy, every time.'],
            ['text' => 'A space that feels elevated and easygoing.'],
            ['text' => 'Moments worth savoring and sharing.'],
            ]);

            $experienceItems = collect(
            data_get($sections, 'experience.items', []),
            )
            ->filter(fn (mixed $item): bool => is_array($item))
            ->take(4)
            ->values();

            if ($experienceItems->count() < 4) {
                $experienceItems=$experienceItems
                ->concat(
                $defaultExperienceItems->slice(
                $experienceItems->count(),
                ),
                )
                ->take(4)
                ->values();
                }

                $aboutNavigation = [
                [
                'id' => 'about-hero',
                'label' => 'Introduction',
                ],
                [
                'id' => 'island-roots',
                'label' => 'Our story',
                ],
                [
                'id' => 'values',
                'label' => 'Our values',
                ],
                [
                'id' => 'heritage',
                'label' => 'Our heritage',
                ],
                [
                'id' => 'experience',
                'label' => 'Guest experience',
                ],
                [
                'id' => 'invitation',
                'label' => 'Your invitation',
                ],
                ];
                @endphp

                <div
                    data-about-page
                    data-about-active-section="about-hero"
                    data-about-motion="loading"
                    class="about-page">
                    <x-public.section-pager
                        :items="$aboutNavigation"
                        current="about-hero"
                        label="About page sections"
                        context="about"
                        enhancer="about" />

                    {{-- Full-screen About hero --}}
                    <section
                        id="about-hero"
                        data-about-panel
                        data-about-section="about-hero"
                        aria-labelledby="about-hero-heading"
                        class="about-panel isolate bg-primary-deep text-canvas">
                        @if ($heroImage?->image_url)
                        <div
                            data-about-hero-image
                            data-about-parallax
                            class="absolute inset-0 -z-30 overflow-hidden">
                            <x-public.responsive-image
                                :image="$heroImage"
                                :alt="$heroImage->alt_text ?: $heroImage->title ?: 'Warm Coast and Cay restaurant dining room'"
                                variant="hero"
                                sizes="100vw"
                                width="2000"
                                height="1400"
                                loading="eager"
                                fetchpriority="high"
                                img-class="size-full object-cover" />
                        </div>
                        @endif

                        <div
                            class="absolute inset-0 -z-20 bg-gradient-to-r
                    from-primary-deep via-primary-deep/75
                    to-primary-deep/15"
                            aria-hidden="true">
                        </div>

                        <div
                            class="absolute inset-0 -z-10 bg-gradient-to-t
                    from-primary-deep/75 via-transparent to-black/25"
                            aria-hidden="true">
                        </div>

                        <div class="public-container flex min-h-[100svh] items-center py-32">
                            <div
                                data-about-hero-content
                                class="max-w-3xl pt-12">
                                <p
                                    data-about-reveal
                                    class="text-xs font-semibold uppercase
                            tracking-[0.32em] text-sun">
                                    Our Story
                                </p>

                                <h1
                                    id="about-hero-heading"
                                    data-about-reveal
                                    class="mt-5 font-display text-5xl leading-[0.98]
                            text-canvas sm:text-6xl lg:text-7xl xl:text-8xl">
                                    {{ $heroTitle }}
                                </h1>

                                <p
                                    data-about-reveal
                                    class="mt-4 max-w-2xl font-display text-3xl
                            italic leading-tight text-coral
                            sm:text-4xl lg:text-5xl">
                                    {{ $heroAccent }}
                                </p>

                                <p
                                    data-about-reveal
                                    class="mt-7 max-w-xl text-base leading-8
                            text-canvas/76 sm:text-lg">
                                    {{ $heroDescription }}
                                </p>

                                <div
                                    data-about-reveal
                                    class="mt-9 flex flex-wrap gap-3">
                                    <a
                                        href="#island-roots"
                                        class="public-button-primary">
                                        Explore Our Story
                                    </a>

                                    <a
                                        href="{{ route('gallery') }}"
                                        class="public-button-secondary text-canvas">
                                        View Gallery
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Island roots and restaurant story --}}
                    <section
                        id="island-roots"
                        data-about-panel
                        data-about-section="island-roots"
                        aria-labelledby="island-roots-heading"
                        class="about-panel bg-canvas">
                        <div class="grid min-h-[100svh] w-full lg:grid-cols-2">
                            <div
                                class="flex items-center px-5 py-24 sm:px-8
                        lg:px-12 xl:px-[max(4rem,calc((100vw-86rem)/2+2.5rem))]">
                                <div class="max-w-xl">
                                    <p
                                        data-about-reveal
                                        class="public-eyebrow">
                                        {{ data_get(
                                $sections,
                                'story.eyebrow',
                                'Our Story',
                            ) }}
                                    </p>

                                    <h2
                                        id="island-roots-heading"
                                        data-about-reveal
                                        class="mt-5 font-display text-4xl leading-[1.05]
                                text-ink sm:text-5xl lg:text-6xl">
                                        {{ $storyTitle }}
                                    </h2>

                                    <div
                                        data-about-reveal
                                        class="mt-6 h-px w-28 bg-coral"
                                        aria-hidden="true">
                                    </div>

                                    <p
                                        data-about-reveal
                                        class="mt-7 text-base leading-8 text-muted">
                                        {{ $storyDescription }}
                                    </p>

                                    <p
                                        data-about-reveal
                                        class="mt-5 text-base leading-8 text-muted">
                                        Our chefs blend time-honored Caribbean inspiration
                                        with California ingredients to create food that feels
                                        both familiar and new.
                                    </p>

                                    <div
                                        data-about-reveal
                                        class="mt-10 grid grid-cols-3 gap-5
                                border-t border-line pt-8">
                                        <article>
                                            <p class="font-display text-2xl text-coral">
                                                1
                                            </p>

                                            <h3 class="mt-2 text-sm font-semibold text-ink">
                                                Passion
                                            </h3>

                                            <p class="mt-2 text-xs leading-5 text-muted">
                                                A shared love for food and people.
                                            </p>
                                        </article>

                                        <article>
                                            <p class="font-display text-2xl text-coral">
                                                2
                                            </p>

                                            <h3 class="mt-2 text-sm font-semibold text-ink">
                                                Culture
                                            </h3>

                                            <p class="mt-2 text-xs leading-5 text-muted">
                                                Caribbean roots and California ease.
                                            </p>
                                        </article>

                                        <article>
                                            <p class="font-display text-2xl text-coral">
                                                ∞
                                            </p>

                                            <h3 class="mt-2 text-sm font-semibold text-ink">
                                                Memories
                                            </h3>

                                            <p class="mt-2 text-xs leading-5 text-muted">
                                                Moments that bring everyone together.
                                            </p>
                                        </article>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="relative min-h-[65svh] overflow-hidden
                        lg:min-h-[100svh]">
                                @if ($storyImage?->image_url)
                                <div
                                    data-about-image-reveal
                                    class="absolute inset-0 overflow-hidden">
                                    <x-public.responsive-image
                                        :image="$storyImage"
                                        :alt="$storyImage->alt_text ?: $storyImage->title ?: 'Elegant Coast and Cay dining room'"
                                        variant="hero"
                                        sizes="(min-width: 1024px) 50vw, 100vw"
                                        width="1600"
                                        height="1500"
                                        img-class="size-full object-cover" />
                                </div>
                                @endif

                                <div
                                    class="absolute inset-0 bg-gradient-to-t
                            from-primary-deep/45 via-transparent to-transparent"
                                    aria-hidden="true">
                                </div>

                                <blockquote
                                    data-about-reveal
                                    class="about-quote-card absolute bottom-8 left-5
                            max-w-xs rounded-panel bg-primary-deep/94
                            p-7 text-canvas shadow-elevated
                            backdrop-blur-md sm:bottom-10 sm:left-8
                            lg:left-0 lg:-translate-x-1/2">
                                    <p class="font-display text-4xl leading-none text-sun">
                                        “
                                    </p>

                                    <p class="mt-3 font-display text-2xl italic leading-tight">
                                        {{ $storyQuote }}
                                    </p>
                                </blockquote>
                            </div>
                        </div>
                    </section>

                    {{-- Four restaurant values --}}
                    <section
                        id="values"
                        data-about-panel
                        data-about-section="values"
                        aria-labelledby="values-heading"
                        class="about-panel bg-primary-deep text-canvas">
                        <div class="public-container py-24 lg:py-28">
                            <x-public.section-heading
                                data-about-reveal
                                eyebrow="{{ data_get(
                        $sections,
                        'values_eyebrow',
                        'What Defines Us',
                    ) }}"
                                title="{{ data_get(
                        $sections,
                        'values_title',
                        'Our values. In everything we do.',
                    ) }}"
                                align="left"
                                theme="dark" />

                            <div
                                class="mt-12 grid gap-5 md:grid-cols-2
                        xl:grid-cols-4">
                                @foreach ($values as $value)
                                @php
                                $valueImage = $valueImages->get(
                                $loop->index,
                                );
                                @endphp

                                <article
                                    data-about-reveal
                                    class="about-value-card overflow-hidden
                                rounded-card bg-surface text-ink shadow-panel">
                                    <div class="p-7">
                                        <div
                                            class="flex items-start justify-between gap-4">
                                            <span
                                                class="flex size-10 items-center
                                            justify-center rounded-full
                                            border border-primary/15
                                            bg-surface-soft text-primary">
                                                {{ str_pad(
                                            (string) ($loop->index + 1),
                                            2,
                                            '0',
                                            STR_PAD_LEFT,
                                        ) }}
                                            </span>

                                            <span
                                                class="font-display text-2xl
                                            italic text-coral">
                                                {{ data_get(
                                            $value,
                                            'number',
                                            str_pad(
                                                (string) ($loop->index + 1),
                                                2,
                                                '0',
                                                STR_PAD_LEFT,
                                            ),
                                        ) }}
                                            </span>
                                        </div>

                                        <h3
                                            class="mt-7 font-display text-2xl
                                        leading-tight text-ink">
                                            {{ data_get($value, 'title') }}
                                        </h3>

                                        <p class="mt-4 text-sm leading-7 text-muted">
                                            {{ data_get($value, 'description') }}
                                        </p>
                                    </div>

                                    <div
                                        data-about-image-reveal
                                        class="about-value-card__image overflow-hidden
                                    bg-primary-soft">
                                        @if ($valueImage?->image_url)
                                        <x-public.responsive-image
                                            :image="$valueImage"
                                            :alt="$valueImage->alt_text ?: $valueImage->title ?: data_get($value, 'title', 'Coast and Cay restaurant value')"
                                            variant="card"
                                            sizes="(min-width: 1280px) 25vw, (min-width: 768px) 50vw, 100vw"
                                            width="900"
                                            height="700"
                                            img-class="size-full object-cover" />
                                        @endif
                                    </div>
                                </article>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    {{-- Heritage and ingredients --}}
                    <section
                        id="heritage"
                        data-about-panel
                        data-about-section="heritage"
                        aria-labelledby="heritage-heading"
                        class="about-panel isolate bg-primary-deep text-canvas">
                        @if ($heritageImage?->image_url)
                        <div
                            data-about-parallax
                            class="absolute inset-0 -z-30 overflow-hidden">
                            <x-public.responsive-image
                                :image="$heritageImage"
                                :alt="$heritageImage->alt_text ?: $heritageImage->title ?: 'Caribbean ingredients prepared at Coast and Cay'"
                                variant="hero"
                                sizes="100vw"
                                width="2000"
                                height="1400"
                                img-class="size-full object-cover" />
                        </div>
                        @endif

                        <div
                            class="absolute inset-0 -z-20 bg-primary-deep/68"
                            aria-hidden="true">
                        </div>

                        <div
                            class="absolute inset-0 -z-10 bg-gradient-to-r
                    from-black/55 via-transparent to-black/50"
                            aria-hidden="true">
                        </div>

                        <div class="public-container py-24 lg:py-28">
                            <div
                                class="grid items-center gap-12
                        lg:grid-cols-[1fr_0.72fr]">
                                <article
                                    data-about-reveal
                                    class="max-w-2xl rounded-panel border
                            border-canvas/12 bg-primary-deep/88 p-8
                            shadow-elevated backdrop-blur-md sm:p-10">
                                    <p
                                        class="text-xs font-semibold uppercase
                                tracking-[0.3em] text-sun">
                                        {{ data_get(
                                $sections,
                                'heritage.eyebrow',
                                'Our Heritage',
                            ) }}
                                    </p>

                                    <h2
                                        id="heritage-heading"
                                        class="mt-5 font-display text-4xl leading-[1.05]
                                text-canvas sm:text-5xl">
                                        {{ data_get(
                                $sections,
                                'heritage.title',
                                'Honoring where we come from. Creating what is next.',
                            ) }}
                                    </h2>

                                    <p class="mt-7 text-base leading-8 text-canvas/72">
                                        {{ data_get(
                                $sections,
                                'heritage.description',
                                'Our inspiration comes from sun-soaked islands, family kitchens, and the farmers and producers who share our values.',
                            ) }}
                                    </p>
                                </article>

                                <div class="about-heritage-list">
                                    @foreach ($heritageItems as $item)
                                    <article
                                        data-about-reveal
                                        class="about-heritage-item">
                                        <span
                                            class="flex size-11 shrink-0 items-center
                                        justify-center rounded-full border
                                        border-sun/45 font-display text-lg
                                        text-sun">
                                            {{ str_pad(
                                        (string) ($loop->index + 1),
                                        2,
                                        '0',
                                        STR_PAD_LEFT,
                                    ) }}
                                        </span>

                                        <div>
                                            <h3
                                                class="font-display text-2xl
                                            text-canvas">
                                                {{ data_get($item, 'title') }}
                                            </h3>

                                            <p
                                                class="mt-2 text-sm leading-7
                                            text-canvas/65">
                                                {{ data_get(
                                            $item,
                                            'description',
                                        ) }}
                                            </p>
                                        </div>
                                    </article>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Hospitality and guest experience --}}
                    <section
                        id="experience"
                        data-about-panel
                        data-about-section="experience"
                        aria-labelledby="experience-heading"
                        class="about-panel bg-canvas">
                        <div
                            class="public-container grid items-center gap-14
                    py-24 lg:grid-cols-[0.76fr_1.24fr] lg:py-28">
                            <div class="max-w-xl">
                                <p
                                    data-about-reveal
                                    class="public-eyebrow">
                                    {{ data_get(
                            $sections,
                            'experience.eyebrow',
                            'The Coast & Cay Experience',
                        ) }}
                                </p>

                                <h2
                                    id="experience-heading"
                                    data-about-reveal
                                    class="mt-5 font-display text-4xl leading-[1.05]
                            text-ink sm:text-5xl lg:text-6xl">
                                    {{ data_get(
                            $sections,
                            'experience.title',
                            'Why guests keep coming back.',
                        ) }}
                                </h2>

                                <p
                                    data-about-reveal
                                    class="mt-7 text-base leading-8 text-muted">
                                    {{ data_get(
                            $sections,
                            'experience.description',
                            'It is more than the food. It is how we make you feel. Every visit is designed to be effortless, memorable, and filled with good energy.',
                        ) }}
                                </p>

                                <ul class="mt-8 space-y-4">
                                    @foreach ($experienceItems as $item)
                                    <li
                                        data-about-reveal
                                        class="flex items-start gap-4 text-sm
                                    leading-7 text-muted">
                                        <span
                                            class="mt-1.5 flex size-5 shrink-0
                                        items-center justify-center
                                        rounded-full bg-coral/12
                                        text-[0.6rem] text-coral">
                                            ✓
                                        </span>

                                        <span>
                                            {{ data_get($item, 'text') }}
                                        </span>
                                    </li>
                                    @endforeach
                                </ul>

                                <a
                                    data-about-reveal
                                    href="{{ route('contact.create') }}"
                                    class="public-button-primary mt-9">
                                    Plan Your Visit
                                </a>
                            </div>

                            <div class="about-mosaic">
                                @foreach ($experienceImages as $image)
                                <figure
                                    data-about-image-reveal
                                    class="overflow-hidden rounded-card
                                bg-surface-soft shadow-card">
                                    <x-public.responsive-image
                                        :image="$image"
                                        :alt="$image->alt_text ?: $image->title ?: 'The Coast and Cay guest experience'"
                                        variant="card"
                                        sizes="(min-width: 1024px) 30vw, 50vw"
                                        width="1000"
                                        height="800"
                                        img-class="size-full object-cover" />
                                </figure>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    {{-- Final full-screen invitation --}}
                    <section
                        id="invitation"
                        data-about-panel
                        data-about-section="invitation"
                        aria-labelledby="invitation-heading"
                        class="about-panel isolate bg-primary-deep text-canvas">
                        @if ($closingImage?->image_url)
                        <div
                            data-about-parallax
                            class="absolute inset-0 -z-30 overflow-hidden">
                            <x-public.responsive-image
                                :image="$closingImage"
                                :alt="$closingImage->alt_text ?: $closingImage->title ?: 'Signature Coast and Cay dish'"
                                variant="hero"
                                sizes="100vw"
                                width="2000"
                                height="1300"
                                img-class="size-full object-cover" />
                        </div>
                        @endif

                        <div
                            class="absolute inset-0 -z-20 bg-primary-deep/74"
                            aria-hidden="true">
                        </div>

                        <div
                            class="absolute inset-0 -z-10 bg-gradient-to-r
                    from-primary-deep via-primary-deep/50 to-transparent"
                            aria-hidden="true">
                        </div>

                        <div class="public-container py-28">
                            <div class="max-w-3xl">
                                <p
                                    data-about-reveal
                                    class="text-xs font-semibold uppercase
                            tracking-[0.32em] text-sun">
                                    {{ data_get(
                            $sections,
                            'closing.eyebrow',
                            'You Are Invited',
                        ) }}
                                </p>

                                <h2
                                    id="invitation-heading"
                                    data-about-reveal
                                    class="mt-5 font-display text-5xl leading-[0.98]
                            text-canvas sm:text-6xl lg:text-7xl">
                                    {{ data_get(
                            $sections,
                            'closing.title',
                            'Good food. Good people. Great memories.',
                        ) }}
                                </h2>

                                <p
                                    data-about-reveal
                                    class="mt-7 max-w-xl text-base leading-8
                            text-canvas/72 sm:text-lg">
                                    {{ data_get(
                            $sections,
                            'closing.description',
                            'Come for the flavor. Stay for the feeling.',
                        ) }}
                                </p>

                                <div
                                    data-about-reveal
                                    class="mt-9 flex flex-wrap gap-3">
                                    <a
                                        href="{{ route('menu') }}"
                                        class="public-button-primary">
                                        Explore the Menu
                                    </a>

                                    <a
                                        href="{{ route('gallery') }}"
                                        class="public-button-secondary text-canvas">
                                        View Gallery
                                    </a>

                                    <a
                                        href="{{ route('contact.create') }}"
                                        class="public-button-secondary text-canvas">
                                        Contact Us
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
</x-layouts.public>