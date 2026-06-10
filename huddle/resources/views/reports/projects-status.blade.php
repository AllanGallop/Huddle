@php
    $forPdf = $forPdf ?? false;
    $filters = $filters ?? [];
    $filterSummary = $filterSummary ?? [];
    $showFinancials = $showFinancials ?? false;
    $detailColspan = $showFinancials ? 5 : 4;
@endphp

<x-layouts.document
    :title="__('Projects Status Report') . ' — ' . $generatedAt->format('j F Y')"
    :for-pdf="$forPdf"
    :pdf-url="route('reports.projects-status.pdf', $filters)"
    :email-action="route('reports.projects-status.email')"
    :back-url="route('reports.index', $filters)"
    :back-label="__('Back to reports')"
    :recipient-email="auth()->user()?->email ?? ''"
    :email-fields="$filters"
    paper-orientation="portrait"
    paper-margin="6mm"
    :force-light-mode="true"
    :show-header="false"
    :show-footer="false"
>
    <style>
        .report-table {
            table-layout: fixed;
        }
        .report-table th,
        .report-table td {
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }
        .report-muted {
            color: #71717a;
            font-size: 0.7rem;
        }
        .report-detail {
            white-space: pre-wrap;
            word-break: break-word;
            color: #27272a;
            font-size: 0.76rem;
            line-height: 1.35;
        }
        .report-detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 0.6rem;
        }
        .report-detail-box {
            padding: 0.55rem;
            border: 1px solid #e4e4e7;
            border-radius: 0.35rem;
            background: #fafafa;
        }
        .report-comment-list {
            display: grid;
            gap: 0.35rem;
        }
        .report-comment {
            padding-bottom: 0.35rem;
            border-bottom: 1px solid #e4e4e7;
        }
        .report-comment:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }
        .report-reply {
            margin-top: 0.35rem;
            margin-left: 0.5rem;
            padding-left: 0.5rem;
            border-left: 2px solid #d4d4d8;
        }
        .report-block {
            margin-top: 0.1rem;
        }
    </style>

    <h1>{{ __('Projects Status Report') }}</h1>
    <p class="meta" style="margin-bottom: 0.55rem; font-size: 0.8rem;">
        <strong>{{ __('Active project register') }}</strong><br>
        {{ __('Generated') }}: {{ $generatedAt->format('j F Y, H:i') }}
    </p>

    @if ($filterSummary !== [])
        <div style="margin-bottom: 0.6rem; padding: 0.4rem 0.55rem; border: 1px solid #e4e4e7; border-radius: 0.35rem; background: #fafafa; font-size: 0.74rem; color: #3f3f46;">
            <strong style="color: #18181b;">{{ __('Filters applied') }}:</strong> {{ implode(' · ', $filterSummary) }}
        </div>
    @endif

    @if ($projects->isEmpty())
        <div class="notes">
            {{ __('No projects matched the selected report filters.') }}
        </div>
    @else
        <table class="report-table" style="margin-top: 0; margin-bottom: 0; font-size: 0.76rem;">
            <thead>
                <tr>
                    <th style="width: 10%;">{{ __('ID') }}</th>
                    <th style="width: {{ $showFinancials ? '23%' : '30%' }};">{{ __('Project') }}</th>
                    <th style="width: {{ $showFinancials ? '22%' : '30%' }};">{{ __('Overview') }}</th>
                    @if ($showFinancials)
                        <th style="width: 23%;">{{ __('Finance') }}</th>
                    @endif
                    <th style="width: {{ $showFinancials ? '22%' : '30%' }};">{{ __('Engagement') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projects as $project)
                    <tr>
                        <td style="white-space: nowrap; font-weight: 700; color: #287878;">{{ $project->formattedId() }}</td>
                        <td>
                            <strong>{{ $project->name }}</strong>
                        </td>
                        <td>
                            <div class="report-block"><strong>{{ $project->leader->name }}</strong></div>
                            <div class="report-muted">{{ __('Status') }}: {{ $project->statusLabel() }}</div>
                            <div class="report-muted">
                                {{ __('Due') }}:
                                @if ($project->due_date)
                                    {{ $project->due_date->format('j M Y') }}
                                    @if ($project->isOverdue())
                                        <span style="color: #b91c1c; font-weight: 700;">({{ __('Overdue') }})</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </div>
                        </td>
                        @if ($showFinancials)
                            <td>
                                @if ($project->financial_status || $project->quote_amount || $project->invoice_amount || $project->deposit_amount || $project->payment_amount)
                                    <div>{{ $project->financialStatusLabel() ?? __('Unspecified') }}</div>
                                    <div class="report-muted">
                                        {{ __('Quote') }}: {{ $project->formatMoney($project->quote_amount) }}
                                    </div>
                                    <div class="report-muted">
                                        {{ __('Invoice') }}: {{ $project->formatMoney($project->invoice_amount) }}
                                    </div>
                                    <div class="report-muted">
                                        {{ __('Balance') }}: {{ $project->formatMoney($project->balanceDue()) }}
                                    </div>
                                @else
                                    <span style="color: #a1a1aa;">—</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            <div class="report-block">
                                {{ trans_choice(':count volunteer|:count volunteers', $project->volunteers_count, ['count' => $project->volunteers_count]) }}
                                @if ($project->volunteer_required)
                                    <span class="report-muted">({{ __('needed') }})</span>
                                @endif
                            </div>
                            @if ($project->volunteers_count > 0)
                                <div class="report-muted">
                                    {{ $project->volunteers->pluck('user.name')->filter()->take(3)->join(', ') }}
                                    @if ($project->volunteers_count > 3)
                                        {{ __(' +:count more', ['count' => $project->volunteers_count - 3]) }}
                                    @endif
                                </div>
                            @endif
                            <div class="report-muted" style="margin-top: 0.15rem;">
                                {{ trans_choice(':count comment|:count comments', $project->comments_count, ['count' => $project->comments_count]) }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="{{ $detailColspan }}" style="padding-top: 0.3rem; padding-bottom: 0.55rem;">
                            <div class="report-detail-grid">
                                <div class="report-detail-box">
                                    <div style="margin-bottom: 0.25rem; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #71717a;">
                                        {{ __('Description') }}
                                    </div>
                                    <div class="report-detail">{{ $project->description }}</div>
                                </div>
                                <div class="report-detail-box">
                                    <div style="margin-bottom: 0.25rem; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #71717a;">
                                        {{ __('Comments') }}
                                    </div>

                                    @if ($project->topLevelComments->isEmpty())
                                        <div class="report-muted">{{ __('No comments yet.') }}</div>
                                    @else
                                        <div class="report-comment-list">
                                            @foreach ($project->topLevelComments as $comment)
                                                <div class="report-comment">
                                                    <div class="report-muted" style="margin-bottom: 0.2rem;">
                                                        <strong style="color: #27272a;">{{ $comment->user->name }}</strong>
                                                        · {{ $comment->created_at->format('j M Y, H:i') }}
                                                    </div>
                                                    <div class="report-detail">{{ $comment->comment }}</div>

                                                    @foreach ($comment->replies as $reply)
                                                        <div class="report-reply">
                                                            <div class="report-muted" style="margin-bottom: 0.2rem;">
                                                                <strong style="color: #27272a;">{{ $reply->user->name }}</strong>
                                                                · {{ $reply->created_at->format('j M Y, H:i') }}
                                                            </div>
                                                            <div class="report-detail">{{ $reply->comment }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-layouts.document>
