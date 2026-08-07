@props(['type'])

@php
    $styles = match ($type) {
        'public' => 'bg-huddle-primary/25 text-huddle-primary dark:bg-huddle-primary/30',
        'private' => 'bg-huddle-alt/30 text-amber-900 dark:bg-huddle-alt/20 dark:text-huddle-alt',
        default => 'bg-zinc-500/15 text-zinc-600',
    };

    $dot = match ($type) {
        'public' => 'bg-huddle-primary',
        'private' => 'bg-huddle-alt',
        default => 'bg-zinc-400',
    };

    $label = str($type)->headline();
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium {$styles}"]) }}>
    <span class="size-1.5 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>
    {{ $label }}
</span>
