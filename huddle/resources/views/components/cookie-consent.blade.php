<div
    x-data="{
        visible: ! localStorage.getItem('huddle_cookie_consent'),
        accept() {
            localStorage.setItem('huddle_cookie_consent', '1');
            this.visible = false;
        },
    }"
    x-show="visible"
    x-cloak
    class="fixed inset-x-0 bottom-0 z-50 border-t border-zinc-200 bg-white p-4 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
>
    <div class="mx-auto flex max-w-5xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            {{ __('We use essential cookies for sign-in and session management.') }}
            <a href="{{ route('privacy.show') }}" class="font-medium text-huddle-primary underline-offset-2 hover:underline" wire:navigate>
                {{ __('Privacy policy') }}
            </a>
        </p>
        <button
            type="button"
            @click="accept()"
            class="inline-flex shrink-0 items-center justify-center rounded-lg bg-huddle-primary px-4 py-2 text-sm font-medium text-white hover:bg-huddle-primary/90"
        >
            {{ __('Accept') }}
        </button>
    </div>
</div>
