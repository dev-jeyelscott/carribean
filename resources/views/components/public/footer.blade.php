@php
    use Illuminate\Support\Facades\Route;

    $restaurantName = $settings['restaurant_name']
        ?? config('app.name');

    $tagline = $settings['tagline']
        ?? 'Caribbean warmth, California ease.';

    $footerDescription = $settings['footer_description']
        ?? 'A warm gathering place for vibrant Caribbean food, relaxed hospitality, and memorable meals shared together.';

    $phone = $settings['phone'] ?? null;
    $email = $settings['email'] ?? null;
    $address = $settings['address'] ?? null;
    $openingHours = $settings['opening_hours'] ?? null;
    $mapLink = $settings['map_link'] ?? null;

    $phoneDigits = is_string($phone)
        ? preg_replace('/\D+/', '', $phone)
        : null;

    $phoneTarget = is_string($phone)
        && is_string($phoneDigits)
        && $phoneDigits !== ''
            ? (str_starts_with(ltrim($phone), '+') ? '+' : '').$phoneDigits
            : null;

    $orderUrl = Route::has('cart.index')
        ? route('cart.index')
        : route('menu');

    $socialLinks = array_filter([
        'Instagram' => $settings['instagram_url'] ?? null,
        'Facebook' => $settings['facebook_url'] ?? null,
        'TikTok' => $settings['tiktok_url'] ?? null,
    ]);

    $exploreLinks = [
        'Home' => route('home'),
        'Menu' => route('menu'),
        'About' => route('about'),
        'Gallery' => route('gallery'),
        'Contact' => route('contact.create'),
    ];

    if (
        ($hasPublishedBlogPosts ?? false)
        && Route::has('blog.index')
    ) {
        $exploreLinks['Journal'] = route('blog.index');
    }

    if (
        ($hasVisibleFaqs ?? false)
        && Route::has('faq')
    ) {
        $exploreLinks['FAQ'] = route('faq');
    }

    $publishedPageSlugs = $publishedPageSlugs ?? [];

    $legalLinks = array_filter([
        'Privacy Policy' => in_array(
            'privacy-policy',
            $publishedPageSlugs,
            true,
        ) && Route::has('privacy-policy')
            ? route('privacy-policy')
            : null,

        'Terms and Conditions' => in_array(
            'terms-and-conditions',
            $publishedPageSlugs,
            true,
        ) && Route::has('terms-and-conditions')
            ? route('terms-and-conditions')
            : null,

        'Refund and Cancellation' => in_array(
            'refund-and-cancellation-policy',
            $publishedPageSlugs,
            true,
        ) && Route::has('refund-and-cancellation-policy')
            ? route('refund-and-cancellation-policy')
            : null,

        'Delivery and Pickup' => in_array(
            'delivery-and-pickup-policy',
            $publishedPageSlugs,
            true,
        ) && Route::has('delivery-and-pickup-policy')
            ? route('delivery-and-pickup-policy')
            : null,
    ]);
