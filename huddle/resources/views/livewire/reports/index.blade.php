<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl">{{ __('Reports') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Generate compact printable reports for active projects.') }}</flux:text>
    </div>

    <section class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-2">
            <flux:heading size="lg" class="inline-flex items-center gap-2">
                <x-material-icon name="assessment" class="text-[1.5rem] text-huddle-primary" />
                {{ __('Projects status report') }}
            </flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Build a lean A4 report for outstanding and in-progress projects. Filters apply to preview, PDF download, and emailed copies.') }}
            </flux:text>
        </div>

        <form method="GET" action="{{ route('reports.projects-status') }}" class="mt-6 space-y-6">
            <div class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Statuses') }}</p>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Choose which active project states to include.') }}</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @foreach (['outstanding', 'in-progress'] as $status)
                            <label class="inline-flex items-center gap-2 rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-200">
                                <input
                                    type="checkbox"
                                    name="statuses[]"
                                    value="{{ $status }}"
                                    @checked(in_array($status, $statuses, true))
                                    class="rounded border-zinc-300 text-huddle-primary focus:ring-huddle-primary"
                                >
                                {{ str($status)->headline() }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Filters') }}</p>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Use only the options you need to keep the report short.') }}</p>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm">
                            <span class="mb-1 block text-zinc-600 dark:text-zinc-300">{{ __('Leader') }}</span>
                            <select name="leader_id" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 shadow-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                                <option value="">{{ __('All leaders') }}</option>
                                @foreach ($this->leaders as $leader)
                                    <option value="{{ $leader->id }}" @selected($leader_id !== '' && (int) $leader_id === $leader->id)>{{ $leader->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        @if ($this->canViewFinancials)
                            <label class="block text-sm">
                                <span class="mb-1 block text-zinc-600 dark:text-zinc-300">{{ __('Financial status') }}</span>
                                <select name="financial_status" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 shadow-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                                    <option value="">{{ __('Any') }}</option>
                                    @foreach (\App\Models\Project::FINANCIAL_STATUSES as $status)
                                        <option value="{{ $status }}" @selected($financial_status === $status)>{{ str($status)->headline() }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif

                        <label class="block text-sm">
                            <span class="mb-1 block text-zinc-600 dark:text-zinc-300">{{ __('Due date from') }}</span>
                            <input type="date" name="due_date_from" value="{{ $due_date_from }}" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 shadow-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                        </label>

                        <label class="block text-sm">
                            <span class="mb-1 block text-zinc-600 dark:text-zinc-300">{{ __('Due date to') }}</span>
                            <input type="date" name="due_date_to" value="{{ $due_date_to }}" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 shadow-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                        </label>

                        <label class="block text-sm">
                            <span class="mb-1 block text-zinc-600 dark:text-zinc-300">{{ __('Volunteer demand') }}</span>
                            <select name="volunteer_filter" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 shadow-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                                <option value="">{{ __('Any') }}</option>
                                <option value="required" @selected($volunteer_filter === 'required')>{{ __('Volunteers required') }}</option>
                                <option value="not_required" @selected($volunteer_filter === 'not_required')>{{ __('No volunteer call') }}</option>
                            </select>
                        </label>

                        <label class="flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 shadow-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-200">
                            <input
                                type="checkbox"
                                name="overdue_only"
                                value="1"
                                @checked($overdue_only)
                                class="rounded border-zinc-300 text-huddle-primary focus:ring-huddle-primary"
                            >
                            {{ __('Only overdue projects') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-huddle-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-huddle-primary/90">
                    <x-material-icon name="visibility" class="text-[1.125rem]" />
                    {{ __('Open report') }}
                </button>
                <button
                    type="submit"
                    formaction="{{ route('reports.projects-status.pdf') }}"
                    formmethod="GET"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                >
                    <x-material-icon name="download" class="text-[1.125rem]" />
                    {{ __('Download PDF') }}
                </button>
                <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    {{ __('Reset filters') }}
                </a>
            </div>
        </form>
    </section>
</div>
