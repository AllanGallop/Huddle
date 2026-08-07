@props(['status'])

@php
    $styles = match ($status) {
        'draft' => 'bg-zinc-200 text-zinc-700 dark:bg-zinc-500/20 dark:text-zinc-300',
        'outstanding' => 'bg-huddle-alt/35 text-amber-900 dark:bg-huddle-alt/25 dark:text-huddle-alt',
        'in-progress' => 'bg-huddle-primary/25 text-huddle-primary dark:bg-huddle-primary/30',
        'completed' => 'bg-huddle-comp/40 text-green-900 dark:bg-huddle-comp/25 dark:text-huddle-comp',
        'cancelled' => 'bg-huddle-accent/25 text-fuchsia-950 dark:bg-huddle-accent/20 dark:text-huddle-accent',
        'archived' => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-500/15 dark:text-zinc-500',
        default => 'bg-zinc-200 text-zinc-700 dark:bg-zinc-500/15 dark:text-zinc-600',
    };

    $dot = match ($status) {
        'draft' => 'bg-zinc-500',
        'outstanding' => 'bg-huddle-alt',
        'in-progress' => 'bg-huddle-primary',
        'completed' => 'bg-huddle-comp',
        'cancelled' => 'bg-huddle-accent',
        'archived' => 'bg-zinc-400',
        default => 'bg-zinc-400',
    };

    $label = str($status)->headline();
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium {$styles}"]) }}>
    <span class="size-1.5 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>
    {{ $label }}
</span>
