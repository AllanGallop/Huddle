@props(['comment', 'depth' => 0, 'replyingTo' => null])

<div @class([
    'rounded-lg border border-zinc-200 p-4 dark:border-zinc-700',
    'ms-0' => $depth === 0,
    'ms-6 border-s-2 border-s-huddle-primary/30' => $depth > 0,
]) wire:key="event-comment-{{ $comment->id }}">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $comment->user->name }}</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $comment->comment }}</p>
            <p class="mt-2 text-xs text-zinc-400">{{ $comment->created_at->diffForHumans() }}</p>
        </div>
        @if ($depth === 0)
            <flux:button size="sm" variant="ghost" wire:click="startReply({{ $comment->id }})">
                {{ __('Reply') }}
            </flux:button>
        @endif
    </div>

    @if ($replyingTo === $comment->id)
        <form wire:submit="addComment" class="mt-4 space-y-3">
            <flux:textarea wire:model="comment" :placeholder="__('Write a reply...')" rows="2" required />
            <div class="flex gap-2">
                <flux:button type="submit" variant="primary" size="sm">{{ __('Post reply') }}</flux:button>
                <flux:button type="button" variant="ghost" size="sm" wire:click="cancelReply">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    @endif

    @if ($comment->replies->isNotEmpty())
        <div class="mt-4 space-y-3">
            @foreach ($comment->replies as $reply)
                <x-event-comment :comment="$reply" :depth="$depth + 1" :replying-to="$replyingTo" />
            @endforeach
        </div>
    @endif
</div>
