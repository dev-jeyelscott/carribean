<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Welcome back')"
            :description="__('Log in to manage your details and follow your orders.')" />

        <x-auth-session-status
            class="text-center"
            :status="session('status')" />

        <x-passkey-verify />

        <form
            method="POST"
            action="{{ route('login.store') }}"
            class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com" />

            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable />

                @if (Route::has('password.request'))
                    <flux:link
                        class="absolute end-0 top-0 text-sm"
                        :href="route('password.request')"
                        wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox
                name="remember"
                :label="__('Remember me')"
                :checked="old('remember')" />

            <flux:button
                variant="primary"
                type="submit"
                class="w-full"
                data-test="login-button">
                {{ __('Log in') }}
            </flux:button>
        </form>

        @if (Route::has('register'))
            <div class="text-center text-sm text-zinc-600">
                <span>{{ __('New to Coast & Cay?') }}</span>

                <flux:link :href="route('register')" wire:navigate>
                    {{ __('Create an account') }}
                </flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>
