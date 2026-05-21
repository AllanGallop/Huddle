<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:link
            :href="url()->previous() !== url()->current() ? url()->previous() : route('dashboard')"
            wire:navigate
            class="inline-flex items-center gap-1 text-sm"
        >
            <x-material-icon name="arrow_back" class="text-[1rem]" />
            {{ __('Back') }}
        </flux:link>

        <div class="mt-4 flex items-center gap-4">
            <x-user-avatar :user="$profileUser" size="lg" />
            <div>
                <flux:heading size="xl">{{ $profileUser->name }}</flux:heading>
                <flux:text class="mt-1">
                    <span @class([
                        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                        'bg-huddle-primary/15 text-huddle-primary' => $profileUser->isAdmin(),
                        'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => ! $profileUser->isAdmin(),
                    ])>
                        {{ str($profileUser->role?->name ?? 'member')->headline() }}
                    </span>
                </flux:text>
            </div>
        </div>
    </div>

    <x-user-membership
        :user="$profileUser"
        class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6 lg:max-w-2xl"
    />
</div>
