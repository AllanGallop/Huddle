@props(['user', 'size' => 'sm'])

@php
    $status = $user->membershipStatus();
@endphp

<div @class([
    'inline-flex shrink-0 rounded-lg',
    'ring-2 ring-offset-2 ring-offset-white dark:ring-offset-zinc-900' => $status !== 'none',
    'ring-huddle-comp' => $status === 'active',
    'ring-zinc-400 dark:ring-zinc-500' => $status === 'expired',
])>
    <flux:avatar :name="$user->name" :initials="$user->initials()" :size="$size" />
</div>
