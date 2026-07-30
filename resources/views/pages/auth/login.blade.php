<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Welcome back')"
            :description="__('Log in to manage your details and follow your orders.')" />

        <x-auth-session-status
            class="text-center"
            :status="session('status')" />

        <form
            method="POST"
            action="{{ route('login.store') }}"
            class="auth-form-grid">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
                input:class="auth-input" />

            <div class="auth-password-field">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Enter your password')"
                    input:class="auth-input"
                    viewable />

                @if (Route::has('password.request'))
                    <flux:link
                        class="auth-password-field__link"
                        :href="route('password.request')"
                        wire:navigate>
                        {{ __('Forgot password?') }}
                    </flux:link>
                @endif
            </div>

            <div class="flex items-center justify-between gap-4">
                <flux:checkbox
                    name="remember"
                    :label="__('Remember me')"
                    :checked="old('remember')" />

                <span class="text-xs text-brand-muted">
                    Secure customer login
                </span>
            </div>

            <flux:button
                variant="primary"
                type="submit"
                class="auth-submit-button w-full"
                data-test="login-button">
                <span>{{ __('Log in') }}</span>
                <span class="ml-2" aria-hidden="true">&rarr;</span>
            </flux:button>
        </form>

        @if (Route::has('register'))
            <div class="text-center text-sm text-brand-muted">
                <span>{{ __('New to Coast & Cay?') }}</span>

                <flux:link
                    class="font-semibold"
                    :href="route('register')"
                    wire:navigate>
                    {{ __('Create an account') }}
                </flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>
