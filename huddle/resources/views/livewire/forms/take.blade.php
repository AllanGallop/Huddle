<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:link :href="route('forms.index')" wire:navigate class="inline-flex items-center gap-1 text-sm">
            <x-material-icon name="arrow_back" class="text-[1rem]" />
            {{ __('Back to forms') }}
        </flux:link>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <flux:heading size="xl">{{ $form->title }}</flux:heading>
            <span @class([
                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                'bg-huddle-primary/15 text-huddle-primary' => $form->isSurvey(),
                'bg-amber-500/15 text-amber-700 dark:text-amber-300' => $form->isExam(),
            ])>
                {{ $form->isExam() ? __('Exam') : __('Survey') }}
            </span>
        </div>
        @if ($form->description)
            <flux:text class="mt-2">{{ $form->description }}</flux:text>
        @endif
        @if ($form->isExam() && ! $submitted)
            <flux:text class="mt-2 text-sm text-zinc-500">
                {{ __('Pass mark: :percent%', ['percent' => $form->pass_percentage]) }}
            </flux:text>
        @endif
    </div>

    @if (session('status'))
        <div @class([
            'rounded-lg border px-4 py-3 text-sm',
            'border-huddle-comp/40 bg-huddle-comp/10 text-zinc-800 dark:text-zinc-200' => ! $submission || $submission->passed !== false,
            'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200' => $submission && $submission->passed === false,
        ])>
            {{ session('status') }}
        </div>
    @endif

    @if ($submitted && $submission && $form->isExam())
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
            <flux:heading size="lg">{{ __('Your result') }}</flux:heading>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <span @class([
                    'inline-flex rounded-full px-3 py-1 text-sm font-medium',
                    'bg-huddle-comp/20 text-huddle-comp' => $submission->passed,
                    'bg-red-500/15 text-red-700 dark:text-red-300' => ! $submission->passed,
                ])>
                    {{ $submission->passed ? __('Passed') : __('Not passed') }}
                </span>
                <flux:text>
                    {{ __(':score of :max points (:percent%)', [
                        'score' => $submission->score,
                        'max' => $submission->max_score,
                        'percent' => $submission->scorePercentage(),
                    ]) }}
                </flux:text>
            </div>
        </div>
    @endif

    <form wire:submit="submit" class="space-y-6">
        @foreach ($form->questions as $question)
            <div wire:key="question-{{ $question->id }}" class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
                <flux:heading size="md" class="mb-4">
                    {{ $question->body }}
                    @if ($form->isExam() && $question->points > 0)
                        <span class="ms-2 text-sm font-normal text-zinc-500">({{ trans_choice(':count point|:count points', $question->points, ['count' => $question->points]) }})</span>
                    @endif
                </flux:heading>

                @if ($question->isYesNo())
                    <flux:radio.group wire:model="answers.{{ $question->id }}" :disabled="$submitted">
                        <flux:radio value="1" :label="__('Yes')" />
                        <flux:radio value="0" :label="__('No')" />
                    </flux:radio.group>
                @else
                    <flux:radio.group wire:model="answers.{{ $question->id }}" :disabled="$submitted">
                        @foreach ($question->options as $option)
                            <flux:radio wire:key="option-{{ $option->id }}" :value="(string) $option->id" :label="$option->label" />
                        @endforeach
                    </flux:radio.group>
                @endif

                @error("answers.{$question->id}")
                    <flux:text class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror
            </div>
        @endforeach

        @unless ($submitted)
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">
                    <span class="inline-flex items-center gap-2">
                        <x-material-icon name="send" class="text-[1.25rem]" />
                        {{ __('Submit') }}
                    </span>
                </flux:button>
            </div>
        @endunless
    </form>
</div>
