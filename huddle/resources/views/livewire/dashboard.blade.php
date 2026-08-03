<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Welcome back, :name', ['name' => $this->firstName]) }}</flux:heading>
            <flux:text class="mt-1">{{ __('Your projects, updates, and upcoming events.') }}</flux:text>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button variant="ghost" :href="route('events.index')" wire:navigate>
                <span class="inline-flex items-center gap-2">
                    <x-material-icon name="event" class="text-[1.25rem]" />
                    {{ __('Events') }}
                </span>
            </flux:button>
            <flux:button variant="primary" :href="route('projects.index')" wire:navigate>
                <span class="inline-flex items-center gap-2">
                    <x-material-icon name="folder" class="text-[1.25rem]" />
                    {{ __('Projects') }}
                </span>
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Active projects') }}</flux:text>
            <p class="mt-3 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $this->projectStats['active'] }}</p>
            <flux:text class="mt-1 text-xs">{{ __('You lead, created, or volunteer on') }}</flux:text>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Leading') }}</flux:text>
            <p class="mt-3 text-3xl font-semibold text-huddle-primary">{{ $this->projectStats['leading'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Volunteering') }}</flux:text>
            <p class="mt-3 text-3xl font-semibold text-huddle-accent">{{ $this->projectStats['volunteering'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Updated recently') }}</flux:text>
            <p class="mt-3 text-3xl font-semibold text-huddle-comp">{{ $this->projectStats['updated'] }}</p>
            <flux:text class="mt-1 text-xs">{{ __('Last :days days', ['days' => \App\Livewire\Dashboard::UPDATE_LOOKBACK_DAYS]) }}</flux:text>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Your project updates') }}</flux:heading>
                <flux:link :href="route('projects.index')" wire:navigate>{{ __('View all') }}</flux:link>
            </div>
            @if ($this->projectUpdates->isEmpty())
                <div class="flex flex-col items-center gap-2 px-5 py-8 text-center">
                    <flux:text>{{ __('No recent updates on your projects.') }}</flux:text>
                    <flux:button variant="primary" size="sm" :href="route('projects.index')" wire:navigate>{{ __('Browse projects') }}</flux:button>
                </div>
            @else
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($this->projectUpdates as $project)
                        <a
                            href="{{ route('projects.show', $project) }}"
                            wire:navigate
                            class="flex items-start justify-between gap-3 px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            wire:key="update-project-{{ $project->id }}"
                        >
                            <div class="min-w-0">
                                <p class="truncate font-medium text-zinc-900 dark:text-white">{{ $project->name }}</p>
                                <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-zinc-500">
                                    <span>{{ $this->involvementLabel($project) }}</span>
                                    <span aria-hidden="true">·</span>
                                    <span>{{ __('Updated') }} {{ $project->updated_at->diffForHumans() }}</span>
                                </p>
                                @if ($project->categories->isNotEmpty())
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @foreach ($project->categories as $category)
                                            <x-user-flag-badge :name="$category->name" wire:key="update-{{ $project->id }}-cat-{{ $category->id }}" />
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <x-project-status-badge :status="$project->project_status" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <div>
                    <flux:heading size="lg">{{ __('Upcoming events') }}</flux:heading>
                    @if ($this->eventStats['volunteering'] > 0)
                        <flux:text class="mt-0.5 text-xs">
                            {{ trans_choice(':count you are volunteering on|:count you are volunteering on', $this->eventStats['volunteering'], ['count' => $this->eventStats['volunteering']]) }}
                        </flux:text>
                    @endif
                </div>
                <flux:link :href="route('events.index')" wire:navigate>{{ __('View all') }}</flux:link>
            </div>
            @if ($this->upcomingEvents->isEmpty())
                <div class="flex flex-col items-center gap-2 px-5 py-8 text-center">
                    <flux:text>{{ __('No upcoming events.') }}</flux:text>
                    <flux:button variant="primary" size="sm" :href="route('events.index')" wire:navigate>{{ __('Browse events') }}</flux:button>
                </div>
            @else
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($this->upcomingEvents as $event)
                        <a href="{{ route('events.show', $event) }}" wire:navigate class="block px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50" wire:key="upcoming-event-{{ $event->id }}">
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $event->name }}</p>
                            <p class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-zinc-500">
                                <span class="inline-flex items-center gap-1">
                                    <x-material-icon name="schedule" class="inline !text-[0.875rem]" />
                                    {{ $event->start_time->format('j M Y, H:i') }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <x-material-icon name="location_on" class="inline !text-[0.875rem]" />
                                    {{ $event->location }}
                                </span>
                            </p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Your projects by category') }}</flux:heading>
            <flux:link :href="route('projects.index')" wire:navigate>{{ __('Browse all') }}</flux:link>
        </div>

        @if ($this->projectsByCategory->isEmpty())
            <div class="flex flex-col items-center gap-2 px-5 py-10 text-center">
                <flux:text>{{ __('You are not on any active projects yet.') }}</flux:text>
                <flux:button variant="primary" size="sm" :href="route('projects.index')" wire:navigate>{{ __('Find a project') }}</flux:button>
            </div>
        @else
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($this->projectsByCategory as $categoryName => $projects)
                    <div class="px-5 py-4" wire:key="dash-cat-{{ md5($categoryName) }}">
                        <div class="mb-3 flex items-center gap-2">
                            <x-user-flag-badge :name="$categoryName" />
                            <flux:text class="text-xs text-zinc-500">
                                {{ trans_choice(':count project|:count projects', $projects->count(), ['count' => $projects->count()]) }}
                            </flux:text>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($projects as $project)
                                <a
                                    href="{{ route('projects.show', $project) }}"
                                    wire:navigate
                                    class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-3 transition hover:border-huddle-primary/40 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50"
                                    wire:key="dash-cat-project-{{ $categoryName }}-{{ $project->id }}"
                                >
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $project->name }}</p>
                                        <p class="mt-0.5 text-xs text-zinc-500">{{ $this->involvementLabel($project) }}</p>
                                    </div>
                                    <x-project-status-badge :status="$project->project_status" />
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
