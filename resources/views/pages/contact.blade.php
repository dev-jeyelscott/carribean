<x-layouts.public
    :title="$page?->meta_title ?: 'Contact | Coast & Cay'"
    :description="$page?->meta_description ?: 'Contact Coast & Cay for restaurant information, online-order support, directions, accessibility, and general questions.'"
    :header-overlay="true">
    @php
    /*
    * Resolve editable restaurant details with development-safe fallbacks.
    */
    $restaurantPhone = $settings['phone'] ?? '(310) 438-2673';
    $restaurantEmail = $settings['email'] ?? 'hello@coastandcay.com';
    $restaurantAddress = $settings['address']
    ?? '2400 Ocean Avenue, Santa Monica, CA 90405';
    $openingHours = $settings['opening_hours']
    ?? "Monday – Thursday: 11AM – 10PM\nFriday – Saturday: 11AM – 11PM\nSunday: 11AM – 10PM";
    @endphp

    <div
        data-contact-page
        data-home-motion
        class="contact-page">

        {{-- Full-screen welcome hero --}}
        <section
            id="contact-hero"
            data-public-hero
            data-gsap="section"
            aria-labelledby="contact-hero-heading"
            class="contact-panel bg-brand-cream">
            <div
                class="contact-organic-glow -left-56 top-16"
                aria-hidden="true">
            </div>

            <div class="contact-panel__content">
                <div class="public-container">
                    <div
                        class="grid items-center gap-10 lg:grid-cols-[0.88fr_1.12fr]
                            lg:gap-14 xl:gap-20">
                        <div
                            data-gsap="hero-content"
                            class="relative z-10 max-w-2xl">
                            <p
                                data-gsap-reveal
                                class="public-eyebrow text-brand-coral">
                                We would love to hear from you
                            </p>

                            <h1
                                id="contact-hero-heading"
                                data-gsap-reveal
                                class="mt-5 font-display text-5xl leading-[0.94]
                                    text-brand-white sm:text-6xl
                                    lg:text-7xl xl:text-8xl">
                                {{ $page?->title ?: 'Let’s Plan Your Visit' }}
                            </h1>

                            <p
                                data-gsap-reveal
                                class="mt-7 max-w-xl text-base leading-8
                                    text-brand-muted sm:text-lg">
                                {{ $page?->excerpt ?: 'Whether you are joining us for a relaxed dinner, planning a meaningful celebration, or simply have a question, our team is ready to welcome you with island hospitality.' }}
                            </p>

                            <div
                                data-gsap-reveal
                                class="mt-8 flex flex-col gap-3 sm:flex-row">
                                <a
                                    href="{{ route('menu') }}"
                                    class="public-button-primary justify-center">
                                    Explore the Menu
                                </a>

                                <a
                                    href="#contact-message"
                                    class="public-button-secondary justify-center">
                                    Send an Inquiry
                                </a>
                            </div>
                        </div>

                        <div
                            data-gsap="hero-image"
                            class="contact-hero-media relative min-h-[23rem]
                                sm:min-h-[31rem] lg:min-h-[min(67svh,46rem)]">
                            @if ($heroImage?->image_url)
                            <x-public.responsive-image
                                :image="$heroImage"
                                :alt="$heroImage->alt_text ?: $heroImage->title ?: 'Warm Coast and Cay restaurant terrace'"
                                variant="hero"
                                sizes="(min-width: 1024px) 54vw, 100vw"
                                width="1440"
                                height="1200"
                                loading="eager"
                                fetchpriority="high"
                                img-class="absolute inset-0 size-full object-cover" />
                            @else
                            <div
                                data-contact-hero-fallback
                                class="absolute inset-0
                                        bg-[radial-gradient(circle_at_72%_25%,rgba(242,199,107,0.28),transparent_25%),linear-gradient(135deg,#5a8f96,#0c342b_68%)]"
                                aria-hidden="true">
                            </div>
                            @endif
                        </div>
                    </div>

                    <div
                        data-gsap-reveal
                        class="relative z-20 mt-8 grid gap-3 sm:grid-cols-2
                            lg:-mt-9 lg:grid-cols-4 lg:px-4">
                        <a
                            href="tel:{{ preg_replace('/[^0-9+]/', '', $restaurantPhone) }}"
                            class="contact-quick-card transition
                                hover:-translate-y-1 hover:shadow-island">
                            <span class="contact-quick-card__icon">
                                <svg
                                    aria-hidden="true"
                                    viewBox="0 0 24 24"
                                    class="size-5 fill-none stroke-current"
                                    stroke-width="1.8">
                                    <path
                                        d="M7.2 3.5 9.8 8l-2.1 2.1a15.3 15.3 0 0 0 6.2 6.2l2.1-2.1 4.5 2.6v2.5a1.7 1.7 0 0 1-1.7 1.7A15.8 15.8 0 0 1 3 5.2a1.7 1.7 0 0 1 1.7-1.7h2.5Z" />
                                </svg>
                            </span>

                            <span class="min-w-0">
                                <span
                                    class="block text-[0.65rem] font-semibold
                                        uppercase tracking-[0.16em]
                                        text-brand-muted">
                                    Call us
                                </span>
                                <span
                                    class="mt-1 block truncate text-sm
                                        font-semibold text-brand-forest">
                                    {{ $restaurantPhone }}
                                </span>
                            </span>
                        </a>

                        <a
                            href="mailto:{{ $restaurantEmail }}"
                            class="contact-quick-card transition
                                hover:-translate-y-1 hover:shadow-island">
                            <span
                                class="contact-quick-card__icon
                                    !bg-brand-coral">
                                <svg
                                    aria-hidden="true"
                                    viewBox="0 0 24 24"
                                    class="size-5 fill-none stroke-current"
                                    stroke-width="1.8">
                                    <rect
                                        x="3"
                                        y="5"
                                        width="18"
                                        height="14"
                                        rx="2" />
                                    <path d="m4 7 8 6 8-6" />
                                </svg>
                            </span>

                            <span class="min-w-0">
                                <span
                                    class="block text-[0.65rem] font-semibold
                                        uppercase tracking-[0.16em]
                                        text-brand-muted">
                                    Email us
                                </span>
                                <span
                                    class="mt-1 block truncate text-sm
                                        font-semibold text-brand-forest">
                                    {{ $restaurantEmail }}
                                </span>
                            </span>
                        </a>

                        <a
                            href="#contact-visit"
                            class="contact-quick-card transition
                                hover:-translate-y-1 hover:shadow-island">
                            <span
                                class="contact-quick-card__icon
                                    !bg-brand-palm">
                                <svg
                                    aria-hidden="true"
                                    viewBox="0 0 24 24"
                                    class="size-5 fill-none stroke-current"
                                    stroke-width="1.8">
                                    <path
                                        d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>
                            </span>

                            <span class="min-w-0">
                                <span
                                    class="block text-[0.65rem] font-semibold
                                        uppercase tracking-[0.16em]
                                        text-brand-muted">
                                    Find us
                                </span>
                                <span
                                    class="mt-1 block truncate text-sm
                                        font-semibold text-brand-forest">
                                    Santa Monica, California
                                </span>
                            </span>
                        </a>

                        <div class="contact-quick-card">
                            <span
                                class="contact-quick-card__icon
                                    !bg-brand-ocean">
                                <svg
                                    aria-hidden="true"
                                    viewBox="0 0 24 24"
                                    class="size-5 fill-none stroke-current"
                                    stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                </svg>
                            </span>

                            <span class="min-w-0">
                                <span
                                    class="block text-[0.65rem] font-semibold
                                        uppercase tracking-[0.16em]
                                        text-brand-muted">
                                    Opening hours
                                </span>
                                <span
                                    class="mt-1 block truncate text-sm
                                        font-semibold text-brand-forest">
                                    Open daily
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Full-screen contact form --}}
        <section
            id="contact-message"
            data-gsap="section"
            aria-labelledby="contact-message-heading"
            class="contact-panel public-island-pattern bg-brand-sand">
            <div
                class="contact-organic-glow -right-48 bottom-0
                    !bg-brand-ocean/10"
                aria-hidden="true">
            </div>

            <div class="contact-panel__content">
                <div
                    class="public-container grid items-center gap-8
                        lg:grid-cols-[1.12fr_0.88fr] lg:gap-12">
                    <div
                        data-gsap="panel"
                        class="contact-surface p-6 sm:p-8 lg:p-9">
                        <p
                            data-gsap-reveal
                            class="public-eyebrow">
                            Send us a message
                        </p>

                        <h2
                            id="contact-message-heading"
                            data-gsap-reveal
                            class="mt-3 font-display text-4xl
                                text-brand-forest sm:text-5xl">
                            How can we help?
                        </h2>

                        <p
                            data-gsap-reveal
                            class="mt-3 max-w-2xl text-sm leading-7
                                text-brand-muted">
                            Our team usually responds within one business day.
                            Include your order number when requesting help with
                            an existing online order.
                        </p>

                        @if (session('success'))
                        <x-public.alert type="success" class="mt-6">
                            {{ session('success') }}
                        </x-public.alert>
                        @endif

                        <x-public.contact-form />
                    </div>

                    <aside data-gsap="panel">
                        <p
                            data-gsap-reveal
                            class="public-eyebrow text-brand-coral">
                            We are here for you
                        </p>

                        <h2
                            data-gsap-reveal
                            class="mt-4 max-w-md font-display text-4xl
                                leading-tight text-brand-forest sm:text-5xl">
                            Helpful answers, genuine hospitality.
                        </h2>

                        <div class="mt-7 grid gap-3">
                            <a
                                data-gsap-reveal
                                href="tel:{{ preg_replace('/[^0-9+]/', '', $restaurantPhone) }}"
                                class="contact-detail-card transition
                                    hover:-translate-y-0.5">
                                <span class="contact-detail-card__icon">
                                    <svg
                                        aria-hidden="true"
                                        viewBox="0 0 24 24"
                                        class="size-5 fill-none stroke-current"
                                        stroke-width="1.8">
                                        <path
                                            d="M7.2 3.5 9.8 8l-2.1 2.1a15.3 15.3 0 0 0 6.2 6.2l2.1-2.1 4.5 2.6v2.5a1.7 1.7 0 0 1-1.7 1.7A15.8 15.8 0 0 1 3 5.2a1.7 1.7 0 0 1 1.7-1.7h2.5Z" />
                                    </svg>
                                </span>

                                <span class="min-w-0">
                                    <span class="public-eyebrow !text-[0.62rem]">
                                        Phone
                                    </span>
                                    <strong
                                        class="mt-1 block text-sm
                                            text-brand-forest">
                                        {{ $restaurantPhone }}
                                    </strong>
                                    <span
                                        class="mt-1 block text-xs
                                            text-brand-muted">
                                        Call during restaurant hours.
                                    </span>
                                </span>
                            </a>

                            <a
                                data-gsap-reveal
                                href="mailto:{{ $restaurantEmail }}"
                                class="contact-detail-card transition
                                    hover:-translate-y-0.5">
                                <span
                                    class="contact-detail-card__icon
                                        !bg-brand-coral">
                                    <svg
                                        aria-hidden="true"
                                        viewBox="0 0 24 24"
                                        class="size-5 fill-none stroke-current"
                                        stroke-width="1.8">
                                        <rect
                                            x="3"
                                            y="5"
                                            width="18"
                                            height="14"
                                            rx="2" />
                                        <path d="m4 7 8 6 8-6" />
                                    </svg>
                                </span>

                                <span class="min-w-0">
                                    <span class="public-eyebrow !text-[0.62rem]">
                                        Email
                                    </span>
                                    <strong
                                        class="mt-1 block break-words text-sm
                                            text-brand-forest">
                                        {{ $restaurantEmail }}
                                    </strong>
                                    <span
                                        class="mt-1 block text-xs
                                            text-brand-muted">
                                        We aim to reply within one business day.
                                    </span>
                                </span>
                            </a>

                            <div
                                data-gsap-reveal
                                class="contact-detail-card">
                                <span
                                    class="contact-detail-card__icon
                                        !bg-brand-palm">
                                    <svg
                                        aria-hidden="true"
                                        viewBox="0 0 24 24"
                                        class="size-5 fill-none stroke-current"
                                        stroke-width="1.8">
                                        <path
                                            d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                        <circle cx="12" cy="10" r="2.5" />
                                    </svg>
                                </span>

                                <span class="min-w-0">
                                    <span class="public-eyebrow !text-[0.62rem]">
                                        Location
                                    </span>
                                    <strong
                                        class="mt-1 block text-sm
                                            text-brand-forest">
                                        {{ $restaurantAddress }}
                                    </strong>
                                    <span
                                        class="mt-1 block text-xs
                                            text-brand-muted">
                                        Near the California coast.
                                    </span>
                                </span>
                            </div>

                            <div
                                data-gsap-reveal
                                class="contact-detail-card">
                                <span
                                    class="contact-detail-card__icon
                                        !bg-brand-ocean">
                                    <svg
                                        aria-hidden="true"
                                        viewBox="0 0 24 24"
                                        class="size-5 fill-none stroke-current"
                                        stroke-width="1.8">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 2" />
                                    </svg>
                                </span>

                                <span class="min-w-0">
                                    <span class="public-eyebrow !text-[0.62rem]">
                                        Opening hours
                                    </span>
                                    <strong
                                        class="mt-1 block whitespace-pre-line
                                            text-sm leading-6
                                            text-brand-forest">{{ $openingHours }}</strong>
                                </span>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        {{-- Full-screen location and visit information --}}
        <section
            id="contact-visit"
            data-gsap="section"
            aria-labelledby="contact-visit-heading"
            class="contact-panel bg-brand-cream">
            <div class="contact-panel__content">
                <div
                    class="public-container grid items-center gap-8
                        lg:grid-cols-[0.72fr_1.28fr] lg:gap-12">
                    <div>
                        <p
                            data-gsap-reveal
                            class="public-eyebrow text-brand-coral">
                            Visit us
                        </p>

                        <h2
                            id="contact-visit-heading"
                            data-gsap-reveal
                            class="mt-4 font-display text-5xl leading-[0.98]
                                text-brand-forest sm:text-6xl">
                            Find us by the ocean.
                        </h2>

                        <p
                            data-gsap-reveal
                            class="mt-6 max-w-lg text-base leading-8
                                text-brand-muted">
                            Coast & Cay is designed as a welcoming pause from
                            the everyday—close to the shore, easy to reach, and
                            ready for dinner, pickup, or a relaxed celebration.
                        </p>

                        <div class="mt-8 grid gap-5">
                            <div data-gsap-reveal>
                                <p class="public-eyebrow">
                                    Parking and arrival
                                </p>
                                <p
                                    class="mt-2 text-sm leading-7
                                        text-brand-muted">
                                    Confirm final valet, street-parking, and
                                    accessibility details through Site Settings
                                    before launch.
                                </p>
                            </div>

                            <div data-gsap-reveal>
                                <p class="public-eyebrow">
                                    Pickup and online orders
                                </p>
                                <p
                                    class="mt-2 text-sm leading-7
                                        text-brand-muted">
                                    Place an order online and follow the pickup
                                    instructions shown during checkout.
                                </p>
                            </div>

                            <div data-gsap-reveal>
                                <p class="public-eyebrow">
                                    Restaurant address
                                </p>
                                <p
                                    class="mt-2 text-sm font-semibold leading-7
                                        text-brand-forest">
                                    {{ $restaurantAddress }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        data-gsap="panel"
                        class="grid gap-5 sm:grid-cols-[1.1fr_0.9fr]">
                        <div class="contact-map">
                            <div
                                class="contact-map__marker"
                                aria-hidden="true">
                                <span
                                    class="font-display text-sm font-semibold">
                                    C&amp;C
                                </span>
                            </div>

                            <div
                                class="contact-surface absolute bottom-5
                                    left-5 right-5 z-10 p-5
                                    sm:right-auto sm:max-w-[17rem]">
                                <p class="font-display text-xl text-brand-forest">
                                    Coast &amp; Cay
                                </p>
                                <p
                                    class="mt-2 text-sm leading-6
                                        text-brand-muted">
                                    {{ $restaurantAddress }}
                                </p>

                                <a
                                    href="https://www.google.com/maps/search/?api=1&query={{ urlencode($restaurantAddress) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-4 inline-flex text-xs font-semibold
                                        uppercase tracking-[0.16em]
                                        text-brand-palm transition
                                        hover:text-brand-coral">
                                    Get directions
                                    <span aria-hidden="true" class="ml-2">
                                        ↗
                                    </span>
                                </a>
                            </div>
                        </div>

                        <div class="grid gap-5">
                            <div
                                data-gsap="hero-image"
                                class="relative min-h-56 overflow-hidden
                                    rounded-[2rem] bg-brand-palm-dark">
                                @if ($heroImage?->image_url)
                                <x-public.responsive-image
                                    :image="$heroImage"
                                    :alt="$heroImage->alt_text ?: $heroImage->title ?: 'Coast and Cay restaurant atmosphere'"
                                    variant="card"
                                    sizes="(min-width: 640px) 35vw, 100vw"
                                    width="900"
                                    height="720"
                                    loading="lazy"
                                    img-class="absolute inset-0 size-full object-cover" />
                                @endif

                                <div
                                    class="absolute inset-0
                                        bg-gradient-to-t
                                        from-brand-palm-dark/45
                                        to-transparent"
                                    aria-hidden="true">
                                </div>
                            </div>

                            <div
                                class="rounded-[2rem] bg-brand-palm-dark
                                    p-6 text-white shadow-island-dark">
                                <p class="public-eyebrow text-brand-sun">
                                    Opening hours
                                </p>

                                <p
                                    class="mt-4 whitespace-pre-line text-sm
                                        leading-7 text-white/80">{{ $openingHours }}</p>

                                <p
                                    class="mt-5 border-t border-white/12 pt-4
                                        text-xs leading-6 text-white/55">
                                    Final kitchen and holiday hours remain
                                    editable through restaurant settings.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Full-screen closing invitation and FAQs --}}
        <section
            id="contact-welcome"
            data-gsap="section"
            aria-labelledby="contact-welcome-heading"
            class="contact-panel bg-brand-sand">
            <div
                class="absolute inset-y-0 left-0 -z-20 hidden w-[46%]
                    overflow-hidden lg:block">
                @if ($heroImage?->image_url)
                <x-public.responsive-image
                    :image="$heroImage"
                    :alt="$heroImage->alt_text ?: $heroImage->title ?: 'A warm Coast and Cay dining experience'"
                    variant="hero"
                    sizes="46vw"
                    width="1200"
                    height="1400"
                    loading="lazy"
                    img-class="absolute inset-0 size-full object-cover" />
                @endif

                <div
                    class="absolute inset-0 bg-gradient-to-r
                        from-brand-palm-dark/25
                        via-brand-palm-dark/38
                        to-brand-palm-dark/75"
                    aria-hidden="true">
                </div>
            </div>

            <div class="contact-panel__content">
                <div
                    class="public-container grid items-center gap-9
                        lg:grid-cols-2 lg:gap-14">
                    <div
                        data-gsap="panel"
                        class="relative overflow-hidden rounded-[2rem]
                            bg-brand-palm-dark p-8 text-white
                            shadow-island-dark lg:bg-transparent
                            lg:p-10 lg:shadow-none">
                        <p
                            data-gsap-reveal
                            class="public-eyebrow text-brand-coral">
                            We look forward to welcoming you
                        </p>

                        <h2
                            id="contact-welcome-heading"
                            data-gsap-reveal
                            class="mt-5 max-w-lg font-display text-5xl
                                leading-[0.98] text-white sm:text-6xl">
                            Good food. Warm people. Island spirit.
                        </h2>

                        <p
                            data-gsap-reveal
                            class="mt-6 max-w-md text-base leading-8
                                text-white/72">
                            From relaxed dinners to meaningful celebrations,
                            we are honored to be part of the moments that
                            matter.
                        </p>

                        <div
                            data-gsap-reveal
                            class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a
                                href="{{ route('menu') }}"
                                class="public-button-primary justify-center">
                                Explore the Menu
                            </a>

                            <a
                                href="#contact-message"
                                class="inline-flex min-h-12 items-center
                                    justify-center rounded-full border
                                    border-white/30 px-6 text-sm font-semibold
                                    text-white transition hover:bg-white
                                    hover:text-brand-forest">
                                Send a Message
                            </a>
                        </div>
                    </div>

                    <div data-gsap="panel">
                        <p class="public-eyebrow">
                            Before your visit
                        </p>

                        <h2
                            class="mt-4 font-display text-4xl
                                text-brand-forest sm:text-5xl">
                            Frequently asked questions
                        </h2>

                        <div
                            class="contact-surface mt-6 px-6
                                sm:px-7">
                            <details class="contact-faq-item">
                                <summary>
                                    Do you welcome walk-ins?
                                </summary>
                                <p
                                    class="pb-5 pr-10 text-sm leading-7
                                        text-brand-muted">
                                    Walk-ins are welcome when space permits.
                                    Contact the restaurant before arriving with
                                    a large group or when accessibility support
                                    is required.
                                </p>
                            </details>

                            <details class="contact-faq-item">
                                <summary>
                                    Can you help with dietary needs?
                                </summary>
                                <p
                                    class="pb-5 pr-10 text-sm leading-7
                                        text-brand-muted">
                                    Contact the restaurant before your visit so
                                    the team can explain current ingredients and
                                    available accommodations.
                                </p>
                            </details>

                            <details class="contact-faq-item">
                                <summary>
                                    Do you offer pickup or delivery?
                                </summary>
                                <p
                                    class="pb-5 pr-10 text-sm leading-7
                                        text-brand-muted">
                                    Online ordering supports pickup and
                                    configured local delivery areas whenever
                                    the restaurant is accepting online orders.
                                </p>
                            </details>

                            <details class="contact-faq-item">
                                <summary>
                                    Is the restaurant accessible?
                                </summary>
                                <p
                                    class="pb-5 pr-10 text-sm leading-7
                                        text-brand-muted">
                                    Send us a message or call before your visit
                                    for current entrance, seating, restroom, and
                                    parking accessibility details.
                                </p>
                            </details>
                        </div>

                        <div
                            data-gsap-reveal
                            class="mt-5 rounded-[1.5rem] border
                                border-brand-coral/35 bg-brand-cream p-6">
                            <p
                                class="font-display text-2xl
                                    text-brand-forest">
                                Planning something special?
                            </p>

                            <p
                                class="mt-2 text-sm leading-7
                                    text-brand-muted">
                                Tell us about your occasion and our team will
                                confirm what can be accommodated.
                            </p>

                            <a
                                href="#contact-message"
                                class="mt-5 inline-flex text-xs font-semibold
                                    uppercase tracking-[0.16em]
                                    text-brand-palm transition
                                    hover:text-brand-coral">
                                Inquire about your event
                                <span aria-hidden="true" class="ml-2">
                                    →
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>