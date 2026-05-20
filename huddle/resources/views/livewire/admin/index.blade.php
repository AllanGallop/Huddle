<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl" class="inline-flex items-center gap-2">
            <x-material-icon name="admin_panel_settings" class="text-[1.75rem] text-huddle-primary" />
            {{ __('Admin') }}
        </flux:heading>
        <flux:text class="mt-1">{{ __('Manage team members, user tags, and organisation bank details.') }}</flux:text>
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
                                        <flux:avatar :name="$user->name" :initials="$user->initials()" size="sm" />
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</span>
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

    <flux:modal wire:model="showUserModal" class="md:max-w-lg">
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
                <div>
                    <flux:heading size="sm" class="mb-3">{{ __('Tags') }}</flux:heading>
                    <div class="flex flex-wrap gap-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-600">
                        @foreach ($this->flags as $flag)
                            <flux:checkbox
                                wire:model="assignedFlagIds"
                                :value="$flag->id"
                                :label="$flag->name"
                                wire:key="assign-flag-{{ $flag->id }}"
                            />
                        @endforeach
                    </div>
                    @error('assignedFlagIds')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
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
