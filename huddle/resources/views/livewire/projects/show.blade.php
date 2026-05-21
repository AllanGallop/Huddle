<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 flex-1">
            <flux:link :href="route('projects.index')" wire:navigate class="inline-flex items-center gap-1 text-sm">
                <x-material-icon name="arrow_back" class="text-[1rem]" />
                {{ __('Back to projects') }}
            </flux:link>
            <flux:heading size="xl" class="mt-2">{{ $project->name }}</flux:heading>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <x-project-status-badge :status="$project->project_status" />
                <flux:text class="text-sm">
                    {{ __('Led by') }}
                    <x-user-link :user="$project->leader" class="text-zinc-700 dark:text-zinc-200" />
                </flux:text>
                @if ($project->due_date)
                    <span @class([
                        'inline-flex items-center gap-1 text-sm font-medium',
                        'text-red-600 dark:text-red-400' => $project->isOverdue(),
                        'text-zinc-600 dark:text-zinc-300' => ! $project->isOverdue(),
                    ])>
                        <x-material-icon name="event" class="text-[1rem]" />
                        {{ __('Due') }} {{ $project->due_date->format('j M Y') }}
                        @if ($project->isOverdue())
                            ({{ __('overdue') }})
                        @endif
                    </span>
                @endif
                @if ($project->volunteer_required)
                    <span class="inline-flex items-center gap-1 text-sm font-medium text-huddle-accent">
                        <x-material-icon name="volunteer_activism" class="text-[1rem]" />
                        {{ __('Volunteers needed') }}
                    </span>
                @endif
            </div>
        </div>

        @if ($this->canManageProject)
            <div class="flex flex-wrap items-center gap-2">
                <flux:button variant="ghost" wire:click="openEditModal">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="edit" class="text-[1.25rem]" />
                        {{ __('Edit project') }}
                    </span>
                </flux:button>
                <flux:button
                    variant="danger"
                    wire:click="deleteProject"
                    wire:confirm="{{ __('Delete this project? This cannot be undone.') }}"
                >
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="delete" class="text-[1.25rem]" />
                        {{ __('Delete') }}
                    </span>
                </flux:button>
            </div>
        @endif
    </div>

    @if ($this->canManageFinancials)
        <nav class="flex gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800/60" aria-label="{{ __('Project sections') }}">
            <button
                type="button"
                wire:click="setTab('overview')"
                @class([
                    'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                    'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'overview',
                    'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'overview',
                ])
            >
                <span class="inline-flex items-center justify-center gap-2">
                    <x-material-icon name="dashboard" class="text-[1.125rem]" />
                    {{ __('Overview') }}
                </span>
            </button>
            <button
                type="button"
                wire:click="setTab('finance')"
                @class([
                    'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                    'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $activeTab === 'finance',
                    'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $activeTab !== 'finance',
                ])
            >
                <span class="inline-flex items-center justify-center gap-2">
                    <x-material-icon name="payments" class="text-[1.125rem]" />
                    {{ __('Finance') }}
                </span>
            </button>
        </nav>

        @if (session('status'))
            <div class="rounded-lg border border-huddle-comp/40 bg-huddle-comp/10 px-4 py-3 text-sm text-zinc-800 dark:text-zinc-200">
                {{ session('status') }}
            </div>
        @endif
    @endif

    @if (! $this->canManageFinancials || $activeTab === 'overview')
    {{-- Description --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <p class="mb-2 text-sm font-medium text-zinc-800 dark:text-white">{{ __('Description') }}</p>
        <div
            class="min-h-[10rem] w-full resize-none rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm leading-relaxed text-zinc-700 whitespace-pre-wrap dark:border-zinc-600 dark:bg-zinc-800/60 dark:text-zinc-200"
            role="region"
            aria-readonly="true"
        >{{ $project->description }}</div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Main column: images + comments --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Image carousel --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3">
                    <flux:heading size="lg" class="inline-flex items-center gap-2">
                        <x-material-icon name="photo_library" class="text-[1.375rem] text-huddle-primary" />
                        {{ __('Images') }}
                    </flux:heading>
                    @if ($this->images->isNotEmpty())
                        <flux:text class="text-xs text-zinc-500">
                            {{ $this->activeImageIndex + 1 }} / {{ $this->images->count() }}
                        </flux:text>
                    @endif
                </div>

                <form wire:submit="uploadImage" class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center">
                    <input
                        type="file"
                        wire:model="photo"
                        accept="image/*"
                        class="block w-full text-sm text-zinc-500 file:me-3 file:rounded-md file:border-0 file:bg-huddle-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-huddle-primary hover:file:bg-huddle-primary/20"
                    />
                    <flux:button type="submit" variant="primary" size="sm" wire:loading.attr="disabled" wire:target="photo,uploadImage" class="shrink-0">
                        <span class="inline-flex items-center gap-1.5">
                            <x-material-icon name="add_photo_alternate" class="text-[1.125rem]" />
                            {{ __('Upload') }}
                        </span>
                    </flux:button>
                </form>
                @error('photo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div wire:loading wire:target="photo" class="mt-1 text-xs text-zinc-500">{{ __('Uploading...') }}</div>

                @if ($this->images->isEmpty())
                    <div class="mt-4 flex h-32 items-center justify-center rounded-lg border border-dashed border-zinc-300 bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800/40">
                        <flux:text class="text-sm">{{ __('No images yet.') }}</flux:text>
                    </div>
                @else
                    <div class="relative mt-4">
                        <div class="relative mx-auto max-w-md overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                            <div class="aspect-[4/3] max-h-40">
                                <img
                                    src="{{ $this->activeImage->url() }}"
                                    alt="{{ $project->name }}"
                                    class="size-full object-cover"
                                />
                            </div>

                            @if ($this->images->count() > 1)
                                <button
                                    type="button"
                                    wire:click="previousImage"
                                    class="absolute start-1 top-1/2 flex size-8 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-white transition hover:bg-black/70"
                                    aria-label="{{ __('Previous image') }}"
                                >
                                    <x-material-icon name="chevron_left" class="text-[1.25rem]" />
                                </button>
                                <button
                                    type="button"
                                    wire:click="nextImage"
                                    class="absolute end-1 top-1/2 flex size-8 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-white transition hover:bg-black/70"
                                    aria-label="{{ __('Next image') }}"
                                >
                                    <x-material-icon name="chevron_right" class="text-[1.25rem]" />
                                </button>
                            @endif

                            @if ($this->canManageProject)
                                <button
                                    type="button"
                                    wire:click="deleteImage({{ $this->activeImage->id }})"
                                    wire:confirm="{{ __('Remove this image?') }}"
                                    class="absolute end-1 top-1 inline-flex items-center gap-0.5 rounded-md bg-black/60 px-2 py-1 text-xs text-white"
                                >
                                    <x-material-icon name="close" class="text-[0.875rem]" />
                                    {{ __('Remove') }}
                                </button>
                            @endif
                        </div>

                        <div class="mt-3 flex justify-center gap-2 overflow-x-auto pb-1">
                            @foreach ($this->images as $index => $image)
                                <button
                                    type="button"
                                    wire:click="setActiveImage({{ $index }})"
                                    wire:key="thumb-{{ $image->id }}"
                                    @class([
                                        'size-14 shrink-0 overflow-hidden rounded-md border-2 transition',
                                        'border-huddle-primary ring-2 ring-huddle-primary/30' => $this->activeImageIndex === $index,
                                        'border-transparent opacity-70 hover:opacity-100' => $this->activeImageIndex !== $index,
                                    ])
                                    aria-label="{{ __('View image :num', ['num' => $index + 1]) }}"
                                >
                                    <img
                                        src="{{ $image->url() }}"
                                        alt=""
                                        class="size-full object-cover"
                                    />
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Comments --}}
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
                        <x-project-comment :comment="$comment" :replying-to="$replyingTo" />
                    @empty
                        <flux:text>{{ __('No comments yet. Be the first to comment.') }}</flux:text>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar: volunteers (grouped) + details --}}
        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="inline-flex items-center gap-2">
                    <x-material-icon name="groups" class="text-[1.375rem] text-huddle-primary" />
                    {{ __('Volunteers') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __(':count people signed up', ['count' => $this->volunteers->count()]) }}</flux:text>

                <div class="mt-4 space-y-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-800/40">
                    {{-- Self sign-up --}}
                    <div>
                        <flux:text class="mb-2 text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Join this project') }}</flux:text>
                        <flux:button
                            :variant="$this->isVolunteering ? 'filled' : 'primary'"
                            wire:click="toggleVolunteer"
                            class="w-full {{ $this->isVolunteering ? '!bg-huddle-comp !text-zinc-900' : '' }}"
                        >
                            <span class="inline-flex items-center justify-center gap-2">
                                <x-material-icon name="{{ $this->isVolunteering ? 'person_remove' : 'group_add' }}" class="text-[1.25rem]" />
                                {{ $this->isVolunteering ? __('Leave volunteer list') : __('Volunteer for this project') }}
                            </span>
                        </flux:button>
                    </div>

                    @if ($this->isAdmin)
                        <hr class="border-zinc-200 dark:border-zinc-600" />

                        {{-- Admin: add volunteer --}}
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

                    {{-- Volunteer list --}}
                    <div>
                        <flux:text class="mb-3 text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Roster') }}</flux:text>

                        @if ($this->volunteers->isEmpty())
                            <flux:text class="text-sm">{{ __('No volunteers yet.') }}</flux:text>
                        @else
                            <ul class="space-y-2">
                                @foreach ($this->volunteers as $volunteer)
                                    <li wire:key="volunteer-{{ $volunteer->id }}">
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
                                                            wire:confirm="{{ __('Remove this volunteer from the project?') }}"
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
                        <dt class="text-zinc-500">{{ __('Created by') }}</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">{{ $project->creator->name }}</dd>
                    </div>
                    @if ($project->due_date)
                        <div>
                            <dt class="text-zinc-500">{{ __('Due date') }}</dt>
                            <dd @class([
                                'font-medium',
                                'text-red-600 dark:text-red-400' => $project->isOverdue(),
                                'text-zinc-900 dark:text-white' => ! $project->isOverdue(),
                            ])>{{ $project->due_date->format('j M Y') }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-zinc-500">{{ __('Created') }}</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">{{ $project->created_at->format('j M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Last updated') }}</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">{{ $project->updated_at->format('j M Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
    @endif

    @if ($this->canManageFinancials && $activeTab === 'finance')
        @include('livewire.projects.partials.financial')
    @endif

    <flux:modal wire:model="showEditModal" class="md:max-w-2xl">
        <form wire:submit="updateProject" class="space-y-6">
            <div>
                <flux:heading size="lg" class="inline-flex items-center gap-2">
                    <x-material-icon name="edit" class="text-[1.5rem] text-huddle-primary" />
                    {{ __('Edit project') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Update project details.') }}</flux:text>
            </div>

            <flux:input wire:model="name" :label="__('Name')" required />

            <flux:textarea wire:model="description" :label="__('Description')" rows="10" required />

            <div class="grid gap-4 sm:grid-cols-2">
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

                <flux:input wire:model="due_date" type="date" :label="__('Due date (optional)')" />
            </div>

            <flux:checkbox wire:model="volunteer_required" :label="__('Volunteers required')" />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeEditModal">
                    {{ __('Cancel') }}
                </flux:button>
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
