@props([
    'mode' => 'browse', // browse|mentors|manage
])

@php
    $canManage = auth()->user()->canAccessMentors();
@endphp

<nav class="flex gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800/60" aria-label="{{ __('Accreditation views') }}">
    <a
        href="{{ route('accreditations.index') }}"
        wire:navigate
        @class([
            'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
            'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $mode === 'browse',
            'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $mode !== 'browse',
        ])
    >
        <span class="inline-flex items-center justify-center gap-2">
            <x-material-icon name="visibility" class="text-[1.125rem]" />
            {{ __('Browse') }}
        </span>
    </a>
    <a
        href="{{ route('accreditations.index', ['tab' => 'mentors']) }}"
        wire:navigate
        @class([
            'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
            'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $mode === 'mentors',
            'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $mode !== 'mentors',
        ])
    >
        <span class="inline-flex items-center justify-center gap-2">
            <x-material-icon name="supervisor_account" class="text-[1.125rem]" />
            {{ __('Mentors') }}
        </span>
    </a>
    @if ($canManage)
        <a
            href="{{ route('mentors.index') }}"
            wire:navigate
            @class([
                'flex-1 rounded-md px-4 py-2 text-sm font-medium transition sm:flex-none',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' => $mode === 'manage',
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $mode !== 'manage',
            ])
        >
            <span class="inline-flex items-center justify-center gap-2">
                <x-material-icon name="settings" class="text-[1.125rem]" />
                {{ __('Manage') }}
            </span>
        </a>
    @endif
</nav>
