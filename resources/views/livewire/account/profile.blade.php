<div class="space-y-8">
    @if (session('profile_status'))
        <div
            role="status"
            class="rounded-2xl border border-brand-palm/20 bg-brand-palm/10
                px-5 py-4 text-sm font-medium text-brand-palm-dark">
            {{ session('profile_status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6" novalidate>
        <div>
            <label
                for="account-name"
                class="mb-2 block text-sm font-semibold text-brand-palm-dark">
                Full name
            </label>

            <input
                id="account-name"
                type="text"
                wire:model="name"
                autocomplete="name"
                class="min-h-12 w-full rounded-xl border border-brand-palm/20
                    bg-white px-4 text-brand-forest shadow-sm transition
                    placeholder:text-brand-muted/60
                    focus:border-brand-ocean focus:outline-none
                    focus:ring-2 focus:ring-brand-ocean/20" />

            @error('name')
                <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label
                for="account-email"
                class="mb-2 block text-sm font-semibold text-brand-palm-dark">
                Email address
            </label>

            <input
                id="account-email"
                type="email"
                wire:model="email"
                autocomplete="email"
                class="min-h-12 w-full rounded-xl border border-brand-palm/20
                    bg-white px-4 text-brand-forest shadow-sm transition
                    placeholder:text-brand-muted/60
                    focus:border-brand-ocean focus:outline-none
                    focus:ring-2 focus:ring-brand-ocean/20" />

            @error('email')
                <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label
                for="account-phone"
                class="mb-2 block text-sm font-semibold text-brand-palm-dark">
                Phone number
            </label>

            <input
                id="account-phone"
                type="tel"
                wire:model="phone"
                autocomplete="tel"
                placeholder="+1 555 123 4567"
                class="min-h-12 w-full rounded-xl border border-brand-palm/20
                    bg-white px-4 text-brand-forest shadow-sm transition
                    placeholder:text-brand-muted/60
                    focus:border-brand-ocean focus:outline-none
                    focus:ring-2 focus:ring-brand-ocean/20" />

            @error('phone')
                <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="save"
                class="public-button-primary disabled:cursor-not-allowed
                    disabled:opacity-60">
                <span wire:loading.remove wire:target="save">
                    Save changes
                </span>

                <span wire:loading wire:target="save">
                    Saving…
                </span>
            </button>

            <p class="text-sm text-brand-muted">
                Checkout will use these details as its defaults.
            </p>
        </div>
    </form>

    @if (! $emailVerified)
        <section
            class="rounded-2xl border border-brand-coral/25
                bg-brand-coral/10 p-5">
            <h2 class="font-display text-xl text-brand-palm-dark">
                Verify your email
            </h2>

            <p class="mt-2 text-sm leading-6 text-brand-muted">
                Your account remains usable, but verifying your email helps
                protect future order notifications and password recovery.
            </p>

            <form
                method="POST"
                action="{{ route('verification.send') }}"
                class="mt-4">
                @csrf

                <button
                    type="submit"
                    class="text-sm font-semibold text-brand-coral-dark
                        underline decoration-brand-coral/40 underline-offset-4
                        hover:text-brand-palm-dark">
                    Send another verification email
                </button>
            </form>
        </section>
    @endif
</div>
