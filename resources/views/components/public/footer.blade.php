@php
use Illuminate\Support\Facades\Route;

$restaurantName = $settings['restaurant_name']
?? config('app.name');

$tagline = $settings['tagline']
?? 'Caribbean warmth, California ease.';

$footerDescription = $settings['footer_description']
?? 'Bold Caribbean flavors, relaxed hospitality, and memorable meals shared together.';

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

$socialLinks = array_filter([
'Instagram' => $settings['instagram_url'] ?? null,
'Facebook' => $settings['facebook_url'] ?? null,
'TikTok' => $settings['tiktok_url'] ?? null,
]);

$quickLinks = [
'Home' => route('home'),
'Menu' => route('menu'),
'About Us' => route('about'),
'Gallery' => route('gallery'),
'Contact' => route('contact.create'),
'Order Online' => route('menu'),
];

if (
($hasPublishedBlogPosts ?? false)
&& Route::has('blog.index')
) {
$quickLinks['Journal'] = route('blog.index');
}

if (
($hasVisibleFaqs ?? false)
&& Route::has('faq')
) {
$quickLinks['FAQ'] = route('faq');
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

'Terms of Service' => in_array(
'terms-and-conditions',
$publishedPageSlugs,
true,
) && Route::has('terms-and-conditions')
? route('terms-and-conditions')
: null,

'Refund Policy' => in_array(
'refund-and-cancellation-policy',
$publishedPageSlugs,
true,
) && Route::has('refund-and-cancellation-policy')
? route('refund-and-cancellation-policy')
: null,

'Delivery Policy' => in_array(
'delivery-and-pickup-policy',
$publishedPageSlugs,
true,
) && Route::has('delivery-and-pickup-policy')
? route('delivery-and-pickup-policy')
: null,
]);
@endphp

<footer
    class="public-paper-texture border-t border-line bg-canvas text-ink">
    <div
        class="public-container grid gap-12 py-14
            sm:grid-cols-2 lg:grid-cols-[1.25fr_0.75fr_1fr_1fr]
            lg:gap-10 lg:py-16">
        <section aria-labelledby="footer-brand-heading">
            <a
                href="{{ route('home') }}"
                id="footer-brand-heading"
                class="inline-flex items-center gap-3">
                <span
                    class="flex size-12 items-center justify-center
                        rounded-full border border-primary/15 bg-surface
                        text-primary shadow-card">
                    <svg
                        class="size-7"
                        viewBox="0 0 32 32"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            d="M16 28V12M16 12c-1-5-6-7-11-5 4 1 7 3 9 6M16 12c2-5 7-7 12-4-5 0-8 2-11 6M16 12c-4-3-8-3-12 0 5-1 8 1 11 4M17 14c4-3 8-2 11 1-5-1-8 0-11 3" />
                    </svg>
                </span>

                <span>
                    <span
                        class="block font-display text-2xl text-primary">
                        {{ $restaurantName }}
                    </span>

                    <span
                        class="mt-1 block text-[0.58rem] font-semibold
                            uppercase tracking-[0.18em] text-coral">
                        {{ $tagline }}
                    </span>
                </span>
            </a>

            <p class="mt-5 max-w-sm text-sm leading-7 text-muted">
                {{ $footerDescription }}
            </p>

            @if ($socialLinks !== [])
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($socialLinks as $label => $url)
                <a
                    href="{{ $url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex min-h-9 items-center
                                justify-center rounded-full border border-line
                                bg-surface px-4 text-xs font-semibold
                                text-primary transition hover:border-coral/40
                                hover:text-coral">
                    {{ $label }}
                </a>
                @endforeach
            </div>
            @endif
        </section>

        <section aria-labelledby="footer-links-heading">
            <h2
                id="footer-links-heading"
                class="text-sm font-semibold text-ink">
                Quick Links
            </h2>

            <nav
                class="mt-5 flex flex-col gap-2.5 text-sm text-muted"
                aria-label="Footer navigation">
                @foreach ($quickLinks as $label => $url)
                <a
                    href="{{ $url }}"
                    class="transition hover:text-coral">
                    {{ $label }}
                </a>
                @endforeach
            </nav>
        </section>

        <section aria-labelledby="footer-contact-heading">
            <h2
                id="footer-contact-heading"
                class="text-sm font-semibold text-ink">
                Contact Us
            </h2>

            <div class="mt-5 space-y-4 text-sm leading-6 text-muted">
                @if ($phoneTarget)
                <a
                    href="tel:{{ $phoneTarget }}"
                    class="block transition hover:text-coral">
                    {{ $phone }}
                </a>
                @endif

                @if ($email)
                <a
                    href="mailto:{{ $email }}"
                    class="block break-words transition
                            hover:text-coral">
                    {{ $email }}
                </a>
                @endif

                @if ($address)
                @if ($mapLink)
                <a
                    href="{{ $mapLink }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block transition hover:text-coral">
                    {{ $address }}
                </a>
                @else
                <p>{{ $address }}</p>
                @endif
                @endif

                @if ($mapLink)
                <a
                    href="{{ $mapLink }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 font-semibold
                            text-primary transition hover:text-coral">
                    Get directions

                    <span aria-hidden="true">&rarr;</span>
                </a>
                @endif
            </div>
        </section>

        <section aria-labelledby="footer-hours-heading">
            <h2
                id="footer-hours-heading"
                class="text-sm font-semibold text-ink">
                Opening Hours
            </h2>

            @if ($openingHours)
            <p
                class="mt-5 whitespace-pre-line text-sm leading-7
                        text-muted">
                {{ $openingHours }}
            </p>
            @else
            <p class="mt-5 text-sm leading-7 text-muted">
                Opening hours will be published soon.
            </p>
            @endif

            <p
                class="mt-5 font-display text-xl italic leading-6
                    text-ocean">
                We can’t wait to welcome you.
            </p>
        </section>
    </div>

    <div class="border-t border-line">
        <div
            class="public-container flex flex-col gap-4 py-5 text-xs
                text-muted md:flex-row md:items-center
                md:justify-between">
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
                    class="transition hover:text-coral">
                    {{ $label }}
                </a>
                @endforeach
            </nav>
            @endif
        </div>
    </div>
</footer>