<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:link :href="route('forms.manage.index')" wire:navigate class="inline-flex items-center gap-1 text-sm">
            <x-material-icon name="arrow_back" class="text-[1rem]" />
            {{ __('Back to manage forms') }}
        </flux:link>
        <flux:heading size="xl" class="mt-2">{{ $this->title() }}</flux:heading>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-huddle-comp/40 bg-huddle-comp/10 px-4 py-3 text-sm text-zinc-800 dark:text-zinc-200">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
            <flux:heading size="lg" class="mb-4">{{ __('Form details') }}</flux:heading>

            <div class="space-y-4">
                <flux:input wire:model="title" :label="__('Title')" required />
                <flux:textarea wire:model="description" :label="__('Description (optional)')" rows="3" />

                <flux:select wire:model.live="type" :label="__('Type')">
                    <flux:select.option value="survey">{{ __('Survey') }}</flux:select.option>
                    <flux:select.option value="exam">{{ __('Exam (scored)') }}</flux:select.option>
                </flux:select>

                @if ($type === 'exam')
                    <flux:input wire:model="pass_percentage" type="number" min="1" max="100" :label="__('Pass percentage')" required />
                @endif

                <flux:switch wire:model="is_published" :label="__('Published')" />
                <flux:text class="text-sm text-zinc-500">{{ __('Members can only complete published forms.') }}</flux:text>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <flux:heading size="lg">{{ __('Questions') }}</flux:heading>
                <div class="flex flex-wrap gap-2">
                    <flux:button type="button" variant="ghost" size="sm" wire:click="addQuestion('yes_no')">
                        {{ __('Add yes/no') }}
                    </flux:button>
                    <flux:button type="button" variant="ghost" size="sm" wire:click="addQuestion('multiple_choice')">
                        {{ __('Add multiple choice') }}
                    </flux:button>
                </div>
            </div>

            @if ($questionDrafts === [])
                <flux:text class="mt-4">{{ __('Add at least one question.') }}</flux:text>
            @endif

            <div class="mt-6 space-y-6">
                @foreach ($questionDrafts as $qIndex => $draft)
                    <div wire:key="draft-{{ $draft['key'] }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-start justify-between gap-3">
                            <flux:text class="text-xs font-medium uppercase tracking-wide text-zinc-500">
                                {{ $draft['type'] === 'yes_no' ? __('Yes / No') : __('Multiple choice') }}
                            </flux:text>
                            <flux:button type="button" size="sm" variant="danger" wire:click="removeQuestion('{{ $draft['key'] }}')">
                                <x-material-icon name="delete" class="text-[1rem]" />
                            </flux:button>
                        </div>

                        <div class="mt-3 space-y-4">
                            <flux:textarea wire:model="questionDrafts.{{ $qIndex }}.body" :label="__('Question')" rows="2" required />

                            @if ($type === 'exam')
                                <flux:input wire:model="questionDrafts.{{ $qIndex }}.points" type="number" min="0" :label="__('Points')" />
                            @endif

                            @if ($draft['type'] === 'yes_no' && $type === 'exam')
                                <flux:select wire:model="questionDrafts.{{ $qIndex }}.correct_yes_no" :label="__('Correct answer')">
                                    <flux:select.option :value="1">{{ __('Yes') }}</flux:select.option>
                                    <flux:select.option :value="0">{{ __('No') }}</flux:select.option>
                                </flux:select>
                            @endif

                            @if ($draft['type'] === 'multiple_choice')
                                <div class="space-y-3">
                                    <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Options') }}</flux:text>
                                    @foreach ($draft['options'] as $oIndex => $option)
                                        <div wire:key="opt-{{ $option['key'] }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
                                            <div class="flex-1">
                                                <flux:input wire:model="questionDrafts.{{ $qIndex }}.options.{{ $oIndex }}.label" :label="__('Option :n', ['n' => $oIndex + 1])" />
                                            </div>
                                            @if ($type === 'exam')
                                                <flux:button
                                                    type="button"
                                                    size="sm"
                                                    :variant="$option['is_correct'] ? 'primary' : 'ghost'"
                                                    wire:click="setCorrectOption('{{ $draft['key'] }}', '{{ $option['key'] }}')"
                                                >
                                                    {{ $option['is_correct'] ? __('Correct') : __('Set correct') }}
                                                </flux:button>
                                            @endif
                                            @if (count($draft['options']) > 2)
                                                <flux:button type="button" size="sm" variant="danger" wire:click="removeOption('{{ $draft['key'] }}', '{{ $option['key'] }}')">
                                                    <x-material-icon name="close" class="text-[1rem]" />
                                                </flux:button>
                                            @endif
                                        </div>
                                    @endforeach
                                    <flux:button type="button" size="sm" variant="ghost" wire:click="addOption('{{ $draft['key'] }}')">
                                        {{ __('Add option') }}
                                    </flux:button>
                                </div>
                                @error("questionDrafts.{$qIndex}.options")
                                    <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
                                @enderror
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-2">
            @if ($form)
                <flux:button variant="ghost" :href="route('forms.manage.submissions', $form)" wire:navigate>
                    {{ __('View responses') }}
                </flux:button>
            @endif
            <flux:button type="submit" variant="primary">
                <span class="inline-flex items-center gap-2">
                    <x-material-icon name="save" class="text-[1.25rem]" />
                    {{ __('Save form') }}
                </span>
            </flux:button>
        </div>
    </form>
</div>
