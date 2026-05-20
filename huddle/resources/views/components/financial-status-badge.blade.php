@props(['status'])

@php
    $styles = match ($status) {
        'quoted' => 'bg-huddle-alt/25 text-amber-800 dark:bg-huddle-alt/20 dark:text-huddle-alt',
        'invoiced' => 'bg-huddle-primary/20 text-huddle-primary',
        'deposit_paid' => 'bg-huddle-accent/20 text-fuchsia-900 dark:bg-huddle-accent/15 dark:text-huddle-accent',
        'paid' => 'bg-huddle-comp/30 text-green-800 dark:bg-huddle-comp/20 dark:text-huddle-comp',
        default => 'bg-zinc-200 text-zinc-700 dark:bg-zinc-500/15 dark:text-zinc-600',
    };

    $label = str($status)->headline();
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$styles}"]) }}>
    {{ $label }}
</span>
