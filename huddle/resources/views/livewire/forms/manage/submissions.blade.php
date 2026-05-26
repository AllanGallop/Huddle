<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:link :href="route('forms.manage.edit', $form)" wire:navigate class="inline-flex items-center gap-1 text-sm">
            <x-material-icon name="arrow_back" class="text-[1rem]" />
            {{ __('Back to edit form') }}
        </flux:link>
        <flux:heading size="xl" class="mt-2">{{ $form->title }}</flux:heading>
        <flux:text class="mt-1">{{ __(':count responses', ['count' => $this->submissions->count()]) }}</flux:text>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        @if ($this->submissions->isEmpty())
            <div class="px-5 py-12 text-center">
                <flux:text>{{ __('No responses yet.') }}</flux:text>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                        <tr>
                            <th class="px-5 py-3">{{ __('Member') }}</th>
                            <th class="px-5 py-3">{{ __('Submitted') }}</th>
                            @if ($form->isExam())
                                <th class="px-5 py-3">{{ __('Score') }}</th>
                                <th class="px-5 py-3">{{ __('Result') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->submissions as $submission)
                            <tr wire:key="submission-{{ $submission->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-user-avatar :user="$submission->user" size="sm" />
                                        <x-user-link :user="$submission->user" />
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-zinc-600 dark:text-zinc-300">{{ $submission->submitted_at->format('j M Y H:i') }}</td>
                                @if ($form->isExam())
                                    <td class="px-5 py-3">{{ $submission->score }} / {{ $submission->max_score }}</td>
                                    <td class="px-5 py-3">
                                        @if ($submission->passed === null)
                                            —
                                        @else
                                            <span @class([
                                                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                'bg-huddle-comp/20 text-huddle-comp' => $submission->passed,
                                                'bg-red-500/15 text-red-700 dark:text-red-300' => ! $submission->passed,
                                            ])>
                                                {{ $submission->passed ? __('Passed') : __('Not passed') }}
                                            </span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
