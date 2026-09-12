@php($forPdf = $forPdf ?? false)

<x-layouts.document
    :title="__('Quote') . ' — ' . $project->name"
    :project="$project"
    :for-pdf="$forPdf"
    :pdf-url="route('projects.quote.pdf', $project)"
    :email-action="route('projects.quote.email', $project)"
    :back-url="route('projects.show', $project)"
    :back-label="__('Back to project')"
    :recipient-email="$project->customer?->email ?: $project->leader->email"
>
    <h1>{{ __('Quote') }}</h1>
    <p class="meta">
        @if ($project->quoted_at)
            {{ __('Quoted') }}: {{ $project->quoted_at->format('j F Y') }}
        @else
            {{ __('Date') }}: {{ now()->format('j F Y') }}
        @endif
    </p>

    @include('projects.partials.document-parties', ['project' => $project])

    <table>
        <thead>
            <tr>
                <th>{{ __('Description') }}</th>
                <th class="amount">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $project->description }}</td>
                <td class="amount">{{ $project->formatMoney($project->quote_amount) }}</td>
            </tr>
            <tr class="total-row">
                <td>{{ __('Quote total') }}</td>
                <td class="amount">{{ $project->formatMoney($project->quote_amount) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($project->quote_notes)
        <div class="notes">
            <strong>{{ __('Notes') }}</strong><br>
            {{ $project->quote_notes }}
        </div>
    @endif
</x-layouts.document>
