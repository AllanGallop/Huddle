<x-layouts::auth :title="__('Privacy policy')">
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-8 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
        <div>
            <flux:heading size="xl">{{ __('Privacy policy') }}</flux:heading>
            <flux:text class="mt-2 text-zinc-500">
                {{ __('Version :version · Last updated :date', [
                    'version' => config('gdpr.policy_version'),
                    'date' => now()->format('j F Y'),
                ]) }}
            </flux:text>
        </div>

        <section class="space-y-2">
            <flux:heading size="lg">{{ __('Who we are') }}</flux:heading>
            <p>
                {{ __(':controller operates :app, a members-only platform for coordinating community projects, events, and internal resources.', [
                    'controller' => config('gdpr.controller_name'),
                    'app' => config('app.name'),
                ]) }}
            </p>
            @if (config('gdpr.contact_email'))
                <p>{{ __('Data protection contact: :email', ['email' => config('gdpr.contact_email')]) }}</p>
            @endif
        </section>

        <section class="space-y-2">
            <flux:heading size="lg">{{ __('What we collect') }}</flux:heading>
            <ul class="list-disc space-y-1 ps-5">
                <li>{{ __('Account details: name, email address, password (stored hashed), and role.') }}</li>
                <li>{{ __('Membership data: tags, accreditations, and renewal periods assigned by administrators.') }}</li>
                <li>{{ __('Activity data: project and event comments, volunteer sign-ups, form responses, and wiki contributions.') }}</li>
                <li>{{ __('Technical data: session identifiers, IP address, and browser user-agent stored with active sessions.') }}</li>
                <li>{{ __('Communication preferences: community digest opt-in/out status.') }}</li>
            </ul>
        </section>

        <section class="space-y-2">
            <flux:heading size="lg">{{ __('Why we use your data') }}</flux:heading>
            <ul class="list-disc space-y-1 ps-5">
                <li>{{ __('To provide access to the platform and authenticate you.') }}</li>
                <li>{{ __('To coordinate community projects, events, forms, and internal documentation.') }}</li>
                <li>{{ __('To send service emails such as password resets, invitations, and optional community digests.') }}</li>
                <li>{{ __('To maintain security, audit activity, and comply with legal obligations.') }}</li>
            </ul>
        </section>

        <section class="space-y-2">
            <flux:heading size="lg">{{ __('Your rights') }}</flux:heading>
            <p>{{ __('Under UK GDPR you have the right to access, rectify, erase, restrict, object to processing, and port your personal data. You can:') }}</p>
            <ul class="list-disc space-y-1 ps-5">
                <li>{{ __('Update your profile and notification preferences in Settings.') }}</li>
                <li>{{ __('Download a JSON export of your data from Settings → Privacy & data.') }}</li>
                <li>{{ __('Delete your account from Settings → Profile (this erases personal data and anonymises historical references).') }}</li>
                <li>{{ __('Unsubscribe from digest emails via Settings or the link in each email.') }}</li>
            </ul>
        </section>

        <section class="space-y-2">
            <flux:heading size="lg">{{ __('Cookies') }}</flux:heading>
            <p>
                {{ __('We use essential session cookies to keep you signed in. If you choose “remember me”, a longer-lived cookie is stored. Appearance preferences may be saved in your browser’s local storage. We do not use non-essential tracking cookies.') }}
            </p>
        </section>

        <section class="space-y-2">
            <flux:heading size="lg">{{ __('Retention') }}</flux:heading>
            <p>
                {{ __('We keep your account data while your membership is active. When you delete your account, personal identifiers are removed and historical records are reassigned to a generic “deleted member” placeholder where needed for community continuity.') }}
            </p>
        </section>

        <div class="pt-4">
            @auth
                <flux:link :href="route('privacy.edit')" wire:navigate>{{ __('Back to privacy settings') }}</flux:link>
            @else
                <flux:link :href="route('login')" wire:navigate>{{ __('Sign in') }}</flux:link>
            @endauth
        </div>
    </div>
</x-layouts::auth>
