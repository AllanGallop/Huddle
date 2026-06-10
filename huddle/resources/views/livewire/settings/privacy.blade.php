<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Privacy & data') }}</flux:heading>

    <x-settings.layout
        :heading="__('Privacy & data')"
        :subheading="__('Manage your personal data, export a copy, and review how we use cookies.')"
    >
        <div class="space-y-8">
            @if (session('status'))
                <flux:callout variant="success">{{ session('status') }}</flux:callout>
            @endif

            <section class="space-y-3">
                <flux:heading size="lg">{{ __('Privacy policy') }}</flux:heading>
                <flux:text>
                    {{ __('Read how :app collects, uses, and protects your personal data.', ['app' => config('app.name')]) }}
                </flux:text>
                <flux:link :href="route('privacy.show')" target="_blank" rel="noopener noreferrer" wire:navigate>
                    {{ __('View privacy policy') }}
                </flux:link>

                @if (auth()->user()->hasAcceptedPrivacyPolicy())
                    <flux:text class="text-sm text-zinc-500">
                        {{ __('Accepted on :date (version :version).', [
                            'date' => auth()->user()->privacy_policy_accepted_at->format('j F Y'),
                            'version' => auth()->user()->privacy_policy_version,
                        ]) }}
                    </flux:text>
                @else
                    <form wire:submit="acceptPrivacyPolicy" class="mt-4 space-y-4 rounded-lg border border-amber-300 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-950/30">
                        <flux:text class="text-sm">
                            {{ __('Please confirm you have read and accept the current privacy policy to continue using :app.', ['app' => config('app.name')]) }}
                        </flux:text>
                        <flux:checkbox wire:model="accept_policy" :label="__('I have read and accept the privacy policy')" />
                        @error('accept_policy')
                            <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
                        @enderror
                        <flux:button type="submit" variant="primary" size="sm">
                            {{ __('Accept privacy policy') }}
                        </flux:button>
                    </form>
                @endif
            </section>

            <flux:separator />

            <section class="space-y-3">
                <flux:heading size="lg">{{ __('Download your data') }}</flux:heading>
                <flux:text>
                    {{ __('Export a machine-readable copy of the personal data we hold about you (GDPR right of access / portability).') }}
                </flux:text>
                <flux:button :href="route('user-data.export')" variant="primary" size="sm" icon="arrow-down-tray">
                    {{ __('Download JSON export') }}
                </flux:button>
            </section>

            <flux:separator />

            <section class="space-y-3">
                <flux:heading size="lg">{{ __('Cookies') }}</flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                    {{ __('We use essential cookies to keep you signed in and remember your preferences. Session cookies expire after :minutes minutes of inactivity. Optional “remember me” cookies keep you signed in for longer. We do not use third-party advertising cookies.', ['minutes' => config('session.lifetime')]) }}
                </flux:text>
            </section>

            <flux:separator />

            <section class="space-y-3">
                <flux:heading size="lg">{{ __('Contact') }}</flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                    @if (config('gdpr.contact_email'))
                        {{ __('For data protection enquiries, contact :controller at :email.', [
                            'controller' => config('gdpr.controller_name'),
                            'email' => config('gdpr.contact_email'),
                        ]) }}
                    @else
                        {{ __('For data protection enquiries, contact :controller.', ['controller' => config('gdpr.controller_name')]) }}
                    @endif
                </flux:text>
            </section>
        </div>
    </x-settings.layout>
</section>
