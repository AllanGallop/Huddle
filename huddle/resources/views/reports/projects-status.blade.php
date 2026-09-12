@php
    $forPdf = $forPdf ?? false;
    $filters = $filters ?? [];
    $filterSummary = $filterSummary ?? [];
    $showFinancials = $showFinancials ?? false;
    $includeComments = $includeComments ?? true;
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
    paper-margin="8mm"
    :force-light-mode="true"
    :show-header="false"
    :show-footer="false"
>
    <h1>{{ __('Projects Status Report') }}</h1>
    <p class="meta" style="margin-bottom: 0.75rem; font-size: 0.8rem;">
        <strong>{{ __('Active project register') }}</strong><br>
        {{ __('Generated') }}: {{ $generatedAt->format('j F Y, H:i') }}
    </p>

    @include('reports.partials.projects-status-body', [
        'projects' => $projects,
        'filterSummary' => $filterSummary,
        'showFinancials' => $showFinancials,
        'includeComments' => $includeComments,
        'compact' => false,
    ])
</x-layouts.document>
