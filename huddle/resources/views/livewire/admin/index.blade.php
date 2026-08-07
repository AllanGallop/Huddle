<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl" class="inline-flex items-center gap-2">
            <x-material-icon name="admin_panel_settings" class="text-[1.75rem] text-huddle-primary" />
            {{ __('Admin') }}
        </flux:heading>
        <flux:text class="mt-1">{{ __('Manage team members, tags, project categories, membership renewals, branding, organisation bank details, and application updates.') }}</flux:text>
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

    @error('role')
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300">
            {{ $message }}
        </div>
    @enderror

    @error('update')
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
            wire:click="setTab('roles')"
            @class([
                'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'roles',
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'roles',
            ])
        >
            <span class="inline-flex items-center justify-center gap-2">
                <x-material-icon name="shield_person" class="text-[1.125rem]" />
                {{ __('Roles') }}
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
            wire:click="setTab('categories')"
            @class([
                'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'categories',
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'categories',
            ])
        >
            <span class="inline-flex items-center justify-center gap-2">
                <x-material-icon name="category" class="text-[1.125rem]" />
                {{ __('Categories') }}
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
        <button
            type="button"
            wire:click="setTab('updates')"
            @class([
                'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'updates',
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'updates',
            ])
        >
            <span class="inline-flex items-center justify-center gap-2">
                <x-material-icon name="system_update_alt" class="text-[1.125rem]" />
                {{ __('Updates') }}
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
                            <th class="px-5 py-3">{{ __('Roles') }}</th>
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
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($user->roles as $role)
                                            <span @class([
                                                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                'bg-huddle-primary/15 text-huddle-primary' => strcasecmp($role->name, 'admin') === 0,
                                                'bg-huddle-comp/20 text-green-800 dark:text-huddle-comp' => strcasecmp($role->name, 'Mentor') === 0,
                                                'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => ! in_array(strtolower($role->name), ['admin', 'mentor'], true),
                                            ])>
                                                {{ str($role->name)->headline() }}
                                            </span>
                                        @empty
                                            <span class="text-zinc-400">—</span>
                                        @endforelse
                                    </div>
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
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            wire:click="resendInvitation({{ $user->id }})"
                                            wire:confirm="{{ __('Resend invitation email to :email? Any previous invite link will stop working.', ['email' => $user->email]) }}"
                                            :disabled="$user->id === auth()->id()"
                                            title="{{ __('Resend invite') }}"
                                        >
                                            <x-material-icon name="mail" class="text-[1rem]" />
                                        </flux:button>
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

    @if ($activeTab === 'roles')
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div>
                    <flux:heading size="lg">{{ __('Roles') }}</flux:heading>
                    <flux:text class="mt-1 text-sm">{{ __('Define roles and which capabilities they grant. Users can hold multiple roles.') }}</flux:text>
                </div>
                <flux:button variant="primary" wire:click="openCreateRoleModal">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="add" class="text-[1.25rem]" />
                        {{ __('Add role') }}
                    </span>
                </flux:button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                        <tr>
                            <th class="px-5 py-3">{{ __('Role') }}</th>
                            <th class="px-5 py-3 hidden md:table-cell">{{ __('Description') }}</th>
                            <th class="px-5 py-3">{{ __('Permissions') }}</th>
                            <th class="px-5 py-3">{{ __('Users') }}</th>
                            <th class="px-5 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->roles as $role)
                            <tr wire:key="role-{{ $role->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">
                                    {{ str($role->name)->headline() }}
                                    @if ($role->is_system)
                                        <span class="ms-2 rounded-full bg-zinc-500/15 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:text-zinc-300">{{ __('System') }}</span>
                                    @endif
                                </td>
                                <td class="hidden px-5 py-3 text-zinc-600 dark:text-zinc-300 md:table-cell">
                                    {{ $role->description ?: '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    @if (strcasecmp($role->name, 'admin') === 0)
                                        <span class="text-xs text-zinc-500">{{ __('All capabilities') }}</span>
                                    @elseif ($role->permissions->isEmpty())
                                        <span class="text-zinc-400">—</span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($role->permissions as $permission)
                                                <span class="rounded-full bg-huddle-primary/10 px-2 py-0.5 text-xs text-huddle-primary">{{ $permission->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $role->users_count }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-1">
                                        <flux:button size="sm" variant="ghost" wire:click="openEditRoleModal({{ $role->id }})">
                                            <x-material-icon name="edit" class="text-[1rem]" />
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            wire:click="deleteRole({{ $role->id }})"
                                            wire:confirm="{{ __('Delete role :name?', ['name' => $role->name]) }}"
                                            :disabled="$role->is_system || $role->users_count > 0"
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

    @if ($activeTab === 'categories')
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div>
                    <flux:heading size="lg">{{ __('Project categories') }}</flux:heading>
                    <flux:text class="mt-1 text-sm">{{ __('Labels for projects (woodshop, H&S, metalwork, etc.).') }}</flux:text>
                </div>
                <flux:button variant="primary" wire:click="openCreateCategoryModal">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="add" class="text-[1.25rem]" />
                        {{ __('Add category') }}
                    </span>
                </flux:button>
            </div>

            @if ($this->projectCategories->isEmpty())
                <div class="px-5 py-12 text-center">
                    <flux:text>{{ __('No categories yet. Create categories to assign them to projects.') }}</flux:text>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                            <tr>
                                <th class="px-5 py-3">{{ __('Category') }}</th>
                                <th class="px-5 py-3 hidden md:table-cell">{{ __('Description') }}</th>
                                <th class="px-5 py-3">{{ __('Projects') }}</th>
                                <th class="px-5 py-3 text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($this->projectCategories as $category)
                                <tr wire:key="category-{{ $category->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-5 py-3">
                                        <x-user-flag-badge :name="$category->name" />
                                    </td>
                                    <td class="hidden max-w-md px-5 py-3 text-zinc-600 dark:text-zinc-300 md:table-cell">
                                        {{ $category->description ?: '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-zinc-600 dark:text-zinc-300">{{ $category->projects_count }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex justify-end gap-1">
                                            <flux:button size="sm" variant="ghost" wire:click="openEditCategoryModal({{ $category->id }})">
                                                <x-material-icon name="edit" class="text-[1rem]" />
                                            </flux:button>
                                            <flux:button
                                                size="sm"
                                                variant="danger"
                                                wire:click="deleteCategory({{ $category->id }})"
                                                wire:confirm="{{ __('Delete category :name? It will be removed from all projects.', ['name' => $category->name]) }}"
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

    @if ($activeTab === 'updates')
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
            <div>
                <flux:heading size="lg" class="inline-flex items-center gap-2">
                    <x-material-icon name="system_update_alt" class="text-[1.5rem] text-huddle-primary" />
                    {{ __('Application updates') }}
                </flux:heading>
                <flux:text class="mt-1 text-sm">
                    {{ __('Check GitHub for a newer release package, then apply database migrations after you upload the new files.') }}
                </flux:text>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Installed version') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                        {{ $installedVersion ?? __('Unknown (no VERSION file)') }}
                    </dd>
                </div>
                <div class="rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Latest release') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                        @if ($latestReleaseTag)
                            {{ $latestReleaseTag }}
                            @if ($updateAvailable)
                                <span class="ml-2 text-xs font-semibold text-amber-700 dark:text-amber-300">{{ __('Update available') }}</span>
                            @endif
                        @else
                            {{ __('Not checked yet') }}
                        @endif
                    </dd>
                </div>
            </dl>

            @if ($updateCheckMessage)
                <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">{{ $updateCheckMessage }}</p>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                <flux:button type="button" variant="filled" wire:click="checkForUpdates" wire:loading.attr="disabled">
                    <span class="inline-flex items-center gap-2" wire:loading.remove wire:target="checkForUpdates">
                        <x-material-icon name="travel_explore" class="text-[1.25rem]" />
                        {{ __('Check for updates') }}
                    </span>
                    <span class="inline-flex items-center gap-2" wire:loading wire:target="checkForUpdates">
                        {{ __('Checking…') }}
                    </span>
                </flux:button>

                @if ($latestReleaseZipUrl)
                    <flux:button
                        type="button"
                        variant="ghost"
                        :href="$latestReleaseZipUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <span class="inline-flex items-center gap-2">
                            <x-material-icon name="download" class="text-[1.25rem]" />
                            {{ __('Download release zip') }}
                        </span>
                    </flux:button>
                @elseif ($latestReleaseUrl)
                    <flux:button
                        type="button"
                        variant="ghost"
                        :href="$latestReleaseUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <span class="inline-flex items-center gap-2">
                            <x-material-icon name="open_in_new" class="text-[1.25rem]" />
                            {{ __('View on GitHub') }}
                        </span>
                    </flux:button>
                @endif
            </div>

            <div class="mt-8 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                <flux:heading size="base">{{ __('Apply database update') }}</flux:heading>
                <flux:text class="mt-1 text-sm">
                    {{ __('After uploading a new release (keep .env and storage/), run migrations and seeders here. Restart queue workers afterwards if you use them.') }}
                </flux:text>

                <div class="mt-4">
                    <flux:button
                        type="button"
                        variant="primary"
                        wire:click="applyDatabaseUpdate"
                        wire:confirm="{{ __('Run database migrations and seeders now?') }}"
                        wire:loading.attr="disabled"
                    >
                        <span class="inline-flex items-center gap-2" wire:loading.remove wire:target="applyDatabaseUpdate">
                            <x-material-icon name="database" class="text-[1.25rem]" />
                            {{ __('Update database') }}
                        </span>
                        <span class="inline-flex items-center gap-2" wire:loading wire:target="applyDatabaseUpdate">
                            {{ __('Updating…') }}
                        </span>
                    </flux:button>
                </div>

                @if (count($updateOutput) > 0)
                    <pre class="mt-4 overflow-x-auto rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-xs text-zinc-700 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">{{ implode("\n", $updateOutput) }}</pre>
                @endif
            </div>
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

            <div>
                <flux:label>{{ __('Roles') }}</flux:label>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    @foreach ($this->roles as $role)
                        <label class="flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                            <input
                                type="checkbox"
                                wire:model="assignedRoleIds"
                                value="{{ $role->id }}"
                                class="rounded border-zinc-300 text-huddle-primary focus:ring-huddle-primary"
                            >
                            <span>{{ str($role->name)->headline() }}</span>
                        </label>
                    @endforeach
                </div>
                @error('assignedRoleIds')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

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

    <flux:modal wire:model="showRoleModal" class="md:max-w-2xl">
        <form wire:submit="saveRole" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingRoleId ? __('Edit role') : __('Add role') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Choose which capabilities this role grants.') }}</flux:text>
            </div>

            <flux:input
                wire:model="role_name"
                :label="__('Name')"
                required
                :disabled="$editingRoleId && $this->roles->firstWhere('id', $editingRoleId)?->is_system"
            />
            <flux:textarea wire:model="role_description" :label="__('Description')" rows="2" />

            @php
                $editingRole = $editingRoleId ? $this->roles->firstWhere('id', $editingRoleId) : null;
                $isAdminRole = $editingRole && strcasecmp($editingRole->name, 'admin') === 0;
            @endphp

            @if ($isAdminRole)
                <flux:text class="text-sm">{{ __('The admin role always has full access and does not need individual permissions.') }}</flux:text>
            @else
                <div>
                    <flux:label>{{ __('Permissions') }}</flux:label>
                    <div class="mt-2 grid gap-2">
                        @foreach ($this->permissions as $permission)
                            <label class="flex items-start gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                                <input
                                    type="checkbox"
                                    wire:model="assignedPermissionIds"
                                    value="{{ $permission->id }}"
                                    class="mt-0.5 rounded border-zinc-300 text-huddle-primary focus:ring-huddle-primary"
                                >
                                <span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $permission->name }}</span>
                                    @if ($permission->description)
                                        <span class="block text-xs text-zinc-500">{{ $permission->description }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeRoleModal">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingRoleId ? __('Save changes') : __('Create role') }}
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

            <x-member-select
                :users="$this->users"
                :selected-id="$membership_assignment_user_id"
                wire-model="membership_assignment_user_id"
                :label="__('Member')"
                :placeholder="__('Select a member…')"
                required
            />

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

    <flux:modal wire:model="showCategoryModal" class="md:max-w-lg">
        <form wire:submit="saveCategory" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingCategoryId ? __('Edit category') : __('Add category') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Categories help group projects (woodshop, H&S, and similar).') }}</flux:text>
            </div>

            <flux:input wire:model="category_name" :label="__('Name')" required />
            <flux:textarea wire:model="category_description" :label="__('Description (optional)')" rows="3" />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeCategoryModal">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingCategoryId ? __('Save changes') : __('Create category') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
