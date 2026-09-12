@props(['name'])

@php
    $palette = [
        'bg-teal-100 text-teal-950 ring-1 ring-inset ring-teal-700/25 dark:bg-huddle-primary/30 dark:text-teal-100 dark:ring-teal-300/30',
        'bg-amber-100 text-amber-950 ring-1 ring-inset ring-amber-700/25 dark:bg-huddle-alt/25 dark:text-amber-100 dark:ring-amber-300/30',
        'bg-lime-100 text-lime-950 ring-1 ring-inset ring-lime-700/30 dark:bg-huddle-comp/25 dark:text-lime-100 dark:ring-lime-300/25',
        'bg-fuchsia-100 text-fuchsia-950 ring-1 ring-inset ring-fuchsia-700/25 dark:bg-huddle-accent/25 dark:text-fuchsia-100 dark:ring-fuchsia-300/30',
    ];

    $styles = $palette[crc32((string) $name) % count($palette)];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {$styles}"]) }}>
    {{ $name }}
</span>
