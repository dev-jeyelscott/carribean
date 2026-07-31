@props([
    'submitLabel' => 'Send Message',
    'successTitle' => 'Message received',
])

<form
    method="POST"
    action="{{ route('contact-inquiries.store') }}"
    x-data="contactForm"
    @submit.prevent="submit"
    data-success-title="{{ $successTitle }}"
    {{ $attributes->class('contact-form') }}
    novalidate>
    @csrf

    {{-- Display the successful asynchronous submission response. --}}
    <div
        x-cloak
        x-show="successMessage"
        class="rounded-xl border border-primary/20 bg-primary/5
            px-4 py-3 text-sm leading-6 text-ink"
        role="status"
        aria-live="polite">
        <strong x-text="successTitle"></strong>
        <p class="mt-1" x-text="successMessage"></p>
    </div>

    {{-- Display a form-level request or network failure. --}}
    <div
        x-cloak
        x-show="errors.form"
        class="rounded-xl border border-red-300 bg-red-50
            px-4 py-3 text-sm leading-6 text-red-800"
        role="alert">
        <span x-text="errors.form"></span>
    </div>

    <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
        {{-- Collect the guest's name. --}}
        <div class="contact-form__field">
            <label
                for="customer_name"
                class="contact-form__label">
                Name
                <span
                    class="text-coral"
                    aria-hidden="true">
                    *
                </span>
            </label>

            <input
                id="customer_name"
                name="customer_name"
                type="text"
                value="{{ old('customer_name') }}"
                @class([
                    'contact-form__control',
                    'contact-form__control--invalid' => $errors->has('customer_name'),
                ])
                maxlength="120"
                autocomplete="name"
                placeholder="Your full name"
                aria-invalid="{{ $errors->has('customer_name') ? 'true' : 'false' }}"
                @if ($errors->has('customer_name'))
                    aria-describedby="customer_name-error"
                @endif
                required>

            @error('customer_name')
                <p
                    id="customer_name-error"
                    class="public-field-error contact-form__error"
                    role="alert">
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 20 20"
                        class="mt-0.5 size-4 shrink-0 fill-current">
                        <path
                            fill-rule="evenodd"
                            d="M10 1.75a8.25 8.25 0 1 0 0 16.5 8.25 8.25 0 0 0 0-16.5ZM9.25 6a.75.75 0 0 1 1.5 0v4.25a.75.75 0 0 1-1.5 0V6Zm.75 7.75a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z"
                            clip-rule="evenodd" />
                    </svg>

                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        {{-- Collect the reply email address. --}}
        <div class="contact-form__field">
            <label
                for="email"
                class="contact-form__label">
                Email
                <span
                    class="text-coral"
                    aria-hidden="true">
                    *
                </span>
            </label>

            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                @class([
                    'contact-form__control',
                    'contact-form__control--invalid' => $errors->has('email'),
                ])
                maxlength="160"
                autocomplete="email"
                inputmode="email"
                placeholder="you@example.com"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                @if ($errors->has('email'))
                    aria-describedby="email-error"
                @endif
                required>

            @error('email')
                <p
                    id="email-error"
                    class="public-field-error contact-form__error"
                    role="alert">
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 20 20"
                        class="mt-0.5 size-4 shrink-0 fill-current">
                        <path
                            fill-rule="evenodd"
                            d="M10 1.75a8.25 8.25 0 1 0 0 16.5 8.25 8.25 0 0 0 0-16.5ZM9.25 6a.75.75 0 0 1 1.5 0v4.25a.75.75 0 0 1-1.5 0V6Zm.75 7.75a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z"
                            clip-rule="evenodd" />
                    </svg>

                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        {{-- Collect an optional callback number. --}}
        <div class="contact-form__field">
            <label
                for="phone"
                class="contact-form__label">
                Phone
                <span class="contact-form__optional">
                    Optional
                </span>
            </label>

            <input
                id="phone"
                name="phone"
                type="tel"
                value="{{ old('phone') }}"
                @class([
                    'contact-form__control',
                    'contact-form__control--invalid' => $errors->has('phone'),
                ])
                maxlength="40"
                autocomplete="tel"
                inputmode="tel"
                placeholder="+1 (555) 555-0142"
                aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                @if ($errors->has('phone'))
                    aria-describedby="phone-error"
                @endif>

            @error('phone')
                <p
                    id="phone-error"
                    class="public-field-error contact-form__error"
                    role="alert">
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 20 20"
                        class="mt-0.5 size-4 shrink-0 fill-current">
                        <path
                            fill-rule="evenodd"
                            d="M10 1.75a8.25 8.25 0 1 0 0 16.5 8.25 8.25 0 0 0 0-16.5ZM9.25 6a.75.75 0 0 1 1.5 0v4.25a.75.75 0 0 1-1.5 0V6Zm.75 7.75a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z"
                            clip-rule="evenodd" />
                    </svg>

                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        {{-- Categorize the inquiry for faster restaurant handling. --}}
        <div class="contact-form__field">
            <label
                for="subject"
                class="contact-form__label">
                Subject
                <span class="contact-form__optional">
                    Optional
                </span>
            </label>

            <select
                id="subject"
                name="subject"
                @class([
                    'contact-form__control',
                    'contact-form__control--invalid' => $errors->has('subject'),
                ])
                aria-invalid="{{ $errors->has('subject') ? 'true' : 'false' }}"
                @if ($errors->has('subject'))
                    aria-describedby="subject-error"
                @endif>
                <option value="">
                    Choose a subject
                </option>

                @foreach ([
                    'General restaurant question',
                    'Menu or dietary question',
                    'Online order support',
                    'Directions or accessibility',
                    'Restaurant hours or visit question',
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
                <p
                    id="subject-error"
                    class="public-field-error contact-form__error"
                    role="alert">
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 20 20"
                        class="mt-0.5 size-4 shrink-0 fill-current">
                        <path
                            fill-rule="evenodd"
                            d="M10 1.75a8.25 8.25 0 1 0 0 16.5 8.25 8.25 0 0 0 0-16.5ZM9.25 6a.75.75 0 0 1 1.5 0v4.25a.75.75 0 0 1-1.5 0V6Zm.75 7.75a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z"
                            clip-rule="evenodd" />
                    </svg>

                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>
    </div>

    {{-- Collect the complete inquiry across the full form width. --}}
    <div class="contact-form__field">
        <label
            for="message"
            class="contact-form__label">
            Message
            <span
                class="text-coral"
                aria-hidden="true">
                *
            </span>
        </label>

        <textarea
            id="message"
            name="message"
            rows="5"
            @class([
                'contact-form__control contact-form__textarea',
                'contact-form__control--invalid' => $errors->has('message'),
            ])
            maxlength="5000"
            placeholder="Tell us how we can help..."
            aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}"
            @if ($errors->has('message'))
                aria-describedby="message-error"
            @endif
            required>{{ old('message') }}</textarea>

        @error('message')
            <p
                id="message-error"
                class="public-field-error contact-form__error"
                role="alert">
                <svg
                    aria-hidden="true"
                    viewBox="0 0 20 20"
                    class="mt-0.5 size-4 shrink-0 fill-current">
                    <path
                        fill-rule="evenodd"
                        d="M10 1.75a8.25 8.25 0 1 0 0 16.5 8.25 8.25 0 0 0 0-16.5ZM9.25 6a.75.75 0 0 1 1.5 0v4.25a.75.75 0 0 1-1.5 0V6Zm.75 7.75a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z"
                        clip-rule="evenodd" />
                </svg>

                <span>{{ $message }}</span>
            </p>
        @enderror
    </div>

    {{-- Keep the lightweight honeypot outside the visible form flow. --}}
    <div
        class="absolute left-[-10000px] top-auto size-px overflow-hidden"
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
        class="public-button-primary min-h-13 w-full justify-center"
        :disabled="submitting">
        <span x-show="! submitting">
            {{ $submitLabel }}
        </span>

        <span
            x-cloak
            x-show="submitting">
            Sending...
        </span>
    </button>

    <p class="text-center text-xs leading-6 text-muted">
        Your information is used only to respond to your inquiry.
    </p>
</form>
