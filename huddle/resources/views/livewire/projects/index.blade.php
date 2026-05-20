<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Projects') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Browse, filter, and sort community projects.') }}</flux:text>
        </div>
        <flux:button variant="primary" wire:click="openCreateModal">
            <span class="inline-flex items-center gap-2">
                <x-material-icon name="add" class="text-[1.25rem]" />
                {{ __('New project') }}
            </span>
        </flux:button>
    </div>

    {{-- Filters --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 sm:p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="sm" class="inline-flex items-center gap-2">
                <x-material-icon name="filter_list" class="text-[1.125rem] text-huddle-primary" />
                {{ __('Filters') }}
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

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :label="__('Search')"
                :placeholder="__('Name, description, or leader...')"
            >
                <x-slot:iconLeading>
                    <x-material-icon name="search" class="text-[1.25rem] text-zinc-400" />
                </x-slot:iconLeading>
            </flux:input>

            <flux:select wire:model.live="statusFilter" :label="__('Status')">
                <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
                @foreach (\App\Models\Project::STATUSES as $status)
                    <flux:select.option :value="$status">{{ str($status)->headline() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="leaderFilter" :label="__('Leader')">
                <flux:select.option value="">{{ __('All leaders') }}</flux:select.option>
                @foreach ($this->leaders as $leader)
                    <flux:select.option :value="$leader->id">{{ $leader->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="volunteersFilter" :label="__('Volunteers')">
                <flux:select.option value="">{{ __('Any') }}</flux:select.option>
                <flux:select.option value="required">{{ __('Volunteers needed') }}</flux:select.option>
                <flux:select.option value="not_required">{{ __('No volunteer call') }}</flux:select.option>
            </flux:select>

            @if ($this->canFilterFinancials)
                <flux:select wire:model.live="financialStatusFilter" :label="__('Financial status')">
                    <flux:select.option value="">{{ __('Any') }}</flux:select.option>
                    @foreach (\App\Models\Project::FINANCIAL_STATUSES as $status)
                        <flux:select.option :value="$status">{{ str($status)->headline() }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
            <flux:checkbox wire:model.live="mineOnly" :label="__('Only my projects')" />
            <flux:text class="text-sm text-zinc-500">
                {{ trans_choice(':count project|:count projects', $this->projects->count(), ['count' => $this->projects->count()]) }}
            </flux:text>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        @if ($this->projects->isEmpty())
            <div class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                <div class="flex size-14 items-center justify-center rounded-full bg-huddle-primary/10 text-huddle-primary">
                    <x-material-icon name="folder_open" class="text-[2rem]" />
                </div>
                <flux:heading size="lg">
                    {{ $this->hasActiveFilters ? __('No matching projects') : __('No projects yet') }}
                </flux:heading>
                <flux:text>
                    {{ $this->hasActiveFilters ? __('Try adjusting your filters or search.') : __('Create a project to get started.') }}
                </flux:text>
                @unless ($this->hasActiveFilters)
                    <flux:button variant="primary" wire:click="openCreateModal">
                        <span class="inline-flex items-center gap-2">
                            <x-material-icon name="add" class="text-[1.25rem]" />
                            {{ __('Create a project') }}
                        </span>
                    </flux:button>
                @endunless
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'name'"
                        :direction="$sortBy === 'name' ? $sortDirection : null"
                        wire:click="sort('name')"
                        class="min-w-[12rem]"
                    >
                        {{ __('Project') }}
                    </flux:table.column>
                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'leader'"
                        :direction="$sortBy === 'leader' ? $sortDirection : null"
                        wire:click="sort('leader')"
                    >
                        {{ __('Leader') }}
                    </flux:table.column>
                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'status'"
                        :direction="$sortBy === 'status' ? $sortDirection : null"
                        wire:click="sort('status')"
                    >
                        {{ __('Status') }}
                    </flux:table.column>
                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'due_date'"
                        :direction="$sortBy === 'due_date' ? $sortDirection : null"
                        wire:click="sort('due_date')"
                        class="hidden md:table-cell"
                    >
                        {{ __('Due') }}
                    </flux:table.column>
                    @if ($this->canFilterFinancials)
                        <flux:table.column
                            sortable
                            :sorted="$sortBy === 'financial'"
                            :direction="$sortBy === 'financial' ? $sortDirection : null"
                            wire:click="sort('financial')"
                            class="hidden xl:table-cell"
                        >
                            {{ __('Finance') }}
                        </flux:table.column>
                    @endif
                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'created_at'"
                        :direction="$sortBy === 'created_at' ? $sortDirection : null"
                        wire:click="sort('created_at')"
                        class="hidden lg:table-cell"
                    >
                        {{ __('Created') }}
                    </flux:table.column>
                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'updated_at'"
                        :direction="$sortBy === 'updated_at' ? $sortDirection : null"
                        wire:click="sort('updated_at')"
                        class="hidden lg:table-cell"
                    >
                        {{ __('Updated') }}
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->projects as $project)
                        <flux:table.row
                            wire:key="project-{{ $project->id }}"
                            class="cursor-pointer transition hover:bg-zinc-50 dark:hover:bg-zinc-800/60"
                            wire:click="viewProject({{ $project->id }})"
                        >
                            <flux:table.cell variant="strong" class="max-w-md">
                                <div class="min-w-0">
                                    <p class="flex items-center gap-2 truncate font-medium text-zinc-900 dark:text-white">
                                        <x-material-icon name="folder" class="shrink-0 text-[1.125rem] text-huddle-primary" />
                                        {{ $project->name }}
                                    </p>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-500">
                                        <span class="inline-flex items-center gap-1">
                                            <x-material-icon name="chat_bubble_outline" class="text-[0.875rem]" />
                                            {{ $project->comments_count }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-material-icon name="group" class="text-[0.875rem]" />
                                            {{ $project->volunteers_count }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-material-icon name="image" class="text-[0.875rem]" />
                                            {{ $project->images_count }}
                                        </span>
                                    </div>
                                    @if ($project->volunteer_required)
                                        <span class="mt-1.5 inline-flex items-center gap-1 text-xs font-medium text-huddle-accent">
                                            <x-material-icon name="volunteer_activism" class="text-[0.875rem]" />
                                            {{ __('Volunteers needed') }}
                                        </span>
                                    @endif
                                    <p class="mt-1 flex items-center gap-1 text-xs text-zinc-400 lg:hidden">
                                        <x-material-icon name="calendar_today" class="text-[0.875rem]" />
                                        {{ $project->created_at->format('j M Y') }}
                                        @if ($project->updated_at->ne($project->created_at))
                                            · {{ __('Updated') }} {{ $project->updated_at->format('j M Y') }}
                                        @endif
                                    </p>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                    <x-material-icon name="person" class="text-[1rem] text-zinc-400" />
                                    {{ $project->leader->name }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <x-project-status-badge :status="$project->project_status" />
                            </flux:table.cell>

                            <flux:table.cell class="hidden whitespace-nowrap md:table-cell">
                                @if ($project->due_date)
                                    <time
                                        datetime="{{ $project->due_date->toDateString() }}"
                                        @class([
                                            'inline-flex items-center gap-1',
                                            'font-medium text-red-600 dark:text-red-400' => $project->isOverdue(),
                                        ])
                                    >
                                        <x-material-icon name="event" class="text-[1rem] text-zinc-400" />
                                        {{ $project->due_date->format('j M Y') }}
                                    </time>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            @if ($this->canFilterFinancials)
                                <flux:table.cell class="hidden xl:table-cell">
                                    @if (auth()->user()->canManageProjectFinancials($project) && $project->financial_status)
                                        <x-financial-status-badge :status="$project->financial_status" />
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                            @endif

                            <flux:table.cell class="hidden whitespace-nowrap lg:table-cell">
                                <time
                                    datetime="{{ $project->created_at->toIso8601String() }}"
                                    title="{{ $project->created_at->format('j F Y, H:i') }}"
                                    class="inline-flex items-center gap-1"
                                >
                                    <x-material-icon name="event" class="text-[1rem] text-zinc-400" />
                                    {{ $project->created_at->format('j M Y') }}
                                </time>
                            </flux:table.cell>

                            <flux:table.cell class="hidden whitespace-nowrap lg:table-cell">
                                <time
                                    datetime="{{ $project->updated_at->toIso8601String() }}"
                                    title="{{ $project->updated_at->format('j F Y, H:i') }}"
                                    class="inline-flex items-center gap-1"
                                >
                                    <x-material-icon name="update" class="text-[1rem] text-zinc-400" />
                                    {{ $project->updated_at->format('j M Y') }}
                                </time>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>

    <flux:modal wire:model="showCreateModal" class="md:w-lg">
        <form wire:submit="createProject" class="space-y-6">
            <div>
                <flux:heading size="lg" class="inline-flex items-center gap-2">
                    <x-material-icon name="create_new_folder" class="text-[1.5rem] text-huddle-primary" />
                    {{ __('New project') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Add a community project for your team to track.') }}</flux:text>
            </div>

            <flux:input wire:model="name" :label="__('Name')" required />
            <flux:textarea wire:model="description" :label="__('Description')" rows="4" required />

            <flux:select wire:model="leader_id" :label="__('Project leader')">
                @foreach ($this->users as $user)
                    <flux:select.option :value="$user->id">{{ $user->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="project_status" :label="__('Status')">
                @foreach (\App\Models\Project::STATUSES as $status)
                    <flux:select.option :value="$status">{{ str($status)->headline() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:checkbox wire:model="volunteer_required" :label="__('Volunteers required')" />

            <flux:input wire:model="due_date" type="date" :label="__('Due date (optional)')" />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeCreateModal">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="check" class="text-[1.25rem]" />
                        {{ __('Create project') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
