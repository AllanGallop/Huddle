<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:link :href="route('forms.index')" wire:navigate class="inline-flex items-center gap-1 text-sm">
                <x-material-icon name="arrow_back" class="text-[1rem]" />
                {{ __('Back to forms') }}
            </flux:link>
            <flux:heading size="xl" class="mt-2 inline-flex items-center gap-2">
                <x-material-icon name="edit_note" class="text-[1.75rem] text-huddle-primary" />
                {{ __('Manage forms') }}
            </flux:heading>
            <flux:text class="mt-1">{{ __('Create surveys and exams for members to complete.') }}</flux:text>
        </div>
        <flux:button variant="primary" :href="route('forms.manage.create')" wire:navigate>
            <span class="inline-flex items-center gap-2">
                <x-material-icon name="add" class="text-[1.25rem]" />
                {{ __('New form') }}
            </span>
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-huddle-comp/40 bg-huddle-comp/10 px-4 py-3 text-sm text-zinc-800 dark:text-zinc-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        @if ($this->forms->isEmpty())
            <div class="px-5 py-12 text-center">
                <flux:text>{{ __('No forms yet. Create your first survey or exam.') }}</flux:text>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                        <tr>
                            <th class="px-5 py-3">{{ __('Title') }}</th>
                            <th class="px-5 py-3">{{ __('Type') }}</th>
                            <th class="px-5 py-3">{{ __('Status') }}</th>
                            <th class="px-5 py-3">{{ __('Responses') }}</th>
                            <th class="px-5 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->forms as $managedForm)
                            <tr wire:key="managed-form-{{ $managedForm->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">{{ $managedForm->title }}</td>
                                <td class="px-5 py-3">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-huddle-primary/15 text-huddle-primary' => $managedForm->isSurvey(),
                                        'bg-amber-500/15 text-amber-700 dark:text-amber-300' => $managedForm->isExam(),
                                    ])>
                                        {{ $managedForm->isExam() ? __('Exam') : __('Survey') }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    {{ $managedForm->is_published ? __('Published') : __('Draft') }}
                                </td>
                                <td class="px-5 py-3 text-zinc-600 dark:text-zinc-300">{{ $managedForm->submissions_count }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-1">
                                        <flux:button size="sm" variant="ghost" :href="route('forms.manage.submissions', $managedForm)" wire:navigate>
                                            <x-material-icon name="analytics" class="text-[1rem]" />
                                        </flux:button>
                                        <flux:button size="sm" variant="ghost" :href="route('forms.manage.edit', $managedForm)" wire:navigate>
                                            <x-material-icon name="edit" class="text-[1rem]" />
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            wire:click="deleteForm({{ $managedForm->id }})"
                                            wire:confirm="{{ __('Delete :title? All responses will be removed.', ['title' => $managedForm->title]) }}"
                                        >
                                            <x-material-icon name="delete" class="text-[1rem]" />
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
