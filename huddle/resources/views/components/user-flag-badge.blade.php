@props(['name'])

@php
    $palette = [
        'bg-huddle-primary/20 text-huddle-primary dark:bg-huddle-primary/25',
        'bg-huddle-alt/25 text-amber-800 dark:bg-huddle-alt/20 dark:text-huddle-alt',
        'bg-huddle-comp/30 text-green-800 dark:bg-huddle-comp/20 dark:text-huddle-comp',
        'bg-huddle-accent/20 text-fuchsia-900 dark:bg-huddle-accent/15 dark:text-huddle-accent',
    ];

    $styles = $palette[crc32((string) $name) % count($palette)];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {$styles}"]) }}>
    {{ $name }}
</span>
