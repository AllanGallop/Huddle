<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Overview of community projects and events.') }}</flux:text>
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
                    <x-material-icon name="add" class="text-[1.25rem]" />
                    {{ __('New project') }}
                </span>
            </flux:button>
        </div>
    </div>

    <div>
        <flux:heading size="sm" class="mb-3 text-zinc-500">{{ __('Projects') }}</flux:heading>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text>{{ __('Total projects') }}</flux:text>
                <p class="mt-3 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $this->projectStats['total'] }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text>{{ __('In progress') }}</flux:text>
                <p class="mt-3 text-3xl font-semibold text-huddle-primary">{{ $this->projectStats['in_progress'] }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text>{{ __('Completed') }}</flux:text>
                <p class="mt-3 text-3xl font-semibold text-huddle-comp">{{ $this->projectStats['completed'] }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text>{{ __('Need volunteers') }}</flux:text>
                <p class="mt-3 text-3xl font-semibold text-huddle-accent">{{ $this->projectStats['volunteers'] }}</p>
            </div>
        </div>
    </div>

    <div>
        <flux:heading size="sm" class="mb-3 text-zinc-500">{{ __('Events') }}</flux:heading>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text>{{ __('Total events') }}</flux:text>
                <p class="mt-3 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $this->eventStats['total'] }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text>{{ __('Upcoming') }}</flux:text>
                <p class="mt-3 text-3xl font-semibold text-huddle-primary">{{ $this->eventStats['upcoming'] }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text>{{ __('Ongoing') }}</flux:text>
                <p class="mt-3 text-3xl font-semibold text-huddle-comp">{{ $this->eventStats['ongoing'] }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text>{{ __('Need volunteers') }}</flux:text>
                <p class="mt-3 text-3xl font-semibold text-huddle-accent">{{ $this->eventStats['volunteers'] }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Recent projects') }}</flux:heading>
                <flux:link :href="route('projects.index')" wire:navigate>{{ __('View all') }}</flux:link>
            </div>
            @if ($this->recentProjects->isEmpty())
                <flux:text class="px-5 py-8 text-center">{{ __('No projects yet.') }}</flux:text>
            @else
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($this->recentProjects as $project)
                        <a href="{{ route('projects.show', $project) }}" wire:navigate class="flex items-center justify-between gap-3 px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50" wire:key="recent-project-{{ $project->id }}">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-zinc-900 dark:text-white">{{ $project->name }}</p>
                                <p class="text-xs text-zinc-500">{{ __('Led by') }} {{ $project->leader->name }}</p>
                            </div>
                            <x-project-status-badge :status="$project->project_status" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Upcoming events') }}</flux:heading>
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
                            <p class="flex items-center gap-3 mt-0.5 text-sm text-zinc-500">
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
</div>
