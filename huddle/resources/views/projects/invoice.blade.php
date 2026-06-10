@php
    $forPdf = $forPdf ?? false;
    $bank = $bank ?? null;
@endphp

<x-layouts.document
    :title="__('Invoice') . ' — ' . $project->name"
    :project="$project"
    :for-pdf="$forPdf"
    :pdf-url="route('projects.invoice.pdf', $project)"
    :email-action="route('projects.invoice.email', $project)"
    :back-url="route('projects.show', $project)"
    :back-label="__('Back to project')"
    :recipient-email="$project->leader->email"
>
    <h1>{{ __('Invoice') }}</h1>
    <p class="meta">
        <strong>{{ $project->name }}</strong><br>
        {{ __('Project leader') }}: {{ $project->leader->name }}<br>
        @if ($project->invoiced_at)
            {{ __('Invoiced') }}: {{ $project->invoiced_at->format('j F Y') }}
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
                <td class="amount">{{ $project->formatMoney($project->invoice_amount) }}</td>
            </tr>
            @if ($project->deposit_amount)
                <tr>
                    <td>{{ __('Deposit received') }}</td>
                    <td class="amount">− {{ $project->formatMoney($project->deposit_amount) }}</td>
                </tr>
            @endif
            @if ($project->payment_amount)
                <tr>
                    <td>{{ __('Payment received') }}</td>
                    <td class="amount">− {{ $project->formatMoney($project->payment_amount) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>{{ __('Balance due') }}</td>
                <td class="amount">{{ $project->formatMoney($project->balanceDue()) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($project->invoice_notes)
        <div class="notes">
            <strong>{{ __('Notes') }}</strong><br>
            {{ $project->invoice_notes }}
        </div>
    @endif

    @if ($bank?->hasBankDetails())
        <div class="notes" style="margin-top: 1.5rem;">
            <strong>{{ __('Payment details') }}</strong>
            <dl style="margin: 0.75rem 0 0; font-size: 0.875rem;">
                @if ($bank->account_name)
                    <div style="margin-bottom: 0.25rem;"><span style="color: #71717a;">{{ __('Account name') }}:</span> {{ $bank->account_name }}</div>
                @endif
                @if ($bank->bank_name)
                    <div style="margin-bottom: 0.25rem;"><span style="color: #71717a;">{{ __('Bank') }}:</span> {{ $bank->bank_name }}</div>
                @endif
                @if ($bank->sort_code)
                    <div style="margin-bottom: 0.25rem;"><span style="color: #71717a;">{{ __('Sort code') }}:</span> {{ $bank->sort_code }}</div>
                @endif
                @if ($bank->account_number)
                    <div style="margin-bottom: 0.25rem;"><span style="color: #71717a;">{{ __('Account number') }}:</span> {{ $bank->account_number }}</div>
                @endif
                @if ($bank->iban)
                    <div style="margin-bottom: 0.25rem;"><span style="color: #71717a;">{{ __('IBAN') }}:</span> {{ $bank->iban }}</div>
                @endif
            </dl>
            @if ($bank->payment_instructions)
                <p style="margin: 0.75rem 0 0; white-space: pre-wrap;">{{ $bank->payment_instructions }}</p>
            @endif
        </div>
    @endif
</x-layouts.document>
