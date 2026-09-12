<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl" class="inline-flex items-center gap-2.5">
                <span class="flex size-10 items-center justify-center rounded-xl bg-huddle-primary/15 text-huddle-primary">
                    <x-material-icon name="folder" class="text-[1.5rem]" />
                </span>
                {{ __('Projects') }}
            </flux:heading>
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
    <div class="rounded-xl border border-zinc-200 border-s-4 border-s-huddle-primary bg-zinc-50/80 p-4 dark:border-zinc-700 dark:border-s-huddle-primary dark:bg-zinc-800/40 sm:p-5">
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
                <flux:select.option value="active">{{ __('Active (hide completed/cancelled)') }}</flux:select.option>
                <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
                @foreach (\App\Models\Project::STATUSES as $status)
                    <flux:select.option :value="$status">{{ str($status)->headline() }}</flux:select.option>
                @endforeach
            </flux:select>

            <x-member-select
                :users="$this->leaders"
                :selected-id="$leaderFilter"
                wire-model="leaderFilter"
                :label="__('Leader')"
                :placeholder="__('All leaders')"
                :allow-clear="true"
                :clear-label="__('All leaders')"
                empty-value=""
                :show-email="false"
            />

            <flux:select wire:model.live="categoryFilter" :label="__('Category')">
                <flux:select.option value="">{{ __('All categories') }}</flux:select.option>
                @foreach ($this->categories as $category)
                    <flux:select.option :value="$category->id">{{ $category->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="customerFilter" :label="__('Customer')">
                <flux:select.option value="">{{ __('All customers') }}</flux:select.option>
                @foreach ($this->customers as $customer)
                    <flux:select.option :value="$customer->id">{{ $customer->name }}</flux:select.option>
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

    @if ($this->newestProjects->isNotEmpty())
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="sm" class="inline-flex items-center gap-2">
                    <x-material-icon name="fiber_new" class="text-[1.25rem] text-huddle-primary" />
                    {{ __('Newest projects') }}
                </flux:heading>
                <flux:text class="text-xs text-zinc-500">{{ __('Up to 4 most recent') }}</flux:text>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($this->newestProjects as $project)
                    <a
                        href="{{ route('projects.show', $project) }}"
                        wire:navigate
                        wire:key="newest-project-{{ $project->id }}"
                        class="rounded-xl border border-zinc-200 border-s-4 border-s-huddle-primary bg-white p-4 shadow-sm transition hover:border-huddle-primary/40 hover:bg-teal-50/40 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-teal-950/20"
                    >
                        <p class="font-mono text-xs font-semibold tracking-wide text-huddle-primary">#{{ $project->formattedId() }}</p>
                        <p class="mt-1 truncate font-medium text-zinc-900 dark:text-white">{{ $project->name }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <x-project-status-badge :status="$project->project_status" />
                            @if ($project->customer)
                                <span class="truncate text-xs text-zinc-500">{{ $project->customer->name }}</span>
                            @endif
                        </div>
                        @if ($project->categories->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($project->categories->take(2) as $category)
                                    <x-user-flag-badge :name="$category->name" wire:key="newest-{{ $project->id }}-cat-{{ $category->id }}" />
                                @endforeach
                                @if ($project->categories->count() > 2)
                                    <span class="text-xs text-zinc-400">+{{ $project->categories->count() - 2 }}</span>
                                @endif
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:shadow-none">
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
                    <flux:table.column class="w-20 border-s-4 border-s-transparent">
                        {{ __('ID') }}
                    </flux:table.column>
                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'name'"
                        :direction="$sortBy === 'name' ? $sortDirection : null"
                        wire:click="sort('name')"
                        class="min-w-[12rem]"
                    >
                        {{ __('Project') }}
                    </flux:table.column>
                    <flux:table.column class="hidden min-w-[8rem] md:table-cell">
                        {{ __('Categories') }}
                    </flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">
                        {{ __('Activity') }}
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
                        :sorted="$sortBy === 'volunteers'"
                        :direction="$sortBy === 'volunteers' ? $sortDirection : null"
                        wire:click="sort('volunteers')"
                        class="hidden md:table-cell"
                    >
                        {{ __('Volunteers') }}
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
                        @php
                            $statusAccent = match ($project->project_status) {
                                'outstanding' => 'border-s-huddle-alt',
                                'in-progress' => 'border-s-huddle-primary',
                                'completed' => 'border-s-huddle-comp',
                                'cancelled' => 'border-s-huddle-accent',
                                'archived' => 'border-s-zinc-300 dark:border-s-zinc-600',
                                default => 'border-s-zinc-300 dark:border-s-zinc-600',
                            };
                        @endphp
                        <flux:table.row
                            wire:key="project-{{ $project->id }}"
                            class="cursor-pointer transition hover:bg-zinc-50 dark:hover:bg-zinc-800/60"
                            wire:click="viewProject({{ $project->id }})"
                        >
                            <flux:table.cell @class(['border-s-4 ps-3 font-mono text-xs font-semibold tracking-wide text-huddle-primary', $statusAccent])>
                                #{{ $project->formattedId() }}
                            </flux:table.cell>

                            <flux:table.cell variant="strong" class="max-w-md">
                                <div class="min-w-0 space-y-1">
                                    <p class="truncate font-medium text-zinc-900 dark:text-white">
                                        {{ $project->name }}
                                    </p>
                                    @if ($project->customer)
                                        <p class="truncate text-xs text-zinc-500">
                                            {{ $project->customer->name }}
                                            <span class="text-zinc-400">·</span>
                                            {{ $project->customer->typeLabel() }}
                                        </p>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs sm:hidden">
                                        <span class="inline-flex items-center gap-1 text-zinc-500">
                                            <x-material-icon name="chat_bubble" class="text-[0.875rem]" />
                                            {{ $project->comments_count }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-zinc-500">
                                            <x-material-icon name="group" class="text-[0.875rem]" />
                                            {{ $project->volunteers_count }}
                                        </span>
                                        @if ($project->categories->isNotEmpty())
                                            <span class="truncate text-zinc-500">
                                                {{ $project->categories->pluck('name')->join(', ') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="hidden md:table-cell">
                                @if ($project->categories->isEmpty())
                                    <span class="text-zinc-400">—</span>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($project->categories as $category)
                                            <x-user-flag-badge :name="$category->name" wire:key="project-{{ $project->id }}-cat-{{ $category->id }}" />
                                        @endforeach
                                    </div>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="hidden sm:table-cell">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    <flux:tooltip :content="__('Comments')" position="top">
                                        <span class="inline-flex items-center gap-1">
                                            <x-material-icon name="chat_bubble" class="text-[0.875rem] text-huddle-primary" />
                                            {{ $project->comments_count }}
                                        </span>
                                    </flux:tooltip>
                                    <flux:tooltip :content="__('Volunteers')" position="top">
                                        <span class="inline-flex items-center gap-1">
                                            <x-material-icon name="group" class="text-[0.875rem] text-huddle-accent" />
                                            {{ $project->volunteers_count }}
                                        </span>
                                    </flux:tooltip>
                                    <flux:tooltip :content="__('Images')" position="top">
                                        <span class="inline-flex items-center gap-1">
                                            <x-material-icon name="image" class="text-[0.875rem] text-huddle-alt" />
                                            {{ $project->images_count }}
                                        </span>
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                    <x-material-icon name="person" class="text-[1rem] text-huddle-primary" />
                                    {{ $project->leader->name }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <x-project-status-badge :status="$project->project_status" />
                            </flux:table.cell>

                            <flux:table.cell class="hidden md:table-cell">
                                @if ($project->volunteer_required)
                                    <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-huddle-accent/20 px-2.5 py-0.5 text-xs font-medium text-fuchsia-900 dark:bg-huddle-accent/15 dark:text-huddle-accent">
                                        <x-material-icon name="volunteer_activism" class="text-[0.875rem]" />
                                        {{ __('Wanted') }}
                                    </span>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="hidden whitespace-nowrap md:table-cell">
                                @if ($project->due_date)
                                    <time
                                        datetime="{{ $project->due_date->toDateString() }}"
                                        @class([
                                            'inline-flex items-center gap-1',
                                            'font-medium text-red-600 dark:text-red-400' => $project->isOverdue(),
                                            'text-huddle-alt' => ! $project->isOverdue(),
                                        ])
                                    >
                                        <x-material-icon
                                            name="event"
                                            @class([
                                                'text-[1rem]',
                                                'text-red-500' => $project->isOverdue(),
                                                'text-huddle-alt' => ! $project->isOverdue(),
                                            ])
                                        />
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
                                    datetime="{{ $project->updated_at->toIso8601String() }}"
                                    title="{{ $project->updated_at->format('j F Y, H:i') }}"
                                    class="inline-flex items-center gap-1 text-zinc-600 dark:text-zinc-400"
                                >
                                    <x-material-icon name="update" class="text-[1rem] text-huddle-primary" />
                                    {{ $project->updated_at->format('j M Y') }}
                                </time>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>

    <flux:modal wire:model="showCreateModal" class="md:max-w-3xl">
        <form wire:submit="createProject" class="flex max-h-[85vh] flex-col">
            <div class="space-y-6 overflow-y-auto pe-1">
                <div>
                    <flux:heading size="lg" class="inline-flex items-center gap-2">
                        <x-material-icon name="create_new_folder" class="text-[1.5rem] text-huddle-primary" />
                        {{ $createStep === 2 ? __('New customer') : __('New project') }}
                    </flux:heading>
                    <flux:text class="mt-1">
                        {{ $createStep === 2
                            ? __('Add the customer details, then we will create the project.')
                            : __('Add a community project for your team to track.') }}
                    </flux:text>
                    <div class="mt-3 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-zinc-500">
                        <span @class([
                            'rounded-full px-2.5 py-1',
                            'bg-huddle-primary/15 text-huddle-primary' => $createStep === 1,
                            'bg-zinc-100 text-zinc-500 dark:bg-zinc-800' => $createStep !== 1,
                        ])>{{ __('1. Project') }}</span>
                        <span class="text-zinc-300 dark:text-zinc-600">→</span>
                        <span @class([
                            'rounded-full px-2.5 py-1',
                            'bg-huddle-primary/15 text-huddle-primary' => $createStep === 2,
                            'bg-zinc-100 text-zinc-500 dark:bg-zinc-800' => $createStep !== 2,
                        ])>{{ __('2. New customer') }}</span>
                    </div>
                </div>

                @if ($createStep === 1)
                    <flux:input wire:model="name" :label="__('Name')" required />
                    <flux:textarea wire:model="description" :label="__('Description')" rows="5" required />

                    <div class="grid gap-4 sm:grid-cols-2">
                        @if (auth()->user()->can('assignLeader', \App\Models\Project::class))
                            <x-member-select
                                :users="$this->users"
                                :selected-id="$leader_id"
                                wire-model="leader_id"
                                :label="__('Project leader')"
                                :show-email="false"
                            />
                        @else
                            <flux:text class="self-center text-sm text-zinc-600 dark:text-zinc-300">
                                {{ __('You will be assigned as the project leader.') }}
                            </flux:text>
                        @endif

                        <flux:select wire:model="project_status" :label="__('Status')">
                            @foreach (\App\Models\Project::STATUSES as $status)
                                <flux:select.option :value="$status">{{ str($status)->headline() }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:input wire:model="due_date" type="date" :label="__('Due date (optional)')" />

                        <div class="space-y-2 sm:col-span-2">
                            <x-customer-select
                                :customers="$this->customers"
                                :selected-id="$customer_id"
                                wire-model="customer_id"
                                :label="__('Customer (optional)')"
                            />
                            <flux:button type="button" variant="ghost" size="sm" wire:click="goToNewCustomerStep" class="!px-0">
                                <span class="inline-flex items-center gap-1.5 text-huddle-primary">
                                    <x-material-icon name="person_add" class="text-[1.125rem]" />
                                    {{ __('Add new customer instead') }}
                                </span>
                            </flux:button>
                        </div>
                    </div>

                    <flux:checkbox wire:model="volunteer_required" :label="__('Volunteers required')" />

                    @if ($this->categories->isNotEmpty())
                        <x-assign-select
                            :options="$this->categories"
                            :selected-ids="$assignedCategoryIds"
                            wire-model="assignedCategoryIds"
                            :label="__('Categories')"
                            :placeholder="__('Select categories…')"
                            :search-placeholder="__('Search categories…')"
                            :empty-message="__('No matching categories.')"
                            error-name="assignedCategoryIds"
                        />
                    @endif
                @else
                    <flux:text class="rounded-lg border border-zinc-200 bg-zinc-50/80 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800/40">
                        {{ __('Project:') }} <span class="font-medium text-zinc-900 dark:text-white">{{ $name ?: __('Untitled') }}</span>
                    </flux:text>

                    <flux:input wire:model="new_customer_name" :label="__('Name')" required />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select wire:model="new_customer_type" :label="__('Type')" required>
                            @foreach (\App\Models\Customer::TYPES as $type)
                                <flux:select.option :value="$type">{{ str($type)->headline() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:input wire:model="new_customer_telephone" type="tel" :label="__('Telephone')" />
                    </div>

                    <flux:input wire:model="new_customer_email" type="email" :label="__('Email')" />
                    <flux:textarea wire:model="new_customer_address" :label="__('Address')" rows="3" />
                @endif
            </div>

            <div class="mt-6 flex shrink-0 justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                @if ($createStep === 2)
                    <flux:button type="button" variant="ghost" wire:click="backToProjectStep">
                        {{ __('Back') }}
                    </flux:button>
                @else
                    <flux:button type="button" variant="ghost" wire:click="closeCreateModal">
                        {{ __('Cancel') }}
                    </flux:button>
                @endif
                <flux:button type="submit" variant="primary">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="check" class="text-[1.25rem]" />
                        {{ $createStep === 2 ? __('Create customer & project') : __('Create project') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
