<div>
    @if (session('profile_status'))
        <div
            role="status"
            aria-live="polite"
            class="mb-7 flex items-start gap-3 rounded-[1.25rem]
                border border-primary/20 bg-primary/[0.08]
                px-5 py-4 text-sm font-medium text-primary-deep">
            <span
                class="mt-0.5 inline-flex size-6 shrink-0 items-center
                    justify-center rounded-full bg-primary text-canvas"
                aria-hidden="true">
                ✓
            </span>

            <span>
                {{ session('profile_status') }}
            </span>
        </div>
    @endif

    <form
        wire:submit="save"
        class="space-y-7"
        novalidate>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label
                    for="account-name"
                    class="mb-2 block text-sm font-semibold text-ink">
                    Full name
                </label>

                <input
                    id="account-name"
                    type="text"
                    wire:model="name"
                    autocomplete="name"
                    placeholder="Your full name"
                    class="min-h-12 w-full rounded-[1rem] border border-line
                        bg-surface px-4 py-3 text-ink shadow-sm transition
                        duration-200 placeholder:text-muted/55
                        hover:border-primary/30 focus:border-ocean
                        focus:outline-none focus:ring-4 focus:ring-ocean/10"
                    @error('name')
                        aria-invalid="true"
                        aria-describedby="account-name-error"
                    @enderror />

                @error('name')
                    <p
                        id="account-name-error"
                        role="alert"
                        class="mt-2 text-sm font-medium text-coral-deep">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label
                    for="account-phone"
                    class="mb-2 block text-sm font-semibold text-ink">
                    Phone number
                </label>

                <input
                    id="account-phone"
                    type="tel"
                    wire:model="phone"
                    autocomplete="tel"
                    inputmode="tel"
                    placeholder="+1 555 123 4567"
                    class="min-h-12 w-full rounded-[1rem] border border-line
                        bg-surface px-4 py-3 text-ink shadow-sm transition
                        duration-200 placeholder:text-muted/55
                        hover:border-primary/30 focus:border-ocean
                        focus:outline-none focus:ring-4 focus:ring-ocean/10"
                    @error('phone')
                        aria-invalid="true"
                        aria-describedby="account-phone-error"
                    @enderror />

                @error('phone')
                    <p
                        id="account-phone-error"
                        role="alert"
                        class="mt-2 text-sm font-medium text-coral-deep">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <div>
            <div
                class="mb-2 flex flex-wrap items-center
                    justify-between gap-2">
                <label
                    for="account-email"
                    class="block text-sm font-semibold text-ink">
                    Email address
                </label>

                <span
                    @class([
                        'inline-flex items-center gap-2 rounded-full',
                        'px-3 py-1 text-xs font-semibold',
                        'bg-primary/[0.09] text-primary' => $emailVerified,
                        'bg-coral/[0.11] text-coral-deep' => ! $emailVerified,
                    ])>
                    <span
                        @class([
                            'size-1.5 rounded-full',
                            'bg-primary' => $emailVerified,
                            'bg-coral' => ! $emailVerified,
                        ])
                        aria-hidden="true">
                    </span>

                    {{ $emailVerified ? 'Verified' : 'Not verified' }}
                </span>
            </div>

            <input
                id="account-email"
                type="email"
                wire:model="email"
                autocomplete="email"
                inputmode="email"
                placeholder="you@example.com"
                class="min-h-12 w-full rounded-[1rem] border border-line
                    bg-surface px-4 py-3 text-ink shadow-sm transition
                    duration-200 placeholder:text-muted/55
                    hover:border-primary/30 focus:border-ocean
                    focus:outline-none focus:ring-4 focus:ring-ocean/10"
                @error('email')
                    aria-invalid="true"
                    aria-describedby="account-email-error"
                @enderror />

            @error('email')
                <p
                    id="account-email-error"
                    role="alert"
                    class="mt-2 text-sm font-medium text-coral-deep">
                    {{ $message }}
                </p>
            @enderror

            <p class="mt-2 text-sm leading-6 text-muted">
                Changing this address requires verification of the new inbox.
            </p>
        </div>

        <div
            class="flex flex-col gap-4 border-t border-line pt-7
                sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-ink">
                    Checkout defaults
                </p>

                <p class="mt-1 text-sm leading-6 text-muted">
                    Future checkout forms will use these saved details.
                </p>

                <p
                    wire:dirty
                    class="mt-2 text-sm font-semibold text-coral-deep">
                    You have unsaved changes.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    wire:click="resetForm"
                    wire:dirty
                    class="public-button-secondary text-primary">
                    Reset
                </button>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="public-button-primary
                        data-loading:pointer-events-none
                        data-loading:cursor-wait
                        data-loading:opacity-70
                        disabled:cursor-not-allowed
                        disabled:opacity-60">
                    <span class="in-data-loading:hidden">
                        Save changes
                    </span>

                    <span
                        class="not-in-data-loading:hidden
                            inline-flex items-center gap-2">
                        <svg
                            class="size-4 animate-spin
                                motion-reduce:animate-none"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true">
                            <circle
                                class="opacity-30"
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-width="3">
                            </circle>

                            <path
                                class="opacity-90"
                                d="M21 12a9 9 0 0 0-9-9"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-linecap="round">
                            </path>
                        </svg>

                        Saving…
                    </span>
                </button>
            </div>
        </div>
    </form>

    @if (! $emailVerified)
        <section
            aria-labelledby="verify-email-heading"
            class="mt-8 rounded-[1.5rem] border border-coral/20
                bg-coral/[0.08] p-5 sm:p-6">
            <div
                class="flex flex-col gap-5 sm:flex-row
                    sm:items-start sm:justify-between">
                <div class="max-w-xl">
                    <p
                        class="text-xs font-semibold uppercase
                            tracking-[0.18em] text-coral-deep">
                        Account security
                    </p>

                    <h2
                        id="verify-email-heading"
                        class="mt-2 font-display text-2xl
                            text-primary-deep">
                        Verify your email
                    </h2>

                    <p class="mt-3 text-sm leading-6 text-muted">
                        Your account remains usable, but verification helps
                        protect password recovery and confirms where restaurant
                        order notifications should be delivered.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('verification.send') }}"
                    class="shrink-0">
                    @csrf

                    <button
                        type="submit"
                        class="public-button-secondary text-coral-deep">
                        Resend email
                    </button>
                </form>
            </div>
        </section>
    @endif
</div>
