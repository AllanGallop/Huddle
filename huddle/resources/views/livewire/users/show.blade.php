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
                    <div class="flex flex-wrap gap-1">
                        @forelse ($profileUser->roles as $role)
                            <span @class([
                                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                'bg-huddle-primary/15 text-huddle-primary' => strcasecmp($role->name, 'admin') === 0,
                                'bg-huddle-comp/20 text-green-800 dark:text-huddle-comp' => strcasecmp($role->name, 'Mentor') === 0,
                                'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => ! in_array(strtolower($role->name), ['admin', 'mentor'], true),
                            ])>
                                {{ str($role->name)->headline() }}
                            </span>
                        @empty
                            <span class="inline-flex rounded-full bg-zinc-500/15 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                {{ __('Member') }}
                            </span>
                        @endforelse
                    </div>
                </flux:text>
            </div>
        </div>
    </div>

    <x-user-membership
        :user="$profileUser"
        class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6 lg:max-w-2xl"
    />
</div>
