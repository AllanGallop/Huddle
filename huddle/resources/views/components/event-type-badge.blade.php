@props(['type'])

@php
    $styles = match ($type) {
        'public' => 'bg-huddle-primary/15 text-huddle-primary',
        'private' => 'bg-huddle-alt/20 text-huddle-alt',
        default => 'bg-zinc-500/15 text-zinc-600',
    };

    $label = str($type)->headline();
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$styles}"]) }}>
    {{ $label }}
</span>
