@php
    $includeComments = $includeComments ?? true;
    $showFinancials = $showFinancials ?? false;
    $filterSummary = $filterSummary ?? [];
    $compact = $compact ?? false;
@endphp

@if ($filterSummary !== [])
    <div @class([
        'mb-4 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-300',
        'report-filter-summary' => ! $compact,
    ])>
        <strong class="text-zinc-900 dark:text-white">{{ __('Filters applied') }}:</strong>
        {{ implode(' · ', $filterSummary) }}
    </div>
@endif

@if ($projects->isEmpty())
    <div class="rounded-lg border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-500 dark:border-zinc-600">
        {{ __('No projects matched the selected report filters.') }}
    </div>
@else
    <div class="space-y-5">
        @foreach ($projects as $project)
            <section
                wire:key="report-project-{{ $project->id }}"
                class="overflow-hidden rounded-xl border-2 border-zinc-300 bg-white shadow-sm dark:border-zinc-600 dark:bg-zinc-900"
                style="{{ $compact ? '' : 'page-break-inside: avoid; margin-bottom: 1.1rem; border: 2px solid #d4d4d8; border-radius: 0.5rem; background: #fff;' }}"
            >
                <header
                    class="border-b border-zinc-200 bg-teal-50/80 px-4 py-3 dark:border-zinc-700 dark:bg-teal-950/30"
                    style="{{ $compact ? '' : 'border-bottom: 1px solid #e4e4e7; background: #f0fdfa; padding: 0.7rem 0.85rem;' }}"
                >
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-bold tracking-wide text-huddle-primary" style="{{ $compact ? '' : 'color: #287878; font-weight: 700; font-size: 0.85rem;' }}">
                                #{{ $project->formattedId() }}
                            </p>
                            <h2 class="mt-0.5 text-base font-semibold text-zinc-900 dark:text-white" style="{{ $compact ? '' : 'margin: 0.15rem 0 0; font-size: 1rem; color: #18181b;' }}">
                                {{ $project->name }}
                            </h2>
                        </div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-300" style="{{ $compact ? '' : 'font-size: 0.78rem; color: #52525b; text-align: right;' }}">
                            <div><strong style="{{ $compact ? '' : 'color: #27272a;' }}">{{ $project->leader->name }}</strong></div>
                            <div>{{ __('Status') }}: {{ $project->statusLabel() }}</div>
                            <div>
                                {{ __('Due') }}:
                                @if ($project->due_date)
                                    {{ $project->due_date->format('j M Y') }}
                                    @if ($project->isOverdue())
                                        <span class="font-semibold text-red-600" style="color: #b91c1c; font-weight: 700;">({{ __('Overdue') }})</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400" style="{{ $compact ? '' : 'margin-top: 0.45rem; font-size: 0.72rem; color: #71717a;' }}">
                        <span>
                            {{ trans_choice(':count volunteer|:count volunteers', $project->volunteers_count, ['count' => $project->volunteers_count]) }}
                            @if ($project->volunteer_required)
                                ({{ __('needed') }})
                            @endif
                        </span>
                        @if ($project->volunteers_count > 0)
                            <span>
                                {{ $project->volunteers->pluck('user.name')->filter()->take(3)->join(', ') }}
                                @if ($project->volunteers_count > 3)
                                    {{ __(' +:count more', ['count' => $project->volunteers_count - 3]) }}
                                @endif
                            </span>
                        @endif
                        <span>
                            {{ trans_choice(':count comment|:count comments', $project->comments_count, ['count' => $project->comments_count]) }}
                        </span>
                        @if ($showFinancials && ($project->financial_status || $project->quote_amount || $project->invoice_amount))
                            <span>
                                {{ __('Finance') }}: {{ $project->financialStatusLabel() ?? __('Unspecified') }}
                                · {{ __('Quote') }} {{ $project->formatMoney($project->quote_amount) }}
                                · {{ __('Invoice') }} {{ $project->formatMoney($project->invoice_amount) }}
                                · {{ __('Balance') }} {{ $project->formatMoney($project->balanceDue()) }}
                            </span>
                        @endif
                    </div>
                </header>

                <div class="px-4 py-3" style="{{ $compact ? '' : 'padding: 0.75rem 0.85rem;' }}">
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500" style="{{ $compact ? '' : 'margin: 0 0 0.3rem; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #71717a;' }}">
                        {{ __('Description') }}
                    </p>
                    <div class="whitespace-pre-wrap text-sm leading-relaxed text-zinc-800 dark:text-zinc-200" style="{{ $compact ? '' : 'white-space: pre-wrap; word-break: break-word; color: #27272a; font-size: 0.8rem; line-height: 1.4;' }}">
                        {{ $project->description }}
                    </div>
                </div>

                @if ($includeComments)
                    <div class="border-t border-zinc-200 bg-zinc-50/70 dark:border-zinc-700 dark:bg-zinc-800/40" style="{{ $compact ? '' : 'border-top: 1px solid #e4e4e7; background: #fafafa;' }}">
                        <div class="border-b border-zinc-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-700" style="{{ $compact ? '' : 'border-bottom: 1px solid #e4e4e7; padding: 0.45rem 0.85rem; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #71717a;' }}">
                            {{ __('Comments') }}
                        </div>

                        @if ($project->topLevelComments->isEmpty())
                            <p class="px-4 py-3 text-sm text-zinc-500" style="{{ $compact ? '' : 'padding: 0.65rem 0.85rem; color: #71717a; font-size: 0.78rem;' }}">
                                {{ __('No comments yet.') }}
                            </p>
                        @else
                            <div class="divide-y divide-zinc-200 dark:divide-zinc-700" style="{{ $compact ? '' : '' }}">
                                @foreach ($project->topLevelComments as $comment)
                                    <div class="px-4 py-3" style="{{ $compact ? '' : 'padding: 0.55rem 0.85rem; border-top: 1px solid #e4e4e7;' }}">
                                        <p class="text-xs text-zinc-500" style="{{ $compact ? '' : 'margin: 0 0 0.25rem; font-size: 0.7rem; color: #71717a;' }}">
                                            <strong class="text-zinc-800 dark:text-zinc-200" style="{{ $compact ? '' : 'color: #27272a;' }}">{{ $comment->user->name }}</strong>
                                            · {{ $comment->created_at->format('j M Y, H:i') }}
                                        </p>
                                        <div class="whitespace-pre-wrap text-sm text-zinc-800 dark:text-zinc-200" style="{{ $compact ? '' : 'white-space: pre-wrap; word-break: break-word; color: #27272a; font-size: 0.78rem; line-height: 1.35;' }}">
                                            {{ $comment->comment }}
                                        </div>

                                        @foreach ($comment->replies as $reply)
                                            <div class="mt-2 border-s-2 border-zinc-300 ps-3 dark:border-zinc-600" style="{{ $compact ? '' : 'margin-top: 0.4rem; margin-left: 0.35rem; padding-left: 0.55rem; border-left: 2px solid #d4d4d8;' }}">
                                                <p class="text-xs text-zinc-500" style="{{ $compact ? '' : 'margin: 0 0 0.2rem; font-size: 0.68rem; color: #71717a;' }}">
                                                    <strong class="text-zinc-800 dark:text-zinc-200" style="{{ $compact ? '' : 'color: #27272a;' }}">{{ $reply->user->name }}</strong>
                                                    · {{ $reply->created_at->format('j M Y, H:i') }}
                                                </p>
                                                <div class="whitespace-pre-wrap text-sm text-zinc-800 dark:text-zinc-200" style="{{ $compact ? '' : 'white-space: pre-wrap; word-break: break-word; color: #27272a; font-size: 0.76rem; line-height: 1.35;' }}">
                                                    {{ $reply->comment }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </section>
        @endforeach
    </div>
@endif
