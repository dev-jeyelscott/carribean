<x-layouts::auth :title="__('Create account')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Create your account')"
            :description="__('Save your details and follow your restaurant orders in one place.')" />

        <x-auth-session-status
            class="text-center"
            :status="session('status')" />

        <form
            method="POST"
            action="{{ route('register.store') }}"
            class="auth-form-grid">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <flux:input
                    name="name"
                    :label="__('Full name')"
                    :value="old('name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="__('Your full name')"
                    input:class="auth-input" />

                <flux:input
                    name="phone"
                    :label="__('Phone number')"
                    :value="old('phone')"
                    type="tel"
                    required
                    autocomplete="tel"
                    placeholder="+1 555 123 4567"
                    input:class="auth-input" />
            </div>

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
                input:class="auth-input" />

            <div class="grid gap-5 sm:grid-cols-2">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Create password')"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    input:class="auth-input"
                    viewable />

                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Repeat password')"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    input:class="auth-input"
                    viewable />
            </div>

            <p class="text-xs leading-6 text-brand-muted">
                Your account is used only to speed up checkout and give you
                secure access to your Coast &amp; Cay order history.
            </p>

            <flux:button
                type="submit"
                variant="primary"
                class="auth-submit-button w-full"
                data-test="register-user-button">
                <span>{{ __('Create account') }}</span>
                <span class="ml-2" aria-hidden="true">&rarr;</span>
            </flux:button>
        </form>

        <div class="text-center text-sm text-brand-muted">
            <span>{{ __('Already have an account?') }}</span>

            <flux:link
                class="font-semibold"
                :href="route('login')"
                wire:navigate>
                {{ __('Log in') }}
            </flux:link>
        </div>
    </div>
</x-layouts::auth>
