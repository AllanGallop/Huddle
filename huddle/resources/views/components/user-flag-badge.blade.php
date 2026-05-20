@props(['name'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full bg-huddle-primary/15 px-2.5 py-0.5 text-xs font-medium text-huddle-primary dark:bg-huddle-primary/20']) }}>
    {{ $name }}
</span>
