@props([
    'name',
])

<span
    {{ $attributes->class(['material-symbols-outlined leading-none select-none']) }}
    aria-hidden="true"
>{{ $name }}</span>
