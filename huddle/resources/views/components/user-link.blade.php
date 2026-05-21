@props(['user'])

<flux:link :href="route('users.show', $user)" wire:navigate {{ $attributes->class('font-medium text-huddle-primary hover:underline') }}>
    {{ $user->name }}
</flux:link>
