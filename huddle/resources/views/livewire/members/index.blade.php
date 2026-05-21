<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl" class="inline-flex items-center gap-2">
            <x-material-icon name="groups" class="text-[1.75rem] text-huddle-primary" />
            {{ __('Members') }}
        </flux:heading>
        <flux:text class="mt-1">{{ __('Browse community members by name, membership status, or tags.') }}</flux:text>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 sm:p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="sm" class="inline-flex items-center gap-2">
                <x-material-icon name="filter_list" class="text-[1.125rem] text-huddle-primary" />
                {{ __('Search & filters') }}
            </flux:heading>
            @if ($this->hasActiveFilters)
                <flux:button variant="ghost" size="sm" wire:click="clearFilters">
                    <span class="inline-flex items-center gap-1.5">
                        <x-material-icon name="close" class="text-[1.125rem]" />
                        {{ __('Clear all') }}
                    </span>
                </flux:button>
            @endif
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :label="__('Search')"
                :placeholder="__('Name or tag…')"
            >
                <x-slot:iconLeading>
                    <x-material-icon name="search" class="text-[1.25rem] text-zinc-400" />
                </x-slot:iconLeading>
            </flux:input>

            <flux:select wire:model.live="membershipFilter" :label="__('Membership')">
                <flux:select.option value="">{{ __('All members') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active membership') }}</flux:select.option>
                <flux:select.option value="expired">{{ __('Expired membership') }}</flux:select.option>
                <flux:select.option value="none">{{ __('No membership') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="tagFilter" :label="__('Tag')">
                <flux:select.option value="">{{ __('All tags') }}</flux:select.option>
                @foreach ($this->tags as $tag)
                    <flux:select.option :value="$tag->id">{{ $tag->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <flux:text class="mt-4 text-sm text-zinc-500">
            {{ trans_choice(':count member|:count members', $this->members->count(), ['count' => $this->members->count()]) }}
        </flux:text>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        @if ($this->members->isEmpty())
            <div class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                <div class="flex size-14 items-center justify-center rounded-lg bg-huddle-primary/10 text-huddle-primary">
                    <x-material-icon name="groups" class="text-[2rem]" />
                </div>
                <flux:heading size="lg">
                    {{ $this->hasActiveFilters ? __('No matching members') : __('No members yet') }}
                </flux:heading>
                <flux:text>
                    {{ $this->hasActiveFilters ? __('Try adjusting your search or filters.') : __('Members will appear here once accounts are created.') }}
                </flux:text>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                        <tr>
                            <th class="px-5 py-3">{{ __('Member') }}</th>
                            <th class="px-5 py-3">{{ __('Role') }}</th>
                            <th class="px-5 py-3 hidden md:table-cell">{{ __('Membership') }}</th>
                            <th class="px-5 py-3 hidden lg:table-cell">{{ __('Tags') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->members as $member)
                            @php
                                $status = $member->membershipStatus();
                                $latestMembership = $member->latestMembershipRenewalAssignment();
                            @endphp
                            <tr wire:key="member-{{ $member->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-user-avatar :user="$member" size="sm" />
                                        <x-user-link :user="$member" class="text-zinc-900 dark:text-white" />
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-huddle-primary/15 text-huddle-primary' => $member->isAdmin(),
                                        'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => ! $member->isAdmin(),
                                    ])>
                                        {{ str($member->role?->name ?? 'member')->headline() }}
                                    </span>
                                </td>
                                <td class="hidden px-5 py-3 md:table-cell">
                                    @if ($status === 'none')
                                        <span class="text-zinc-400">—</span>
                                    @else
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            'bg-huddle-comp/20 text-huddle-comp' => $status === 'active',
                                            'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => $status === 'expired',
                                        ])>
                                            @if ($status === 'active')
                                                {{ __('Active') }}
                                                @if ($latestMembership)
                                                    <span class="ms-1 opacity-80">({{ $latestMembership->membershipRenewal->name }})</span>
                                                @endif
                                            @else
                                                {{ __('Expired') }}
                                                @if ($latestMembership)
                                                    <span class="ms-1 opacity-80">({{ $latestMembership->membershipRenewal->name }})</span>
                                                @endif
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td class="hidden px-5 py-3 lg:table-cell">
                                    @if ($member->flags->isEmpty())
                                        <span class="text-zinc-400">—</span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($member->flags as $flag)
                                                <x-user-flag-badge :name="$flag->name" wire:key="member-{{ $member->id }}-flag-{{ $flag->id }}" />
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
