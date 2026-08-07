@blaze(fold: true)

@props([
    'as' => null,
    'external' => null,
    'accent' => true,
    'variant' => null,
    'strong' => false,
])

@php
$classes = Flux::classes()
    ->add('inline-flex items-center font-medium no-underline')
    ->add('transition duration-150 ease-out will-change-transform hover:scale-[1.03] hover:opacity-75 hover:no-underline')
    ->add(match ($variant) {
        'subtle' => 'text-zinc-500 dark:text-white/70',
        default => match ($accent) {
            true => 'text-[var(--color-accent-content)]',
            false => 'text-zinc-800 dark:text-white',
        },
    })
    ;
@endphp
{{-- NOTE: It's important that this file has NO newline at the end of the file. --}}
<?php if ($as !== 'button') : ?><a {{ $attributes->class($classes) }} data-flux-link <?php if ($external) : ?>target="_blank"<?php endif; ?>>{{ $slot }}</a><?php else : ?><button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }} data-flux-link>{{ $slot }}</button><?php endif; ?>