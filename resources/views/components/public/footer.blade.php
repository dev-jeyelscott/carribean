@php
use Illuminate\Support\Facades\Route;

$restaurantName = $settings['restaurant_name']
?? config('app.name');

$tagline = $settings['tagline']
?? 'Caribbean warmth, California ease.';

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

$legalLinks = array_filter([
'FAQ' => Route::has('faq') ? route('faq') : null,
'Privacy Policy' => Route::has('privacy-policy')
? route('privacy-policy')
: null,
'Terms and Conditions' => Route::has('terms-and-conditions')
? route('terms-and-conditions')
: null,
'Refund and Cancellation' => Route::has('refund-and-cancellation-policy')
? route('refund-and-cancellation-policy')
: null,
'Delivery and Pickup' => Route::has('delivery-and-pickup-policy')
? route('delivery-and-pickup-policy')
: null,
]);
@endphp

<footer class="bg-brand-palm-dark text-brand-cream">
    <div class="public-container grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-4">
        <div class="lg:col-span-1">
            <a
                href="{{ route('home') }}"
                class="font-display text-3xl tracking-[0.05em]">
                {{ $restaurantName }}
            </a>

            <p class="mt-5 max-w-sm text-sm leading-7 text-brand-cream/70">
                {{ $tagline }}
            </p>

            @if ($socialLinks !== [])
            <div class="mt-7 flex flex-wrap gap-4 text-xs font-semibold uppercase tracking-[0.16em]">
                @foreach ($socialLinks as $label => $url)
                <a
                    href="{{ $url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-brand-cream/70 transition hover:text-brand-coral">
                    {{ $label }}
                </a>
                @endforeach
            </div>
            @endif
        </div>

        <div>
            <h2 class="text-xs font-semibold uppercase tracking-[0.22em] text-brand-coral">
                Explore
            </h2>

            <nav class="mt-5 flex flex-col gap-3 text-sm text-brand-cream/70">
                <a href="{{ route('home') }}" class="hover:text-white">Home</a>
                <a href="{{ route('menu') }}" class="hover:text-white">Menu</a>
                <a href="{{ route('about') }}" class="hover:text-white">About</a>
                <a href="{{ route('gallery') }}" class="hover:text-white">Gallery</a>
                <a href="{{ route('contact.create') }}" class="hover:text-white">Contact</a>
            </nav>
        </div>

        <div>
            <h2 class="text-xs font-semibold uppercase tracking-[0.22em] text-brand-coral">
                Visit
            </h2>

            <div class="mt-5 space-y-3 text-sm leading-7 text-brand-cream/70">
                @if ($address)
                <p>{{ $address }}</p>
                @endif

                @if ($openingHours)
                <p>{{ $openingHours }}</p>
                @endif

                @if ($phoneTarget)
                <p>
                    <a href="tel:{{ $phoneTarget }}" class="hover:text-white">
                        {{ $phone }}
                    </a>
                </p>
                @endif

                @if ($email)
                <p>
                    <a href="mailto:{{ $email }}" class="hover:text-white">
                        {{ $email }}
                    </a>
                </p>
                @endif

                @if ($mapLink)
                <p>
                    <a
                        href="{{ $mapLink }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold text-brand-coral hover:text-white">
                        Open location map
                    </a>
                </p>
                @endif
            </div>
        </div>

        <div>
            <h2 class="text-xs font-semibold uppercase tracking-[0.22em] text-brand-coral">
                Plan Your Visit
            </h2>

            <div class="mt-5 flex flex-col gap-3">
                <a
                    href="{{ route('reservation-request.create') }}"
                    class="public-button-secondary text-brand-cream">
                    Reserve a Table
                </a>

                <a href="{{ $orderUrl }}" class="public-button-primary">
                    Order Online
                </a>
            </div>
        </div>
    </div>

    @if ($legalLinks !== [])
    <div class="border-t border-white/10">
        <nav class="public-container flex flex-wrap justify-center gap-x-6 gap-y-3 py-5 text-xs text-brand-cream/55">
            @foreach ($legalLinks as $label => $url)
            <a href="{{ $url }}" class="transition hover:text-white">
                {{ $label }}
            </a>
            @endforeach
        </nav>
    </div>
    @endif

    <div class="border-t border-white/10">
        <div class="public-container py-5 text-center text-xs text-brand-cream/50">
            &copy; {{ now()->year }} {{ $restaurantName }}. All rights reserved.
        </div>
    </div>
</footer>