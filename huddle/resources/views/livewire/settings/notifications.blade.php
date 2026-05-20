<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Notification Settings') }}</flux:heading>

    <x-settings.layout
        :heading="__('Notifications')"
        :subheading="__('Choose which emails you receive from :app', ['app' => config('app.name')])"
    >
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-huddle-comp/40 bg-huddle-comp/10 px-4 py-3 text-sm text-zinc-800 dark:text-zinc-200">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit="save" class="my-6 w-full space-y-6">
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Community digest') }}</flux:heading>
                <flux:text class="mt-1 text-sm">
                    {{ __('A periodic summary of new public events, volunteer opportunities, and updates on projects and events you follow.') }}
                </flux:text>

                <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                    <li>{{ __('New public events') }}</li>
                    <li>{{ __('Updates on events and projects you volunteer for') }}</li>
                    <li>{{ __('New projects looking for volunteers') }}</li>
                </ul>

                <div class="mt-4">
                    <flux:checkbox
                        wire:model="digest_opt_out"
                        :label="__('Opt out of community digest emails')"
                    />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save preferences') }}</flux:button>
                <x-action-message on="notifications-saved">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
