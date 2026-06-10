<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl" class="inline-flex items-center gap-2">
            <x-material-icon name="admin_panel_settings" class="text-[1.75rem] text-huddle-primary" />
            {{ __('Admin') }}
        </flux:heading>
        <flux:text class="mt-1">{{ __('Manage team members, tags, membership renewals, branding, and organisation bank details.') }}</flux:text>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-huddle-comp/40 bg-huddle-comp/10 px-4 py-3 text-sm text-zinc-800 dark:text-zinc-200">
            {{ session('status') }}
        </div>
    @endif

    @error('user')
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300">
            {{ $message }}
        </div>
    @enderror

    <nav class="flex gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800/60" aria-label="{{ __('Admin sections') }}">
        <button
            type="button"
            wire:click="setTab('users')"
            @class([
                'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'users',
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'users',
            ])
        >
            <span class="inline-flex items-center justify-center gap-2">
                <x-material-icon name="group" class="text-[1.125rem]" />
                {{ __('Users') }}
            </span>
        </button>
        <button
            type="button"
            wire:click="setTab('tags')"
            @class([
                'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'tags',
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'tags',
            ])
        >
            <span class="inline-flex items-center justify-center gap-2">
                <x-material-icon name="sell" class="text-[1.125rem]" />
                {{ __('Tags') }}
            </span>
        </button>
        <button
            type="button"
            wire:click="setTab('membership')"
            @class([
                'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'membership',
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'membership',
            ])
        >
            <span class="inline-flex items-center justify-center gap-2">
                <x-material-icon name="card_membership" class="text-[1.125rem]" />
                {{ __('Membership') }}
            </span>
        </button>
        <button
            type="button"
            wire:click="setTab('branding')"
            @class([
                'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'branding',
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'branding',
            ])
        >
            <span class="inline-flex items-center justify-center gap-2">
                <x-material-icon name="palette" class="text-[1.125rem]" />
                {{ __('Branding') }}
            </span>
        </button>
        <button
            type="button"
            wire:click="setTab('bank')"
            @class([
                'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'bank',
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'bank',
            ])
        >
            <span class="inline-flex items-center justify-center gap-2">
                <x-material-icon name="account_balance" class="text-[1.125rem]" />
                {{ __('Bank details') }}
            </span>
        </button>
    </nav>

    @if ($activeTab === 'users')
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div>
                    <flux:heading size="lg">{{ __('Team members') }}</flux:heading>
                    <flux:text class="mt-1 text-sm">{{ __(':count users', ['count' => $this->users->count()]) }}</flux:text>
                </div>
                <div class="flex flex-wrap gap-2">
                    <flux:button variant="ghost" wire:click="openCreateUserModal('invite')">
                        <span class="inline-flex items-center gap-2">
                            <x-material-icon name="mail" class="text-[1.25rem]" />
                            {{ __('Invite user') }}
                        </span>
                    </flux:button>
                    <flux:button variant="primary" wire:click="openCreateUserModal('add')">
                        <span class="inline-flex items-center gap-2">
                            <x-material-icon name="person_add" class="text-[1.25rem]" />
                            {{ __('Add user') }}
                        </span>
                    </flux:button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                        <tr>
                            <th class="px-5 py-3">{{ __('Name') }}</th>
                            <th class="px-5 py-3">{{ __('Email') }}</th>
                            <th class="px-5 py-3">{{ __('Role') }}</th>
                            <th class="px-5 py-3 hidden lg:table-cell">{{ __('Tags') }}</th>
                            <th class="px-5 py-3 hidden sm:table-cell">{{ __('Joined') }}</th>
                            <th class="px-5 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->users as $user)
                            <tr wire:key="user-{{ $user->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-user-avatar :user="$user" size="sm" />
                                        <x-user-link :user="$user" class="text-zinc-900 dark:text-white" />
                                        @if ($user->id === auth()->id())
                                            <span class="rounded-full bg-huddle-primary/15 px-2 py-0.5 text-xs font-medium text-huddle-primary">{{ __('You') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-zinc-600 dark:text-zinc-300">{{ $user->email }}</td>
                                <td class="px-5 py-3">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-huddle-primary/15 text-huddle-primary' => $user->isAdmin(),
                                        'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => ! $user->isAdmin(),
                                    ])>
                                        {{ str($user->role?->name ?? 'member')->headline() }}
                                    </span>
                                </td>
                                <td class="hidden px-5 py-3 lg:table-cell">
                                    @if ($user->flags->isEmpty())
                                        <span class="text-zinc-400">—</span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($user->flags as $flag)
                                                <x-user-flag-badge :name="$flag->name" wire:key="user-{{ $user->id }}-flag-{{ $flag->id }}" />
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="hidden px-5 py-3 text-zinc-500 sm:table-cell">{{ $user->created_at->format('j M Y') }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-1">
                                        <flux:button size="sm" variant="ghost" wire:click="openEditUserModal({{ $user->id }})">
                                            <x-material-icon name="edit" class="text-[1rem]" />
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="{{ __('Delete :name? This cannot be undone.', ['name' => $user->name]) }}"
                                            :disabled="$user->id === auth()->id()"
                                        >
                                            <x-material-icon name="delete" class="text-[1rem]" />
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($activeTab === 'tags')
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div>
                    <flux:heading size="lg">{{ __('User tags') }}</flux:heading>
                    <flux:text class="mt-1 text-sm">{{ __('Labels you can assign to members (skills, teams, roles, etc.).') }}</flux:text>
                </div>
                <flux:button variant="primary" wire:click="openCreateTagModal">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="add" class="text-[1.25rem]" />
                        {{ __('Add tag') }}
                    </span>
                </flux:button>
            </div>

            @if ($this->flags->isEmpty())
                <div class="px-5 py-12 text-center">
                    <flux:text>{{ __('No tags yet. Create tags to assign them to users.') }}</flux:text>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                            <tr>
                                <th class="px-5 py-3">{{ __('Tag') }}</th>
                                <th class="px-5 py-3 hidden md:table-cell">{{ __('Description') }}</th>
                                <th class="px-5 py-3">{{ __('Members') }}</th>
                                <th class="px-5 py-3 text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($this->flags as $flag)
                                <tr wire:key="tag-{{ $flag->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-5 py-3">
                                        <x-user-flag-badge :name="$flag->name" />
                                    </td>
                                    <td class="hidden max-w-md px-5 py-3 text-zinc-600 dark:text-zinc-300 md:table-cell">
                                        {{ $flag->description ?: '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-zinc-600 dark:text-zinc-300">{{ $flag->users_count }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex justify-end gap-1">
                                            <flux:button size="sm" variant="ghost" wire:click="openEditTagModal({{ $flag->id }})">
                                                <x-material-icon name="edit" class="text-[1rem]" />
                                            </flux:button>
                                            <flux:button
                                                size="sm"
                                                variant="danger"
                                                wire:click="deleteTag({{ $flag->id }})"
                                                wire:confirm="{{ __('Delete tag :name? It will be removed from all users.', ['name' => $flag->name]) }}"
                                            >
                                                <x-material-icon name="delete" class="text-[1rem]" />
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    @if ($activeTab === 'membership')
        <nav class="flex gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800/60" aria-label="{{ __('Membership sections') }}">
            <button
                type="button"
                wire:click="setMembershipTab('periods')"
                @class([
                    'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                    'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $membershipTab === 'periods',
                    'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $membershipTab !== 'periods',
                ])
            >
                {{ __('Periods') }}
            </button>
            <button
                type="button"
                wire:click="setMembershipTab('assignments')"
                @class([
                    'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                    'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $membershipTab === 'assignments',
                    'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $membershipTab !== 'assignments',
                ])
            >
                {{ __('Assignments') }}
            </button>
        </nav>

        @if ($membershipTab === 'periods')
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                    <div>
                        <flux:heading size="lg">{{ __('Membership periods') }}</flux:heading>
                        <flux:text class="mt-1 text-sm">{{ __('Yearly membership renewals (e.g. 2026, 2025).') }}</flux:text>
                    </div>
                    <flux:button variant="primary" wire:click="openCreateRenewalModal">
                        <span class="inline-flex items-center gap-2">
                            <x-material-icon name="add" class="text-[1.25rem]" />
                            {{ __('Add period') }}
                        </span>
                    </flux:button>
                </div>

                @if ($this->membershipRenewals->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <flux:text>{{ __('No membership periods yet. Create one to assign to members.') }}</flux:text>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                                <tr>
                                    <th class="px-5 py-3">{{ __('Period') }}</th>
                                    <th class="px-5 py-3 hidden sm:table-cell">{{ __('Start') }}</th>
                                    <th class="px-5 py-3 hidden sm:table-cell">{{ __('End') }}</th>
                                    <th class="px-5 py-3">{{ __('Status') }}</th>
                                    <th class="px-5 py-3">{{ __('Members') }}</th>
                                    <th class="px-5 py-3 text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($this->membershipRenewals as $renewal)
                                    <tr wire:key="renewal-{{ $renewal->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">{{ $renewal->name }}</td>
                                        <td class="hidden px-5 py-3 text-zinc-600 dark:text-zinc-300 sm:table-cell">{{ $renewal->start_date->format('j M Y') }}</td>
                                        <td class="hidden px-5 py-3 text-zinc-600 dark:text-zinc-300 sm:table-cell">{{ $renewal->end_date->format('j M Y') }}</td>
                                        <td class="px-5 py-3">
                                            <span @class([
                                                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                'bg-huddle-comp/20 text-huddle-comp' => $renewal->isCurrent(),
                                                'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => ! $renewal->isCurrent(),
                                            ])>
                                                {{ $renewal->isCurrent() ? __('Current') : __('Past') }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-zinc-600 dark:text-zinc-300">{{ $renewal->assignments_count }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex justify-end gap-1">
                                                <flux:button size="sm" variant="ghost" wire:click="openEditRenewalModal({{ $renewal->id }})">
                                                    <x-material-icon name="edit" class="text-[1rem]" />
                                                </flux:button>
                                                <flux:button
                                                    size="sm"
                                                    variant="danger"
                                                    wire:click="deleteRenewal({{ $renewal->id }})"
                                                    wire:confirm="{{ __('Delete :name? All member assignments for this period will be removed.', ['name' => $renewal->name]) }}"
                                                >
                                                    <x-material-icon name="delete" class="text-[1rem]" />
                                                </flux:button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        @if ($membershipTab === 'assignments')
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                    <div>
                        <flux:heading size="lg">{{ __('Member assignments') }}</flux:heading>
                        <flux:text class="mt-1 text-sm">{{ __('Link members to a membership period.') }}</flux:text>
                    </div>
                    <flux:button
                        variant="primary"
                        wire:click="openCreateMembershipAssignmentModal"
                        :disabled="$this->membershipRenewals->isEmpty()"
                    >
                        <span class="inline-flex items-center gap-2">
                            <x-material-icon name="person_add" class="text-[1.25rem]" />
                            {{ __('Assign membership') }}
                        </span>
                    </flux:button>
                </div>

                @if ($this->membershipAssignments->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <flux:text>{{ __('No assignments yet. Assign a membership period to a member.') }}</flux:text>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                                <tr>
                                    <th class="px-5 py-3">{{ __('Member') }}</th>
                                    <th class="px-5 py-3">{{ __('Period') }}</th>
                                    <th class="px-5 py-3">{{ __('Membership') }}</th>
                                    <th class="px-5 py-3 text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($this->membershipAssignments as $assignment)
                                    <tr wire:key="membership-assignment-{{ $assignment->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <x-user-avatar :user="$assignment->user" size="sm" />
                                                <x-user-link :user="$assignment->user" class="text-zinc-900 dark:text-white" />
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">{{ $assignment->membershipRenewal->name }}</td>
                                        <td class="px-5 py-3">
                                            @php $status = $assignment->user->membershipStatus(); @endphp
                                            <span @class([
                                                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                'bg-huddle-comp/20 text-huddle-comp' => $status === 'active',
                                                'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => $status === 'expired',
                                                'bg-zinc-500/10 text-zinc-500' => $status === 'none',
                                            ])>
                                                @if ($status === 'active')
                                                    {{ __('Active') }}
                                                @elseif ($status === 'expired')
                                                    {{ __('Expired') }}
                                                @else
                                                    {{ __('None') }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <div class="flex justify-end gap-1">
                                                <flux:button size="sm" variant="ghost" wire:click="openEditMembershipAssignmentModal({{ $assignment->id }})">
                                                    <x-material-icon name="edit" class="text-[1rem]" />
                                                </flux:button>
                                                <flux:button
                                                    size="sm"
                                                    variant="danger"
                                                    wire:click="deleteMembershipAssignment({{ $assignment->id }})"
                                                    wire:confirm="{{ __('Remove :period membership from :name?', ['period' => $assignment->membershipRenewal->name, 'name' => $assignment->user->name]) }}"
                                                >
                                                    <x-material-icon name="delete" class="text-[1rem]" />
                                                </flux:button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    @endif

    @if ($activeTab === 'branding')
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
            <flux:heading size="lg" class="inline-flex items-center gap-2">
                <x-material-icon name="palette" class="text-[1.375rem] text-huddle-primary" />
                {{ __('Logo & favicon') }}
            </flux:heading>
            <flux:text class="mt-1">{{ __('Customise how Huddle appears in the app, emails, and documents. Leave uploads empty to keep the current assets.') }}</flux:text>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Current logo') }}</flux:text>
                    <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ config('app.name') }}" class="mt-3 h-16 w-16 object-contain">
                </div>
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Current banner') }}</flux:text>
                    <img src="{{ \App\Support\Branding::bannerUrl() }}" alt="{{ config('app.name') }}" class="mt-3 h-12 max-w-full object-contain">
                </div>
            </div>

            <form wire:submit="saveBranding" class="mt-6 space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:input type="file" wire:model="logoUpload" :label="__('Logo')" accept="image/svg+xml,image/png,image/jpeg,image/webp" />
                        <flux:text class="mt-1 text-xs text-zinc-500">{{ __('Square icon for the sidebar and app shell. SVG or PNG recommended.') }}</flux:text>
                    </div>
                    <div>
                        <flux:input type="file" wire:model="faviconUpload" :label="__('Favicon')" accept="image/svg+xml,image/png,image/x-icon" />
                        <flux:text class="mt-1 text-xs text-zinc-500">{{ __('Browser tab icon. Falls back to the logo if not set.') }}</flux:text>
                    </div>
                    <div>
                        <flux:input type="file" wire:model="bannerLightUpload" :label="__('Banner (light backgrounds)')" accept="image/svg+xml,image/png,image/jpeg,image/webp" />
                        <flux:text class="mt-1 text-xs text-zinc-500">{{ __('Used on auth pages, emails, and PDFs.') }}</flux:text>
                    </div>
                    <div>
                        <flux:input type="file" wire:model="bannerDarkUpload" :label="__('Banner (dark backgrounds)')" accept="image/svg+xml,image/png,image/jpeg,image/webp" />
                        <flux:text class="mt-1 text-xs text-zinc-500">{{ __('Used where the background is dark.') }}</flux:text>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <flux:button type="submit" variant="primary">
                        <span class="inline-flex items-center gap-2">
                            <x-material-icon name="save" class="text-[1.25rem]" />
                            {{ __('Save branding') }}
                        </span>
                    </flux:button>
                    <flux:button type="button" variant="ghost" wire:click="resetBranding" wire:confirm="{{ __('Remove custom branding and use the default Huddle logo?') }}">
                        {{ __('Reset to defaults') }}
                    </flux:button>
                </div>
            </form>
        </div>
    @endif

    @if ($activeTab === 'bank')
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
            <flux:heading size="lg" class="inline-flex items-center gap-2">
                <x-material-icon name="account_balance" class="text-[1.375rem] text-huddle-primary" />
                {{ __('Bank account details') }}
            </flux:heading>
            <flux:text class="mt-1">{{ __('Shown on invoices so clients know how to pay you.') }}</flux:text>

            <form wire:submit="saveBankDetails" class="mt-6 space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="account_name" :label="__('Account name')" />
                    <flux:input wire:model="bank_name" :label="__('Bank name')" />
                    <flux:input wire:model="sort_code" :label="__('Sort code')" placeholder="00-00-00" />
                    <flux:input wire:model="account_number" :label="__('Account number')" />
                    <flux:input wire:model="iban" :label="__('IBAN (optional)')" class="sm:col-span-2" />
                </div>

                <flux:textarea
                    wire:model="payment_instructions"
                    :label="__('Payment instructions')"
                    rows="4"
                    :placeholder="__('e.g. Please use your project name as the payment reference.')"
                />

                <flux:button type="submit" variant="primary">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="save" class="text-[1.25rem]" />
                        {{ __('Save bank details') }}
                    </span>
                </flux:button>
            </form>
        </div>
    @endif

    <flux:modal wire:model="showUserModal" class="md:max-w-2xl">
        <form wire:submit="saveUser" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    @if ($userModalMode === 'edit')
                        {{ __('Edit user') }}
                    @elseif ($userModalMode === 'invite')
                        {{ __('Invite user') }}
                    @else
                        {{ __('Add user') }}
                    @endif
                </flux:heading>
                <flux:text class="mt-1">
                    @if ($userModalMode === 'invite')
                        {{ __('Creates the account and emails a link to set their password.') }}
                    @elseif ($userModalMode === 'edit')
                        {{ __('Update details or set a new password.') }}
                    @else
                        {{ __('Create an account with a password you share securely.') }}
                    @endif
                </flux:text>
            </div>

            <flux:input wire:model="name" :label="__('Name')" required />
            <flux:input wire:model="email" type="email" :label="__('Email')" required />

            <flux:select wire:model="role_id" :label="__('Role')">
                @foreach ($this->roles as $role)
                    <flux:select.option :value="$role->id">{{ str($role->name)->headline() }}</flux:select.option>
                @endforeach
            </flux:select>

            @if ($userModalMode === 'add')
                <flux:input wire:model="password" type="password" :label="__('Password')" viewable required />
                <flux:input wire:model="password_confirmation" type="password" :label="__('Confirm password')" viewable required />
            @elseif ($userModalMode === 'edit')
                <flux:input wire:model="password" type="password" :label="__('New password (optional)')" viewable />
                <flux:input wire:model="password_confirmation" type="password" :label="__('Confirm new password')" viewable />
            @endif

            @if ($this->flags->isNotEmpty())
                <x-tag-assign-select
                    :flags="$this->flags"
                    :selected-ids="$assignedFlagIds"
                />
            @endif

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeUserModal">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">
                    @if ($userModalMode === 'edit')
                        {{ __('Save changes') }}
                    @elseif ($userModalMode === 'invite')
                        {{ __('Send invite') }}
                    @else
                        {{ __('Create user') }}
                    @endif
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal wire:model="showRenewalModal" class="md:max-w-lg">
        <form wire:submit="saveRenewal" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingRenewalId ? __('Edit membership period') : __('Add membership period') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Use the calendar year as the name (e.g. 2026). Dates fill in automatically.') }}</flux:text>
            </div>

            <flux:input wire:model.live="renewal_name" :label="__('Period')" placeholder="2026" required />
            <flux:input wire:model="renewal_start_date" type="date" :label="__('Start date')" required />
            <flux:input wire:model="renewal_end_date" type="date" :label="__('End date')" required />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeRenewalModal">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingRenewalId ? __('Save changes') : __('Create period') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal wire:model="showMembershipAssignmentModal" class="md:max-w-lg">
        <form wire:submit="saveMembershipAssignment" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingMembershipAssignmentId ? __('Edit assignment') : __('Assign membership') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Link a member to a membership period.') }}</flux:text>
            </div>

            <flux:select wire:model="membership_assignment_user_id" :label="__('Member')" required>
                <flux:select.option value="">{{ __('Select a member…') }}</flux:select.option>
                @foreach ($this->users as $user)
                    <flux:select.option :value="$user->id">{{ $user->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="membership_assignment_renewal_id" :label="__('Period')" required>
                <flux:select.option value="">{{ __('Select a period…') }}</flux:select.option>
                @foreach ($this->membershipRenewals as $renewal)
                    <flux:select.option :value="$renewal->id">{{ $renewal->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeMembershipAssignmentModal">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingMembershipAssignmentId ? __('Save changes') : __('Assign') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal wire:model="showTagModal" class="md:max-w-lg">
        <form wire:submit="saveTag" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingTagId ? __('Edit tag') : __('Add tag') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Tags help group and identify members across the community.') }}</flux:text>
            </div>

            <flux:input wire:model="tag_name" :label="__('Name')" required />
            <flux:textarea wire:model="tag_description" :label="__('Description (optional)')" rows="3" />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeTagModal">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingTagId ? __('Save changes') : __('Create tag') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
