<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        @error('email')
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-center text-sm text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300" role="alert">
                {{ $message }}
            </div>
        @enderror

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="absolute top-0 text-sm end-0 font-medium text-huddle-primary hover:opacity-80">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            {{-- Native checkbox: Flux ui-checkbox is not a form-associated control, so "remember" never reached Fortify. --}}
            <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                    class="rounded border-zinc-300 text-huddle-primary focus:ring-huddle-primary dark:border-zinc-600 dark:bg-zinc-900"
                >
                <span>{{ __('Remember me') }}</span>
            </label>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
