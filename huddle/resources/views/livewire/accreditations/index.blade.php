<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl" class="inline-flex items-center gap-2.5">
            <span class="flex size-10 items-center justify-center rounded-xl bg-huddle-primary/15 text-huddle-primary">
                <x-material-icon name="verified" class="text-[1.5rem]" />
            </span>
            {{ __('Accreditations') }}
        </flux:heading>
        <flux:text class="mt-1">{{ __('See who holds each accreditation and who to ask for guidance.') }}</flux:text>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        @if ($this->accreditations->isEmpty())
            <div class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                <div class="flex size-14 items-center justify-center rounded-full bg-huddle-primary/10 text-huddle-primary">
                    <x-material-icon name="verified" class="text-[2rem]" />
                </div>
                <flux:heading size="lg">{{ __('No accreditations yet') }}</flux:heading>
                <flux:text>{{ __('Active accreditations will appear here for everyone to browse.') }}</flux:text>
            </div>
        @else
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($this->accreditations as $accreditation)
                    <div wire:key="public-acc-{{ $accreditation->id }}">
                        <button
                            type="button"
                            wire:click="toggle({{ $accreditation->id }})"
                            class="flex w-full items-start justify-between gap-4 px-5 py-4 text-start transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <div class="min-w-0">
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $accreditation->name }}</p>
                                @if ($accreditation->description)
                                    <p class="mt-1 text-sm text-zinc-500">{{ $accreditation->description }}</p>
                                @endif
                                <p class="mt-2 text-xs text-zinc-500">
                                    {{ trans_choice(':count holder|:count holders', $accreditation->active_holders_count, ['count' => $accreditation->active_holders_count]) }}
                                    ·
                                    {{ trans_choice(':count mentor|:count mentors', $accreditation->mentors_count, ['count' => $accreditation->mentors_count]) }}
                                </p>
                            </div>
                            <x-material-icon
                                name="{{ $expandedId === $accreditation->id ? 'expand_less' : 'expand_more' }}"
                                class="shrink-0 text-[1.5rem] text-zinc-400"
                            />
                        </button>

                        @if ($expandedId === $accreditation->id)
                            <div class="grid gap-6 border-t border-zinc-200 bg-zinc-50/80 px-5 py-5 dark:border-zinc-700 dark:bg-zinc-800/40 sm:grid-cols-2">
                                <div>
                                    <flux:heading size="sm" class="mb-3 inline-flex items-center gap-2">
                                        <x-material-icon name="groups" class="text-[1.125rem] text-huddle-primary" />
                                        {{ __('Holders') }}
                                    </flux:heading>
                                    @if ($accreditation->assignments->isEmpty())
                                        <flux:text class="text-sm">{{ __('No active holders yet.') }}</flux:text>
                                    @else
                                        <ul class="space-y-2">
                                            @foreach ($accreditation->assignments->sortBy('user.name') as $assignment)
                                                <li class="flex items-center gap-2 text-sm">
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
                                        <ul class="space-y-2">
                                            @foreach ($accreditation->mentors as $mentor)
                                                <li class="flex items-center gap-2 text-sm">
                                                    <x-user-avatar :user="$mentor" size="sm" />
                                                    <x-user-link :user="$mentor" />
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
