@props(['user'])

@if ($user && $user->exists && $user->id)
    <flux:link :href="route('users.show', $user)" wire:navigate {{ $attributes->class('font-medium text-huddle-primary') }}>
        {{ $user->name }}
    </flux:link>
@else
    <span {{ $attributes->class('font-medium text-zinc-500 dark:text-zinc-400') }}>
        {{ $user->name ?? __('Former member') }}
    </span>
@endif