@endphp
<footer
    class="relative isolate overflow-hidden bg-brand-palm-dark
        text-brand-cream">
    {{-- Decorative brand-token accents. --}}
    <div
        class="pointer-events-none absolute -right-32 top-10 -z-10
            size-80 rounded-full bg-brand-ocean/15 blur-3xl"
        aria-hidden="true"></div>

    <div
        class="pointer-events-none absolute -bottom-40 -left-32 -z-10
            size-96 rounded-full bg-brand-coral/10 blur-3xl"
        aria-hidden="true"></div>

    {{-- Primary footer content. --}}
    <div
        class="public-container grid gap-12 py-16
            md:grid-cols-2 lg:grid-cols-[1.25fr_0.75fr_1fr_1fr]
            lg:gap-10 lg:py-20">
        <section aria-labelledby="footer-brand-heading">
            <a
                href="{{ route('home') }}"
                id="footer-brand-heading"
                class="inline-flex items-center gap-3">
                <span
                    class="flex size-12 items-center justify-center
                        rounded-full bg-brand-coral font-display text-xl
                        text-white">
                    {{ str($restaurantName)->substr(0, 1)->upper() }}
                </span>

                <span>
                    <span
                        class="block font-display text-3xl
                            tracking-[0.04em]">
                        {{ $restaurantName }}
                    </span>

                    <span
                        class="mt-1 block text-[0.6rem] font-semibold
                            uppercase tracking-[0.2em] text-brand-sun">
                        {{ $tagline }}
                    </span>
                </span>
            </a>

            <p
                class="mt-6 max-w-sm text-sm leading-7
                    text-brand-cream/65">
                {{ $footerDescription }}
            </p>

            @if ($socialLinks !== [])
                <div class="mt-7 flex flex-wrap gap-2">
                    @foreach ($socialLinks as $label => $url)
                        <a
                            href="{{ $url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex min-h-10 items-center
                                justify-center rounded-full border
                                border-brand-cream/15 px-4 text-[0.65rem]
                                font-semibold uppercase tracking-[0.15em]
                                text-brand-cream/70 transition
                                duration-300 ease-island
                                hover:border-brand-coral
                                hover:bg-brand-coral hover:text-white">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section aria-labelledby="footer-explore-heading">
            <h2
                id="footer-explore-heading"
                class="text-xs font-semibold uppercase
                    tracking-[0.22em] text-brand-coral">
                Explore
            </h2>

            <nav
                class="mt-6 flex flex-col gap-3.5 text-sm
                    text-brand-cream/65"
                aria-label="Footer navigation">
                @foreach ($exploreLinks as $label => $url)
                    <a
                        href="{{ $url }}"
                        class="group inline-flex items-center gap-2
                            transition duration-300
                            hover:text-brand-sun">
                        <span
                            class="h-px w-0 bg-brand-coral transition-all
                                duration-300 group-hover:w-4"
                            aria-hidden="true"></span>

                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </section>

        <section aria-labelledby="footer-visit-heading">
            <h2
                id="footer-visit-heading"
                class="text-xs font-semibold uppercase
                    tracking-[0.22em] text-brand-coral">
                Visit
            </h2>

            <div
                class="mt-6 space-y-5 text-sm leading-7
                    text-brand-cream/65">
                @if ($address)
                    <div>
                        <p
                            class="text-[0.62rem] font-semibold uppercase
                                tracking-[0.17em] text-brand-sun">
                            Address
                        </p>

                        @if ($mapLink)
                            <a
                                href="{{ $mapLink }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-1 block transition
                                    hover:text-white">
                                {{ $address }}
                            </a>
                        @else
                            <p class="mt-1">
                                {{ $address }}
                            </p>
                        @endif
                    </div>
                @endif

                @if ($phoneTarget)
                    <div>
                        <p
                            class="text-[0.62rem] font-semibold uppercase
                                tracking-[0.17em] text-brand-sun">
                            Phone
                        </p>

                        <a
                            href="tel:{{ $phoneTarget }}"
                            class="mt-1 block transition hover:text-white">
                            {{ $phone }}
                        </a>
                    </div>
                @endif

                @if ($email)
                    <div>
                        <p
                            class="text-[0.62rem] font-semibold uppercase
                                tracking-[0.17em] text-brand-sun">
                            Email
                        </p>

                        <a
                            href="mailto:{{ $email }}"
                            class="mt-1 block break-words transition
                                hover:text-white">
                            {{ $email }}
                        </a>
                    </div>
                @endif

                @if ($mapLink)
                    <a
                        href="{{ $mapLink }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 font-semibold
                            text-brand-coral transition
                            hover:text-brand-sun">
                        Get directions

                        <span aria-hidden="true">&rarr;</span>
                    </a>
                @endif
            </div>
        </section>

        <section aria-labelledby="footer-plan-heading">
            <h2
                id="footer-plan-heading"
                class="text-xs font-semibold uppercase
                    tracking-[0.22em] text-brand-coral">
                Plan Your Visit
            </h2>

            @if ($openingHours)
                <div
                    class="mt-6 rounded-island border
                        border-brand-cream/10 bg-brand-palm/45 p-5">
                    <p
                        class="text-[0.62rem] font-semibold uppercase
                            tracking-[0.17em] text-brand-sun">
                        Opening Hours
                    </p>

                    <p
                        class="mt-3 whitespace-pre-line text-sm leading-7
                            text-brand-cream/70">
                        {{ $openingHours }}
                    </p>
                </div>
            @endif

            <p class="mt-6 text-sm leading-7 text-brand-cream/65">
                Join us at the table or order your favorites for pickup
                or local delivery.
            </p>

            <div class="mt-6 flex flex-col gap-3">
                <a
                    href="{{ route('reservation-request.create') }}"
                    class="public-button-secondary text-brand-cream">
                    Reserve a Table
                </a>

                <a
                    href="{{ $orderUrl }}"
                    class="public-button-primary">
                    Order Online
                </a>
            </div>
        </section>
    </div>

    {{-- Legal navigation and copyright. --}}
    <div class="border-t border-brand-cream/10">
        <div
            class="public-container flex flex-col gap-4 py-6
                text-xs text-brand-cream/50 md:flex-row
                md:items-center md:justify-between">
            <p>
                &copy; {{ now()->year }} {{ $restaurantName }}.
                All rights reserved.
            </p>

            @if ($legalLinks !== [])
                <nav
                    class="flex flex-wrap gap-x-5 gap-y-2"
                    aria-label="Legal navigation">
                    @foreach ($legalLinks as $label => $url)
                        <a
                            href="{{ $url }}"
                            class="transition duration-300
                                hover:text-brand-sun">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>
</footer>
