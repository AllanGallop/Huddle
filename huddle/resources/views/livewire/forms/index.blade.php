<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl" class="inline-flex items-center gap-2">
                <x-material-icon name="ballot" class="text-[1.75rem] text-huddle-primary" />
                {{ __('Forms') }}
            </flux:heading>
            <flux:text class="mt-1">{{ __('Complete surveys and exams published by mentors and admins.') }}</flux:text>
        </div>
        @if ($this->canManage)
            <flux:button variant="primary" :href="route('forms.manage.index')" wire:navigate>
                <span class="inline-flex items-center gap-2">
                    <x-material-icon name="edit_note" class="text-[1.25rem]" />
                    {{ __('Manage forms') }}
                </span>
            </flux:button>
        @endif
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-huddle-comp/40 bg-huddle-comp/10 px-4 py-3 text-sm text-zinc-800 dark:text-zinc-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        @if ($this->publishedForms->isEmpty())
            <div class="px-5 py-12 text-center">
                <flux:text>{{ __('No published forms yet.') }}</flux:text>
            </div>
        @else
            <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($this->publishedForms as $publishedForm)
                    @php $submission = $this->mySubmissions->get($publishedForm->id); @endphp
                    <li wire:key="form-{{ $publishedForm->id }}" class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <flux:heading size="lg">{{ $publishedForm->title }}</flux:heading>
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-huddle-primary/15 text-huddle-primary' => $publishedForm->isSurvey(),
                                    'bg-amber-500/15 text-amber-700 dark:text-amber-300' => $publishedForm->isExam(),
                                ])>
                                    {{ $publishedForm->isExam() ? __('Exam') : __('Survey') }}
                                </span>
                                @if ($submission)
                                    @if ($publishedForm->isExam() && $submission->passed !== null)
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            'bg-huddle-comp/20 text-huddle-comp' => $submission->passed,
                                            'bg-red-500/15 text-red-700 dark:text-red-300' => ! $submission->passed,
                                        ])>
                                            {{ $submission->passed ? __('Passed') : __('Not passed') }}
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-zinc-500/15 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                            {{ __('Completed') }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                            @if ($publishedForm->description)
                                <flux:text class="mt-1 text-sm">{{ $publishedForm->description }}</flux:text>
                            @endif
                            <flux:text class="mt-2 text-sm text-zinc-500">
                                {{ trans_choice(':count question|:count questions', $publishedForm->questions_count, ['count' => $publishedForm->questions_count]) }}
                                @if ($submission && $publishedForm->isExam())
                                    · {{ __('Score: :score/:max', ['score' => $submission->score, 'max' => $submission->max_score]) }}
                                @endif
                            </flux:text>
                        </div>
                        <flux:button variant="{{ $submission ? 'ghost' : 'primary' }}" :href="route('forms.take', $publishedForm)" wire:navigate>
                            {{ $submission ? __('View response') : __('Start') }}
                        </flux:button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
