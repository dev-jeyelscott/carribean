<x-layouts.public
    :title="$page?->meta_title ?: 'Contact | Coast & Cay'"
    :description="$page?->meta_description ?: 'Contact Coast & Cay for menu questions, online-order support, directions, and restaurant information.'">
    <div data-home-motion>
        <section
            data-public-hero
            class="public-hero-viewport relative isolate flex items-center
                overflow-hidden bg-brand-palm-dark">
            @if ($heroImage?->image_url)
            <div data-gsap="hero-image" class="absolute inset-0 -z-30">
                <x-public.responsive-image
                    :image="$heroImage"
                    :alt="$heroImage->alt_text ?: $heroImage->title ?: 'Warm Coast and Cay dining room'"
                    variant="hero"
                    sizes="100vw"
                    width="1920"
                    height="1280"
                    loading="eager"
                    fetchpriority="high"
                    img-class="h-full w-full object-cover object-center" />
            </div>
            @else
            <div
                data-contact-hero-fallback
                class="absolute inset-0 -z-30
                    bg-[radial-gradient(circle_at_72%_28%,rgba(242,199,107,0.28),transparent_25%),linear-gradient(135deg,#206f7c,#0c342b_62%)]">
            </div>
            @endif

            <div
                class="absolute inset-0 -z-20
                    bg-[linear-gradient(to_bottom,rgba(12,52,43,0.38),rgba(12,52,43,0.62)_48%,rgba(12,52,43,0.98))]">
            </div>

            <div class="public-container pb-20 pt-36 sm:pb-24 sm:pt-44">
                <div data-gsap="hero-content" class="max-w-3xl">
                    <p
                        data-gsap-reveal
                        class="text-xs font-semibold uppercase
                            tracking-[0.3em] text-brand-sun">
                        Contact Coast & Cay
                    </p>

                    <h1
                        data-gsap-reveal
                        class="mt-6 font-display text-5xl leading-[0.96]
                            text-white sm:text-7xl">
                        {{ $page?->title ?: 'Come Say Hello' }}
                    </h1>

                    <p
                        data-gsap-reveal
                        class="mt-7 max-w-2xl text-base leading-8
                            text-white/78 sm:text-lg">
                        {{ $page?->excerpt ?: 'Questions about the menu, an online order, directions, or the restaurant? Our team will be pleased to help.' }}
                    </p>
                </div>
            </div>
        </section>

        <section
            id="contact-inquiry"
            data-gsap="section"
            class="public-island-pattern bg-brand-cream py-20 lg:py-28">
            <div
                class="public-container grid gap-10
                    lg:grid-cols-[0.72fr_1.28fr] lg:gap-14">
                <aside
                    data-gsap="panel"
                    class="rounded-island bg-brand-palm-dark p-7 text-white
                        shadow-island-dark sm:p-9">
                    <p class="public-eyebrow text-brand-sun">
                        Reach the Restaurant
                    </p>

                    <h2
                        class="mt-5 font-display text-4xl leading-tight">
                        We are here to help.
                    </h2>

                    <p class="mt-6 text-sm leading-7 text-white/68">
                        Send a message for menu questions, online-order support,
                        directions, accessibility information, or general
                        restaurant details.
                    </p>

                    <div class="mt-9 space-y-6 text-sm leading-7 text-white/72">
                        @if ($settings['phone'] ?? null)
                        <div>
                            <p class="text-xs font-semibold uppercase
                                tracking-[0.18em] text-brand-sun">
                                Phone
                            </p>
                            <p class="mt-1">{{ $settings['phone'] }}</p>
                        </div>
                        @endif

                        @if ($settings['email'] ?? null)
                        <div>
                            <p class="text-xs font-semibold uppercase
                                tracking-[0.18em] text-brand-sun">
                                Email
                            </p>
                            <a
                                href="mailto:{{ $settings['email'] }}"
                                class="mt-1 block break-words transition
                                    hover:text-brand-sun">
                                {{ $settings['email'] }}
                            </a>
                        </div>
                        @endif

                        @if ($settings['address'] ?? null)
                        <div>
                            <p class="text-xs font-semibold uppercase
                                tracking-[0.18em] text-brand-sun">
                                Address
                            </p>
                            <p class="mt-1">{{ $settings['address'] }}</p>
                        </div>
                        @endif

                        @if ($settings['opening_hours'] ?? null)
                        <div>
                            <p class="text-xs font-semibold uppercase
                                tracking-[0.18em] text-brand-sun">
                                Opening Hours
                            </p>
                            <p class="mt-1 whitespace-pre-line">
                                {{ $settings['opening_hours'] }}
                            </p>
                        </div>
                        @endif
                    </div>
                </aside>

                <div
                    data-gsap="panel"
                    class="rounded-island bg-white p-7 shadow-island sm:p-10">
                    <div>
                        <p class="public-eyebrow">Send a Message</p>

                        <h2
                            class="mt-4 font-display text-4xl
                                text-brand-forest">
                            How can we help?
                        </h2>

                        <p class="mt-4 max-w-2xl text-sm leading-7
                            text-brand-muted">
                            For help with an existing order, include the order
                            number in your message.
                        </p>
                    </div>

                    @if (session('success'))
                    <x-public.alert type="success" class="mt-7">
                        {{ session('success') }}
                    </x-public.alert>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('contact-inquiries.store') }}"
                        x-data="contactForm"
                        @submit.prevent="submit"
                        data-success-title="Message received"
                        class="mt-8 space-y-6"
                        novalidate>
                        @csrf

                        <div
                            x-cloak
                            x-show="successMessage"
                            class="rounded-island border border-brand-palm/20
                                bg-brand-palm/5 p-4 text-sm text-brand-forest"
                            role="status"
                            aria-live="polite">
                            <strong x-text="successTitle"></strong>
                            <p class="mt-1" x-text="successMessage"></p>
                        </div>

                        <div
                            x-cloak
                            x-show="errors.form"
                            class="rounded-island border border-red-300
                                bg-red-50 p-4 text-sm text-red-800"
                            role="alert">
                            <span x-text="errors.form"></span>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="customer_name" class="public-label">
                                    Name
                                </label>
                                <input
                                    id="customer_name"
                                    name="customer_name"
                                    type="text"
                                    value="{{ old('customer_name') }}"
                                    class="public-input"
                                    maxlength="120"
                                    autocomplete="name"
                                    required>
                                @error('customer_name')
                                <p class="public-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="public-label">
                                    Email
                                </label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    class="public-input"
                                    maxlength="160"
                                    autocomplete="email"
                                    required>
                                @error('email')
                                <p class="public-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="phone" class="public-label">
                                    Phone <span class="font-normal">(optional)</span>
                                </label>
                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    value="{{ old('phone') }}"
                                    class="public-input"
                                    maxlength="40"
                                    autocomplete="tel"
                                    placeholder="+1 (555) 555-0142">
                                @error('phone')
                                <p class="public-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="subject" class="public-label">
                                    Subject <span class="font-normal">(optional)</span>
                                </label>
                                <select
                                    id="subject"
                                    name="subject"
                                    class="public-input">
                                    <option value="">Choose a subject</option>
                                    @foreach ([
                                        'General restaurant question',
                                        'Menu question',
                                        'Online order support',
                                        'Directions or accessibility',
                                        'Website feedback',
                                    ] as $subject)
                                    <option
                                        value="{{ $subject }}"
                                        @selected(old('subject') === $subject)>
                                        {{ $subject }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('subject')
                                <p class="public-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="message" class="public-label">
                                Message
                            </label>
                            <textarea
                                id="message"
                                name="message"
                                rows="7"
                                class="public-input"
                                maxlength="5000"
                                required>{{ old('message') }}</textarea>
                            @error('message')
                            <p class="public-field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div
                            class="absolute left-[-10000px] top-auto size-px
                                overflow-hidden"
                            aria-hidden="true">
                            <label for="website">Website</label>
                            <input
                                id="website"
                                name="website"
                                type="text"
                                tabindex="-1"
                                autocomplete="off">
                        </div>

                        <button
                            type="submit"
                            class="public-button-primary w-full sm:w-auto"
                            :disabled="submitting">
                            <span x-show="! submitting">Send Message</span>
                            <span x-cloak x-show="submitting">Sending...</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>
