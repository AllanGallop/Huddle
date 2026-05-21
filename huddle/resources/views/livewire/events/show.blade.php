<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 flex-1">
            <flux:link :href="route('events.index')" wire:navigate class="inline-flex items-center gap-1 text-sm no-underline hover:no-underline">
                <x-material-icon name="arrow_back" class="text-[1rem]" />
                {{ __('Back to events') }}
            </flux:link>
            <flux:heading size="xl" class="mt-2">{{ $event->name }}</flux:heading>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <x-event-status-badge :status="$event->event_status" />
                <x-event-type-badge :type="$event->event_type" />
                <span @class([
                    'inline-flex items-center gap-1 text-sm font-medium',
                    'text-huddle-comp' => $event->isOngoing(),
                    'text-huddle-primary' => $event->isUpcoming(),
                    'text-zinc-500' => $event->isPast(),
                ])>
                    <x-material-icon name="schedule" class="text-[1rem]" />
                    {{ $event->timingLabel() }}
                </span>
                @if ($event->volunteer_required)
                    <span class="inline-flex items-center gap-1 text-sm font-medium text-huddle-accent">
                        <x-material-icon name="volunteer_activism" class="text-[1rem]" />
                        {{ __('Volunteers needed') }}
                    </span>
                @endif
            </div>
        </div>

        @if ($this->canManageEvent)
            <div class="flex flex-wrap items-center gap-2">
                <flux:button variant="ghost" wire:click="openEditModal">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="edit" class="text-[1.25rem]" />
                        {{ __('Edit event') }}
                    </span>
                </flux:button>
                <flux:button
                    variant="danger"
                    wire:click="deleteEvent"
                    wire:confirm="{{ __('Delete this event? This cannot be undone.') }}"
                >
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="delete" class="text-[1.25rem]" />
                        {{ __('Delete') }}
                    </span>
                </flux:button>
            </div>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="mb-2 text-sm font-medium text-zinc-800 dark:text-white">{{ __('Description') }}</p>
                <div class="min-h-[8rem] rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm leading-relaxed text-zinc-700 whitespace-pre-wrap dark:border-zinc-600 dark:bg-zinc-800/60 dark:text-zinc-200">{{ $event->description }}</div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="inline-flex items-center gap-2">
                    <x-material-icon name="forum" class="text-[1.375rem] text-huddle-primary" />
                    {{ __('Comments') }}
                </flux:heading>

                @if ($replyingTo === null)
                    <form wire:submit="addComment" class="mt-4 space-y-3">
                        <flux:textarea wire:model="comment" :placeholder="__('Share an update or ask a question...')" rows="3" required />
                        @error('comment')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <flux:button type="submit" variant="primary">
                            <span class="inline-flex items-center gap-2">
                                <x-material-icon name="send" class="text-[1.25rem]" />
                                {{ __('Post comment') }}
                            </span>
                        </flux:button>
                    </form>
                @endif

                <div class="mt-6 space-y-4">
                    @forelse ($this->comments as $comment)
                        <x-event-comment :comment="$comment" :replying-to="$replyingTo" />
                    @empty
                        <flux:text>{{ __('No comments yet. Be the first to comment.') }}</flux:text>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="inline-flex items-center gap-2">
                    <x-material-icon name="event" class="text-[1.375rem] text-huddle-primary" />
                    {{ __('Schedule') }}
                </flux:heading>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-zinc-500">{{ __('Starts') }}</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">{{ $event->start_time->format('l, j F Y') }}</dd>
                        <dd class="text-zinc-600 dark:text-zinc-300">{{ $event->start_time->format('H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Ends') }}</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">{{ $event->end_time->format('l, j F Y') }}</dd>
                        <dd class="text-zinc-600 dark:text-zinc-300">{{ $event->end_time->format('H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Location') }}</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">{{ $event->location }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="inline-flex items-center gap-2">
                    <x-material-icon name="groups" class="text-[1.375rem] text-huddle-primary" />
                    {{ __('Volunteers') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __(':count people signed up', ['count' => $this->volunteers->count()]) }}</flux:text>

                <div class="mt-4 space-y-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-800/40">
                    <div>
                        <flux:text class="mb-2 text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Join this event') }}</flux:text>
                        <flux:button
                            :variant="$this->isVolunteering ? 'filled' : 'primary'"
                            wire:click="toggleVolunteer"
                            class="w-full {{ $this->isVolunteering ? '!bg-huddle-comp !text-zinc-900' : '' }}"
                        >
                            <span class="inline-flex items-center justify-center gap-2">
                                <x-material-icon name="{{ $this->isVolunteering ? 'person_remove' : 'group_add' }}" class="text-[1.25rem]" />
                                {{ $this->isVolunteering ? __('Leave volunteer list') : __('Volunteer for this event') }}
                            </span>
                        </flux:button>
                    </div>

                    @if ($this->isAdmin)
                        <hr class="border-zinc-200 dark:border-zinc-600" />
                        <div>
                            <flux:text class="mb-2 text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Manage roster') }}</flux:text>
                            <form wire:submit="addVolunteer" class="space-y-3">
                                <flux:select wire:model="adminVolunteerUserId" :label="__('Add member')" placeholder="{{ __('Select a member...') }}">
                                    @foreach ($this->availableVolunteerUsers as $user)
                                        <flux:select.option :value="$user->id">{{ $user->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                @error('adminVolunteerUserId')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <flux:button type="submit" variant="primary" size="sm" class="w-full" :disabled="$this->availableVolunteerUsers->isEmpty()">
                                    <span class="inline-flex items-center justify-center gap-1.5">
                                        <x-material-icon name="person_add" class="text-[1.125rem]" />
                                        {{ __('Add volunteer') }}
                                    </span>
                                </flux:button>
                            </form>
                        </div>
                    @endif

                    <hr class="border-zinc-200 dark:border-zinc-600" />

                    <div>
                        <flux:text class="mb-3 text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Roster') }}</flux:text>
                        @if ($this->volunteers->isEmpty())
                            <flux:text class="text-sm">{{ __('No volunteers yet.') }}</flux:text>
                        @else
                            <ul class="space-y-2">
                                @foreach ($this->volunteers as $volunteer)
                                    <li wire:key="event-volunteer-{{ $volunteer->id }}">
                                        @if ($this->isAdmin && $editingVolunteerId === $volunteer->id)
                                            <form wire:submit="updateVolunteer" class="space-y-2 rounded-lg border border-huddle-primary/30 bg-white p-3 dark:bg-zinc-900">
                                                <flux:select wire:model="editVolunteerUserId" :label="__('Member')">
                                                    @foreach ($this->users as $user)
                                                        <flux:select.option :value="$user->id">{{ $user->name }}</flux:select.option>
                                                    @endforeach
                                                </flux:select>
                                                @error('editVolunteerUserId')
                                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                                <div class="flex gap-2">
                                                    <flux:button type="submit" variant="primary" size="sm">{{ __('Save') }}</flux:button>
                                                    <flux:button type="button" variant="ghost" size="sm" wire:click="cancelEditVolunteer">{{ __('Cancel') }}</flux:button>
                                                </div>
                                            </form>
                                        @else
                                            <div class="flex items-center justify-between gap-2 rounded-lg bg-white px-2 py-1.5 dark:bg-zinc-900">
                                                <div class="flex min-w-0 items-center gap-2">
                                                    <x-user-avatar :user="$volunteer->user" size="sm" />
                                                    <x-user-link :user="$volunteer->user" class="truncate text-sm text-zinc-900 dark:text-white" />
                                                </div>
                                                @if ($this->isAdmin)
                                                    <div class="flex shrink-0 gap-1">
                                                        <flux:button size="sm" variant="ghost" wire:click="startEditVolunteer({{ $volunteer->id }})">
                                                            <x-material-icon name="edit" class="text-[1rem]" />
                                                        </flux:button>
                                                        <flux:button
                                                            size="sm"
                                                            variant="danger"
                                                            wire:click="removeVolunteer({{ $volunteer->id }})"
                                                            wire:confirm="{{ __('Remove this volunteer from the event?') }}"
                                                        >
                                                            <x-material-icon name="delete" class="text-[1rem]" />
                                                        </flux:button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="inline-flex items-center gap-2">
                    <x-material-icon name="info" class="text-[1.375rem] text-huddle-primary" />
                    {{ __('Details') }}
                </flux:heading>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-zinc-500">{{ __('Organised by') }}</dt>
                        <dd><x-user-link :user="$event->creator" class="text-zinc-900 dark:text-white" /></dd>
                    </div>
                    @if ($event->created_at)
                        <div>
                            <dt class="text-zinc-500">{{ __('Created') }}</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $event->created_at->format('j M Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <flux:modal wire:model="showEditModal" class="md:max-w-2xl">
        <form wire:submit="updateEvent" class="space-y-6">
            <div>
                <flux:heading size="lg" class="inline-flex items-center gap-2">
                    <x-material-icon name="edit" class="text-[1.5rem] text-huddle-primary" />
                    {{ __('Edit event') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Update event details.') }}</flux:text>
            </div>

            <flux:input wire:model="name" :label="__('Name')" required />
            <flux:textarea wire:model="description" :label="__('Description')" rows="6" required />
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
                <flux:button type="button" variant="ghost" wire:click="closeEditModal">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="save" class="text-[1.25rem]" />
                        {{ __('Save changes') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
