@props(['user'])

<div {{ $attributes->merge(['class' => 'space-y-6']) }}>
    <div>
        <flux:heading size="lg" class="inline-flex items-center gap-2">
            <x-material-icon name="card_membership" class="text-[1.375rem] text-huddle-primary" />
            {{ __('Membership') }}
        </flux:heading>

        @php
            $status = $user->membershipStatus();
            $latest = $user->latestMembershipRenewalAssignment();
        @endphp

        @if ($latest === null)
            <flux:text class="mt-3">{{ __('No membership periods assigned.') }}</flux:text>
        @else
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="font-medium text-zinc-900 dark:text-white">{{ $latest->membershipRenewal->name }}</span>
                <span @class([
                    'inline-flex shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium',
                    'bg-huddle-comp/20 text-huddle-comp' => $status === 'active',
                    'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => $status === 'expired',
                ])>
                    {{ $status === 'active' ? __('Active') : __('Expired') }}
                </span>
                <flux:text class="text-sm text-zinc-500">
                    {{ $latest->membershipRenewal->start_date->format('j M Y') }}
                    –
                    {{ $latest->membershipRenewal->end_date->format('j M Y') }}
                </flux:text>
            </div>

            @if ($user->membershipRenewalAssignments->count() > 1)
                <ul class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($user->membershipRenewalAssignments->sortByDesc(fn ($a) => $a->membershipRenewal->name) as $assignment)
                        <li wire:key="membership-renewal-{{ $assignment->id }}" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <span class="text-zinc-700 dark:text-zinc-300">{{ $assignment->membershipRenewal->name }}</span>
                            <span @class([
                                'inline-flex shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium',
                                'bg-huddle-comp/20 text-huddle-comp' => $assignment->membershipRenewal->isCurrent(),
                                'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => ! $assignment->membershipRenewal->isCurrent(),
                            ])>
                                {{ $assignment->membershipRenewal->isCurrent() ? __('Current period') : __('Past') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif
    </div>

    <flux:separator />

    <div>
        <flux:heading size="lg" class="inline-flex items-center gap-2">
            <x-material-icon name="sell" class="text-[1.375rem] text-huddle-primary" />
            {{ __('User tags') }}
        </flux:heading>

        @if ($user->flags->isEmpty())
            <flux:text class="mt-3">{{ __('No tags assigned.') }}</flux:text>
        @else
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($user->flags as $flag)
                    <x-user-flag-badge :name="$flag->name" wire:key="membership-flag-{{ $flag->id }}" />
                @endforeach
            </div>
        @endif
    </div>

    <flux:separator />

    <div>
        <flux:heading size="lg" class="inline-flex items-center gap-2">
            <x-material-icon name="verified" class="text-[1.375rem] text-huddle-primary" />
            {{ __('Accreditations') }}
        </flux:heading>

        @if ($user->accreditationAssignments->isEmpty())
            <flux:text class="mt-3">{{ __('No accreditations assigned.') }}</flux:text>
        @else
            <ul class="mt-3 divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($user->accreditationAssignments as $assignment)
                    <li wire:key="membership-accreditation-{{ $assignment->id }}" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $assignment->accreditation->name }}</span>
                        <span @class([
                            'inline-flex shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium',
                            'bg-huddle-comp/20 text-huddle-comp' => $assignment->is_active && $assignment->accreditation->is_active,
                            'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300' => ! $assignment->is_active || ! $assignment->accreditation->is_active,
                        ])>
                            @if ($assignment->is_active && $assignment->accreditation->is_active)
                                {{ __('Active') }}
                            @else
                                {{ __('Inactive') }}
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
