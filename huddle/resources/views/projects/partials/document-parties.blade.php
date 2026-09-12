@props(['project'])

@php
    $customer = $project->customer;
@endphp

<div class="parties">
    <div class="parties-col">
        <span class="parties-label">{{ __('Project') }}</span>
        <p class="parties-name">{{ $project->name }}</p>
        <p class="parties-detail">{{ __('Ref') }}: #{{ $project->formattedId() }}</p>
        <p class="parties-detail">{{ __('Project leader') }}: {{ $project->leader->name }}</p>
        @if ($project->due_date)
            <p class="parties-detail">{{ __('Due date') }}: {{ $project->due_date->format('j F Y') }}</p>
        @endif
    </div>

    @if ($customer)
        <div class="parties-col">
            <span class="parties-label">{{ __('Bill to') }}</span>
            <p class="parties-name">{{ $customer->name }}</p>
            <p class="parties-detail">{{ $customer->typeLabel() }}</p>
            @if ($customer->address)
                <p class="parties-detail">{!! nl2br(e($customer->address)) !!}</p>
            @endif
            @if ($customer->email)
                <p class="parties-detail">{{ $customer->email }}</p>
            @endif
            @if ($customer->telephone)
                <p class="parties-detail">{{ $customer->telephone }}</p>
            @endif
        </div>
    @endif
</div>
