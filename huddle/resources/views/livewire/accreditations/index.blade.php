<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl" class="inline-flex items-center gap-2.5">
            <span class="flex size-10 items-center justify-center rounded-xl bg-huddle-primary/15 text-huddle-primary">
                <x-material-icon name="{{ $tab === 'mentors' ? 'supervisor_account' : 'verified' }}" class="text-[1.5rem]" />
            </span>
            {{ $tab === 'mentors' ? __('Mentors') : __('Accreditations') }}
        </flux:heading>
        <flux:text class="mt-1">
            {{ $tab === 'mentors'
                ? __('People you can ask for guidance on each accreditation.')
                : __('See who holds each accreditation and who to ask for guidance.') }}
        </flux:text>
    </div>

    <x-accreditations-mode-nav :mode="$tab === 'mentors' ? 'mentors' : 'browse'" />

    <div class="rounded-xl border border-zinc-200 border-s-4 border-s-huddle-primary bg-zinc-50/80 p-4 dark:border-zinc-700 dark:border-s-huddle-primary dark:bg-zinc-800/40 sm:p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="w-full sm:max-w-md">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    :label="__('Search')"
                    :placeholder="$tab === 'mentors' ? __('Mentor or accreditation...') : __('Accreditation, holder, or mentor...')"
                >
                    <x-slot:iconLeading>
                        <x-material-icon name="search" class="text-[1.25rem] text-zinc-400" />
                    </x-slot:iconLeading>
                </flux:input>
            </div>

            @if ($tab === 'browse' && $this->accreditations->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    <flux:button variant="ghost" size="sm" wire:click="expandAll">
                        {{ __('Expand all') }}
                    </flux:button>
                    <flux:button variant="ghost" size="sm" wire:click="collapseAll" :disabled="$expandedIds === []">
                        {{ __('Collapse all') }}
                    </flux:button>
                </div>
            @endif
        </div>
        <flux:text class="mt-3 text-sm text-zinc-500">
            @if ($tab === 'mentors')
                {{ trans_choice(':count mentor|:count mentors', $this->mentors->count(), ['count' => $this->mentors->count()]) }}
            @else
                {{ trans_choice(':count accreditation|:count accreditations', $this->accreditations->count(), ['count' => $this->accreditations->count()]) }}
            @endif
        </flux:text>
    </div>

    @if ($tab === 'mentors')
        @if ($this->mentors->isEmpty())
            <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-zinc-200 bg-white px-6 py-16 text-center shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:shadow-none">
                <div class="flex size-14 items-center justify-center rounded-full bg-huddle-primary/10 text-huddle-primary">
                    <x-material-icon name="supervisor_account" class="text-[2rem]" />
                </div>
                <flux:heading size="lg">
                    {{ filled(trim($search)) ? __('No matching mentors') : __('No mentors yet') }}
                </flux:heading>
                <flux:text>
                    {{ filled(trim($search)) ? __('Try a different search term.') : __('Mentors assigned to active accreditations will appear here.') }}
                </flux:text>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->mentors as $mentor)
                    <article
                        wire:key="public-mentor-{{ $mentor->id }}"
                        class="flex flex-col gap-4 rounded-xl border border-zinc-200 border-s-4 border-s-huddle-accent bg-white p-5 shadow-sm dark:border-zinc-700 dark:border-s-huddle-accent dark:bg-zinc-900 dark:shadow-none"
                    >
                        <div class="flex items-center gap-3">
                            <x-user-avatar :user="$mentor" size="md" />
                            <div class="min-w-0">
                                <h2 class="truncate font-semibold text-zinc-900 dark:text-white">
                                    <x-user-link :user="$mentor" class="text-zinc-900 dark:text-white" />
                                </h2>
                                <flux:text class="text-xs text-zinc-500">
                                    {{ trans_choice(':count accreditation|:count accreditations', $mentor->mentoredAccreditations->count(), ['count' => $mentor->mentoredAccreditations->count()]) }}
                                </flux:text>
                            </div>
                        </div>

                        <div>
                            <flux:text class="mb-2 text-xs font-medium uppercase tracking-wide text-zinc-500">
                                {{ __('Mentors for') }}
                            </flux:text>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($mentor->mentoredAccreditations as $accreditation)
                                    <x-user-flag-badge
                                        :name="$accreditation->name"
                                        wire:key="mentor-{{ $mentor->id }}-acc-{{ $accreditation->id }}"
                                    />
                                @endforeach
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    @elseif ($this->accreditations->isEmpty())
        <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-zinc-200 bg-white px-6 py-16 text-center shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:shadow-none">
            <div class="flex size-14 items-center justify-center rounded-full bg-huddle-primary/10 text-huddle-primary">
                <x-material-icon name="verified" class="text-[2rem]" />
            </div>
            <flux:heading size="lg">
                {{ filled(trim($search)) ? __('No matching accreditations') : __('No accreditations yet') }}
            </flux:heading>
            <flux:text>
                {{ filled(trim($search)) ? __('Try a different search term.') : __('Active accreditations will appear here for everyone to browse.') }}
            </flux:text>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->accreditations as $accreditation)
                @php
                    $isExpanded = $this->isExpanded($accreditation->id);
                    $previewMentors = $accreditation->mentors->take(3);
                    $extraMentors = max(0, $accreditation->mentors_count - $previewMentors->count());
                @endphp
                <article
                    wire:key="public-acc-{{ $accreditation->id }}"
                    @class([
                        'flex flex-col overflow-hidden rounded-xl border border-s-4 bg-white shadow-sm transition dark:bg-zinc-900 dark:shadow-none',
                        'border-zinc-200 border-s-huddle-primary dark:border-zinc-700' => ! $isExpanded,
                        'border-huddle-primary/40 border-s-huddle-primary ring-1 ring-huddle-primary/20 dark:border-teal-800/60' => $isExpanded,
                        'sm:col-span-2 xl:col-span-3' => $isExpanded,
                    ])
                >
                    <button
                        type="button"
                        wire:click="toggle({{ $accreditation->id }})"
                        class="flex flex-1 flex-col gap-3 p-5 text-start transition hover:bg-teal-50/40 dark:hover:bg-teal-950/20"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 space-y-1">
                                <h2 class="font-semibold text-zinc-900 dark:text-white">{{ $accreditation->name }}</h2>
                                @if ($accreditation->description)
                                    <p @class([
                                        'text-sm text-zinc-500',
                                        'line-clamp-2' => ! $isExpanded,
                                    ])>
                                        {{ $accreditation->description }}
                                    </p>
                                @endif
                            </div>
                            <x-material-icon
                                name="{{ $isExpanded ? 'expand_less' : 'expand_more' }}"
                                class="shrink-0 text-[1.5rem] text-zinc-400"
                            />
                        </div>

                        <div class="mt-auto flex flex-wrap items-center gap-x-3 gap-y-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                            <span class="inline-flex items-center gap-1 rounded-full bg-huddle-primary/10 px-2.5 py-1 text-xs font-medium text-huddle-primary">
                                <x-material-icon name="groups" class="text-[0.875rem]" />
                                {{ trans_choice(':count holder|:count holders', $accreditation->active_holders_count, ['count' => $accreditation->active_holders_count]) }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-huddle-accent/10 px-2.5 py-1 text-xs font-medium text-fuchsia-800 dark:text-huddle-accent">
                                <x-material-icon name="supervisor_account" class="text-[0.875rem]" />
                                {{ trans_choice(':count mentor|:count mentors', $accreditation->mentors_count, ['count' => $accreditation->mentors_count]) }}
                            </span>
                            @if ($previewMentors->isNotEmpty() && ! $isExpanded)
                                <div class="ms-auto flex items-center gap-2">
                                    <span class="text-xs font-medium text-zinc-500">{{ __('Ask') }}</span>
                                    <div class="flex items-center -space-x-1.5">
                                        @foreach ($previewMentors as $mentor)
                                            <span
                                                wire:key="acc-{{ $accreditation->id }}-mentor-preview-{{ $mentor->id }}"
                                                class="relative rounded-lg ring-2 ring-white dark:ring-zinc-900"
                                                title="{{ $mentor->name }}"
                                            >
                                                <x-user-avatar :user="$mentor" size="sm" />
                                            </span>
                                        @endforeach
                                    </div>
                                    @if ($extraMentors > 0)
                                        <span class="text-xs text-zinc-500">+{{ $extraMentors }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </button>

                    @if ($isExpanded)
                        <div class="grid gap-6 border-t border-zinc-200 bg-zinc-50/80 px-5 py-5 dark:border-zinc-700 dark:bg-zinc-800/40 sm:grid-cols-2">
                            <div>
                                <flux:heading size="sm" class="mb-3 inline-flex items-center gap-2">
                                    <x-material-icon name="groups" class="text-[1.125rem] text-huddle-primary" />
                                    {{ __('Holders') }}
                                </flux:heading>
                                @if ($accreditation->assignments->isEmpty())
                                    <flux:text class="text-sm">{{ __('No active holders yet.') }}</flux:text>
                                @else
                                    <ul class="grid gap-2 sm:grid-cols-2">
                                        @foreach ($accreditation->assignments->sortBy('user.name') as $assignment)
                                            <li class="flex items-center gap-2 rounded-lg bg-white px-2 py-1.5 text-sm dark:bg-zinc-900">
                                                <x-user-avatar :user="$assignment->user" size="sm" />
                                                <x-user-link :user="$assignment->user" />
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div>
                                <flux:heading size="sm" class="mb-3 inline-flex items-center gap-2">
                                    <x-material-icon name="supervisor_account" class="text-[1.125rem] text-huddle-accent" />
                                    {{ __('Who to ask') }}
                                </flux:heading>
                                @if ($accreditation->mentors->isEmpty())
                                    <flux:text class="text-sm">{{ __('No mentors listed for this accreditation.') }}</flux:text>
                                @else
                                    <ul class="grid gap-2 sm:grid-cols-2">
                                        @foreach ($accreditation->mentors as $mentor)
                                            <li class="flex items-center gap-2 rounded-lg bg-white px-2 py-1.5 text-sm dark:bg-zinc-900">
                                                <x-user-avatar :user="$mentor" size="sm" />
                                                <x-user-link :user="$mentor" />
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</div>
