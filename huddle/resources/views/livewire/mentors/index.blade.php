<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl" class="inline-flex items-center gap-2">
            <x-material-icon name="school" class="text-[1.75rem] text-huddle-primary" />
            {{ __('Mentors') }}
        </flux:heading>
        <flux:text class="mt-1">{{ __('Manage accreditations and assign them to community members.') }}</flux:text>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-huddle-comp/40 bg-huddle-comp/10 px-4 py-3 text-sm text-zinc-800 dark:text-zinc-200">
            {{ session('status') }}
        </div>
    @endif

    <nav class="flex gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800/60" aria-label="{{ __('Mentor sections') }}">
        <button
            type="button"
            wire:click="setTab('accreditations')"
            @class([
                'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'accreditations',
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'accreditations',
            ])
        >
            <span class="inline-flex items-center justify-center gap-2">
                <x-material-icon name="verified" class="text-[1.125rem]" />
                {{ __('Accreditations') }}
            </span>
        </button>
        <button
            type="button"
            wire:click="setTab('assignments')"
            @class([
                'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'assignments',
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'assignments',
            ])
        >
            <span class="inline-flex items-center justify-center gap-2">
                <x-material-icon name="assignment_ind" class="text-[1.125rem]" />
                {{ __('Assignments') }}
            </span>
        </button>
    </nav>

    @if ($activeTab === 'accreditations')
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div>
                    <flux:heading size="lg">{{ __('Accreditation types') }}</flux:heading>
                    <flux:text class="mt-1 text-sm">{{ __(':count accreditations', ['count' => $this->accreditations->count()]) }}</flux:text>
                </div>
                <flux:button variant="primary" wire:click="openCreateAccreditationModal">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="add" class="text-[1.25rem]" />
                        {{ __('Add accreditation') }}
                    </span>
                </flux:button>
            </div>

            @if ($this->accreditations->isEmpty())
                <div class="px-5 py-12 text-center">
                    <flux:text>{{ __('No accreditations yet. Create one to assign to members.') }}</flux:text>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                            <tr>
                                <th class="px-5 py-3">{{ __('Name') }}</th>
                                <th class="px-5 py-3 hidden md:table-cell">{{ __('Description') }}</th>
                                <th class="px-5 py-3">{{ __('Status') }}</th>
                                <th class="px-5 py-3">{{ __('Assigned') }}</th>
                                <th class="px-5 py-3 text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($this->accreditations as $accreditation)
                                <tr wire:key="accreditation-{{ $accreditation->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">{{ $accreditation->name }}</td>
                                    <td class="hidden max-w-md px-5 py-3 text-zinc-600 dark:text-zinc-300 md:table-cell">
                                        {{ $accreditation->description ?: '—' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            'bg-huddle-comp/20 text-huddle-comp' => $accreditation->is_active,
                                            'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => ! $accreditation->is_active,
                                        ])>
                                            {{ $accreditation->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-zinc-600 dark:text-zinc-300">{{ $accreditation->assignments_count }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex justify-end gap-1">
                                            <flux:button size="sm" variant="ghost" wire:click="openEditAccreditationModal({{ $accreditation->id }})">
                                                <x-material-icon name="edit" class="text-[1rem]" />
                                            </flux:button>
                                            <flux:button
                                                size="sm"
                                                variant="danger"
                                                wire:click="deleteAccreditation({{ $accreditation->id }})"
                                                wire:confirm="{{ __('Delete :name? All member assignments for this accreditation will be removed.', ['name' => $accreditation->name]) }}"
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

    @if ($activeTab === 'assignments')
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div>
                    <flux:heading size="lg">{{ __('Member assignments') }}</flux:heading>
                    <flux:text class="mt-1 text-sm">{{ __(':count assignments', ['count' => $this->assignments->count()]) }}</flux:text>
                </div>
                <flux:button
                    variant="primary"
                    wire:click="openCreateAssignmentModal"
                    :disabled="$this->accreditations->where('is_active', true)->isEmpty()"
                >
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="person_add" class="text-[1.25rem]" />
                        {{ __('Assign accreditation') }}
                    </span>
                </flux:button>
            </div>

            @if ($this->assignments->isEmpty())
                <div class="px-5 py-12 text-center">
                    <flux:text>{{ __('No assignments yet. Assign an accreditation to a member.') }}</flux:text>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                            <tr>
                                <th class="px-5 py-3">{{ __('Member') }}</th>
                                <th class="px-5 py-3">{{ __('Accreditation') }}</th>
                                <th class="px-5 py-3">{{ __('Status') }}</th>
                                <th class="px-5 py-3 hidden sm:table-cell">{{ __('Updated') }}</th>
                                <th class="px-5 py-3 text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($this->assignments as $assignment)
                                <tr wire:key="assignment-{{ $assignment->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <x-user-avatar :user="$assignment->user" size="sm" />
                                            <div>
                                                <x-user-link :user="$assignment->user" class="text-zinc-900 dark:text-white" />
                                                <div class="text-xs text-zinc-500">{{ $assignment->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">{{ $assignment->accreditation->name }}</td>
                                    <td class="px-5 py-3">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            'bg-huddle-comp/20 text-huddle-comp' => $assignment->is_active,
                                            'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => ! $assignment->is_active,
                                        ])>
                                            {{ $assignment->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                    <td class="hidden px-5 py-3 text-zinc-500 sm:table-cell">{{ $assignment->updated_at->format('j M Y') }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex justify-end gap-1">
                                            <flux:button size="sm" variant="ghost" wire:click="openEditAssignmentModal({{ $assignment->id }})">
                                                <x-material-icon name="edit" class="text-[1rem]" />
                                            </flux:button>
                                            <flux:button
                                                size="sm"
                                                variant="danger"
                                                wire:click="deleteAssignment({{ $assignment->id }})"
                                                wire:confirm="{{ __('Remove :accreditation from :name?', ['accreditation' => $assignment->accreditation->name, 'name' => $assignment->user->name]) }}"
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

    <flux:modal wire:model="showAccreditationModal" class="md:max-w-lg">
        <form wire:submit="saveAccreditation" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingAccreditationId ? __('Edit accreditation') : __('Add accreditation') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Define a type of accreditation members can earn.') }}</flux:text>
            </div>

            <flux:input wire:model="accreditation_name" :label="__('Name')" required />
            <flux:textarea wire:model="accreditation_description" :label="__('Description (optional)')" rows="3" />
            <flux:switch wire:model="accreditation_is_active" :label="__('Active')" />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeAccreditationModal">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingAccreditationId ? __('Save changes') : __('Create accreditation') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal wire:model="showAssignmentModal" class="md:max-w-lg">
        <form wire:submit="saveAssignment" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingAssignmentId ? __('Edit assignment') : __('Assign accreditation') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Link a member to an accreditation type.') }}</flux:text>
            </div>

            <flux:select wire:model="assignment_user_id" :label="__('Member')" required>
                <flux:select.option value="">{{ __('Select a member…') }}</flux:select.option>
                @foreach ($this->users as $user)
                    <flux:select.option :value="$user->id">{{ $user->name }} ({{ $user->email }})</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="assignment_accreditation_id" :label="__('Accreditation')" required>
                <flux:select.option value="">{{ __('Select an accreditation…') }}</flux:select.option>
                @foreach ($this->accreditations as $accreditation)
                    <flux:select.option :value="$accreditation->id">{{ $accreditation->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:switch wire:model="assignment_is_active" :label="__('Active')" />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeAssignmentModal">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingAssignmentId ? __('Save changes') : __('Assign') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
