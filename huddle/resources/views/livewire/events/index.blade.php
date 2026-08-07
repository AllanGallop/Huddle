<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl" class="inline-flex items-center gap-2.5">
                <span class="flex size-10 items-center justify-center rounded-xl bg-huddle-primary/15 text-huddle-primary">
                    <x-material-icon name="event" class="text-[1.5rem]" />
                </span>
                {{ __('Events') }}
            </flux:heading>
            <flux:text class="mt-1">{{ __('Community gatherings, workshops, and meetups.') }}</flux:text>
        </div>
        <flux:button variant="primary" wire:click="openCreateModal">
            <span class="inline-flex items-center gap-2">
                <x-material-icon name="event" class="text-[1.25rem]" />
                {{ __('New event') }}
            </span>
        </flux:button>
    </div>

    <div class="rounded-xl border border-zinc-200 border-s-4 border-s-huddle-comp bg-white p-4 dark:border-zinc-700 dark:border-s-huddle-comp dark:bg-zinc-900 sm:p-5">
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

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :label="__('Search')"
                :placeholder="__('Name, location, or organiser...')"
            >
                <x-slot:iconLeading>
                    <x-material-icon name="search" class="text-[1.25rem] text-zinc-400" />
                </x-slot:iconLeading>
            </flux:input>

            <flux:select wire:model.live="statusFilter" :label="__('Status')">
                <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
                @foreach (\App\Models\Event::STATUSES as $status)
                    <flux:select.option :value="$status">{{ str($status)->headline() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="typeFilter" :label="__('Type')">
                <flux:select.option value="">{{ __('All types') }}</flux:select.option>
                @foreach (\App\Models\Event::TYPES as $type)
                    <flux:select.option :value="$type">{{ str($type)->headline() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="timingFilter" :label="__('When')">
                <flux:select.option value="">{{ __('Any time') }}</flux:select.option>
                <flux:select.option value="upcoming">{{ __('Upcoming') }}</flux:select.option>
                <flux:select.option value="ongoing">{{ __('Ongoing') }}</flux:select.option>
                <flux:select.option value="past">{{ __('Past') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="volunteersFilter" :label="__('Volunteers')">
                <flux:select.option value="">{{ __('Any') }}</flux:select.option>
                <flux:select.option value="required">{{ __('Volunteers needed') }}</flux:select.option>
                <flux:select.option value="not_required">{{ __('No volunteer call') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
            <flux:checkbox wire:model.live="mineOnly" :label="__('Only my events')" />
            <flux:text class="text-sm text-zinc-500">
                {{ trans_choice(':count event|:count events', $this->events->count(), ['count' => $this->events->count()]) }}
            </flux:text>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        @if ($this->events->isEmpty())
            <div class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                <div class="flex size-14 items-center justify-center rounded-full bg-huddle-primary/10 text-huddle-primary">
                    <x-material-icon name="event" class="text-[2rem]" />
                </div>
                <flux:heading size="lg">
                    {{ $this->hasActiveFilters ? __('No matching events') : __('No events yet') }}
                </flux:heading>
                <flux:text>
                    {{ $this->hasActiveFilters ? __('Try adjusting your filters or search.') : __('Create an event to bring the community together.') }}
                </flux:text>
                @unless ($this->hasActiveFilters)
                    <flux:button variant="primary" wire:click="openCreateModal">
                        <span class="inline-flex items-center gap-2">
                            <x-material-icon name="add" class="text-[1.25rem]" />
                            {{ __('Create an event') }}
                        </span>
                    </flux:button>
                @endunless
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortBy === 'name' ? $sortDirection : null" wire:click="sort('name')" class="min-w-[12rem] border-s-4 border-s-transparent">
                        {{ __('Event') }}
                    </flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'start_time'" :direction="$sortBy === 'start_time' ? $sortDirection : null" wire:click="sort('start_time')" class="hidden md:table-cell">
                        {{ __('When') }}
                    </flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Location') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortBy === 'status' ? $sortDirection : null" wire:click="sort('status')">
                        {{ __('Status') }}
                    </flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortBy === 'type' ? $sortDirection : null" wire:click="sort('type')" class="hidden sm:table-cell">
                        {{ __('Type') }}
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->events as $event)
                        @php
                            $statusAccent = match ($event->event_status) {
                                'published' => 'border-s-huddle-comp',
                                'cancelled' => 'border-s-huddle-accent',
                                'archived' => 'border-s-zinc-300 dark:border-s-zinc-600',
                                default => 'border-s-zinc-300 dark:border-s-zinc-600',
                            };
                        @endphp
                        <flux:table.row
                            wire:key="event-{{ $event->id }}"
                            class="cursor-pointer transition hover:bg-zinc-50 dark:hover:bg-zinc-800/60"
                            wire:click="viewEvent({{ $event->id }})"
                        >
                            <flux:table.cell variant="strong" @class(['max-w-md border-s-4 ps-3', $statusAccent])>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <p class="flex min-w-0 max-w-full items-center gap-1.5 font-medium text-zinc-900 dark:text-white">
                                            <x-material-icon name="event" class="shrink-0 text-[1.125rem] text-huddle-primary" />
                                            <span class="truncate">{{ $event->name }}</span>
                                        </p>
                                        @if ($event->volunteer_required)
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-huddle-accent/15 px-2 py-0.5 text-xs font-medium text-huddle-accent"
                                                title="{{ __('Volunteers needed') }}"
                                            >
                                                <x-material-icon name="volunteer_activism" class="text-[0.875rem]" />
                                                {{ __('Volunteers') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs">
                                        <span class="inline-flex items-center gap-1 text-huddle-primary" title="{{ __('Comments') }}">
                                            <x-material-icon name="chat_bubble" class="text-[0.875rem]" />
                                            {{ $event->comments_count }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-huddle-accent" title="{{ __('Volunteers') }}">
                                            <x-material-icon name="group" class="text-[0.875rem]" />
                                            {{ $event->volunteers_count }}
                                        </span>
                                        <span @class([
                                            'inline-flex items-center gap-1 font-medium',
                                            'text-huddle-comp' => $event->isOngoing(),
                                            'text-huddle-primary' => $event->isUpcoming(),
                                            'text-zinc-400' => $event->isPast(),
                                        ])>
                                            @if ($event->isOngoing())
                                                {{ __('Ongoing') }}
                                            @elseif ($event->isUpcoming())
                                                {{ __('Upcoming') }}
                                            @else
                                                {{ __('Past') }}
                                            @endif
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-zinc-400 md:hidden">
                                            <x-material-icon name="schedule" class="text-[0.875rem]" />
                                            {{ $event->start_time->format('j M Y, H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="hidden whitespace-nowrap md:table-cell">
                                <div class="text-sm">
                                    <p class="inline-flex items-center gap-1">
                                        <x-material-icon name="schedule" class="text-[1rem] text-huddle-alt" />
                                        {{ $event->start_time->format('j M Y') }}
                                    </p>
                                    <p class="ms-5 text-xs text-zinc-500">{{ $event->start_time->format('H:i') }} – {{ $event->end_time->format('H:i') }}</p>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="hidden max-w-[10rem] truncate lg:table-cell">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-material-icon name="place" class="text-[1rem] text-huddle-accent" />
                                    {{ $event->location }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <x-event-status-badge :status="$event->event_status" />
                            </flux:table.cell>

                            <flux:table.cell class="hidden sm:table-cell">
                                <x-event-type-badge :type="$event->event_type" />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>

    <flux:modal wire:model="showCreateModal" class="md:max-w-2xl">
        <form wire:submit="createEvent" class="space-y-6">
            <div>
                <flux:heading size="lg" class="inline-flex items-center gap-2">
                    <x-material-icon name="event" class="text-[1.5rem] text-huddle-primary" />
                    {{ __('New event') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Schedule a community event for members to discover and join.') }}</flux:text>
            </div>

            <flux:input wire:model="name" :label="__('Name')" required />
            <flux:textarea wire:model="description" :label="__('Description')" rows="4" required />
            <flux:input wire:model="location" :label="__('Location')" required />

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="start_time" type="datetime-local" :label="__('Starts')" required />
                <flux:input wire:model="end_time" type="datetime-local" :label="__('Ends')" required />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model="event_type" :label="__('Type')">
                    @foreach (\App\Models\Event::TYPES as $type)
                        <flux:select.option :value="$type">{{ str($type)->headline() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="event_status" :label="__('Status')">
                    @foreach (\App\Models\Event::STATUSES as $status)
                        <flux:select.option :value="$status">{{ str($status)->headline() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:checkbox wire:model="volunteer_required" :label="__('Volunteers required')" />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeCreateModal">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="check" class="text-[1.25rem]" />
                        {{ __('Create event') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
