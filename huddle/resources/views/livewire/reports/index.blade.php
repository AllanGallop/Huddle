<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl">{{ __('Reports') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Generate compact printable reports for active projects.') }}</flux:text>
    </div>

    <section class="rounded-xl border border-zinc-200 border-s-4 border-s-huddle-primary bg-zinc-50/80 p-5 dark:border-zinc-700 dark:border-s-huddle-primary dark:bg-zinc-800/40">
        <div class="flex flex-col gap-2">
            <flux:heading size="lg" class="inline-flex items-center gap-2">
                <x-material-icon name="assessment" class="text-[1.5rem] text-huddle-primary" />
                {{ __('Projects status report') }}
            </flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Filters update the preview below. Use printable / PDF / email for the finished report.') }}
            </flux:text>
        </div>

        <div class="mt-6 space-y-6">
            <div class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Statuses') }}</p>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Choose which active project states to include.') }}</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach (['outstanding', 'in-progress'] as $status)
                            <label class="inline-flex items-center gap-2 rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 dark:border-zinc-600 dark:bg-zinc-950 dark:text-zinc-200">
                                <input
                                    type="checkbox"
                                    wire:model.live="statuses"
                                    value="{{ $status }}"
                                    class="rounded border-zinc-300 text-huddle-primary focus:ring-huddle-primary"
                                >
                                {{ str($status)->headline() }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Filters') }}</p>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Use only the options you need to keep the report short.') }}</p>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-member-select
                            :users="$this->leaders"
                            :selected-id="$leader_id"
                            wire-model="leader_id"
                            :label="__('Leader')"
                            :placeholder="__('All leaders')"
                            :allow-clear="true"
                            :clear-label="__('All leaders')"
                            empty-value=""
                            :show-email="false"
                        />

                        @if ($this->canViewFinancials)
                            <flux:select wire:model.live="financial_status" :label="__('Financial status')">
                                <flux:select.option value="">{{ __('Any') }}</flux:select.option>
                                @foreach (\App\Models\Project::FINANCIAL_STATUSES as $status)
                                    <flux:select.option :value="$status">{{ str($status)->headline() }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif

                        <flux:input wire:model.live="due_date_from" type="date" :label="__('Due date from')" />
                        <flux:input wire:model.live="due_date_to" type="date" :label="__('Due date to')" />

                        <flux:select wire:model.live="volunteer_filter" :label="__('Volunteer demand')">
                            <flux:select.option value="">{{ __('Any') }}</flux:select.option>
                            <flux:select.option value="required">{{ __('Volunteers required') }}</flux:select.option>
                            <flux:select.option value="not_required">{{ __('No volunteer call') }}</flux:select.option>
                        </flux:select>

                        <label class="flex items-center gap-2 self-end rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 shadow-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-zinc-200">
                            <input
                                type="checkbox"
                                wire:model.live="overdue_only"
                                class="rounded border-zinc-300 text-huddle-primary focus:ring-huddle-primary"
                            >
                            {{ __('Only overdue projects') }}
                        </label>

                        <label class="flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 shadow-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-zinc-200 sm:col-span-2">
                            <input
                                type="checkbox"
                                wire:model.live="include_comments"
                                class="rounded border-zinc-300 text-huddle-primary focus:ring-huddle-primary"
                            >
                            {{ __('Include comments under each project description') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a
                    href="{{ route('reports.projects-status', $this->filterQuery) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-huddle-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-huddle-primary/90"
                >
                    <x-material-icon name="print" class="text-[1.125rem]" />
                    {{ __('Open printable') }}
                </a>
                <a
                    href="{{ route('reports.projects-status.pdf', $this->filterQuery) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                >
                    <x-material-icon name="download" class="text-[1.125rem]" />
                    {{ __('Download PDF') }}
                </a>
                <a href="{{ route('reports.index') }}" wire:navigate class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    {{ __('Reset filters') }}
                </a>
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:shadow-none">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <flux:heading size="lg">{{ __('Preview') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500">
                {{ trans_choice(':count project|:count projects', $this->preview['projects']->count(), ['count' => $this->preview['projects']->count()]) }}
                · {{ $this->preview['generatedAt']->format('j M Y, H:i') }}
            </flux:text>
        </div>

        @include('reports.partials.projects-status-body', [
            'projects' => $this->preview['projects'],
            'filterSummary' => $this->filterSummary,
            'showFinancials' => $this->canViewFinancials,
            'includeComments' => $include_comments,
            'compact' => true,
        ])
    </section>
</div>
