@props(['status'])

@php
    $styles = match ($status) {
        'draft' => 'bg-zinc-200 text-zinc-700 dark:bg-zinc-500/15 dark:text-zinc-300',
        'published' => 'bg-huddle-comp/30 text-green-800 dark:bg-huddle-comp/20 dark:text-huddle-comp',
        'cancelled' => 'bg-huddle-accent/20 text-fuchsia-900 dark:bg-huddle-accent/15 dark:text-huddle-accent',
        'archived' => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-500/10 dark:text-zinc-500',
        default => 'bg-zinc-200 text-zinc-700 dark:bg-zinc-500/15 dark:text-zinc-600',
    };

    $label = str($status)->headline();
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$styles}"]) }}>
    {{ $label }}
</span>
