@php($forPdf = $forPdf ?? false)

<x-layouts.document
    :title="__('Quote') . ' — ' . $project->name"
    :project="$project"
    document-type="quote"
    :for-pdf="$forPdf"
>
    <h1>{{ __('Quote') }}</h1>
    <p class="meta">
        <strong>{{ $project->name }}</strong><br>
        {{ __('Project leader') }}: {{ $project->leader->name }}<br>
        @if ($project->quoted_at)
            {{ __('Quoted') }}: {{ $project->quoted_at->format('j F Y') }}
        @else
            {{ __('Date') }}: {{ now()->format('j F Y') }}
        @endif
        @if ($project->due_date)
            <br>{{ __('Due date') }}: {{ $project->due_date->format('j F Y') }}
        @endif
    </p>

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
