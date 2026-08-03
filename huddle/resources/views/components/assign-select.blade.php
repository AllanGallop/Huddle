@props([
    'options',
    'selectedIds' => [],
    'wireModel' => 'assignedFlagIds',
    'label' => __('Tags'),
    'placeholder' => __('Select tags…'),
    'searchPlaceholder' => __('Search tags…'),
    'emptyMessage' => __('No matching tags.'),
    'errorName' => null,
])

@php
    $errorKey = $errorName ?? $wireModel;
    $invalid = $errors->has($errorKey) || $errors->has($errorKey.'.*');
    $selectedOptions = $options->whereIn('id', array_map('intval', $selectedIds));
@endphp

<flux:field>
    <flux:label>{{ $label }}</flux:label>

    <div
        x-data="{ open: false, search: '' }"
        x-on:keydown.escape.window="open = false"
        x-on:click.outside="open = false"
        class="relative"
    >
        <button
            type="button"
            x-on:click="open = ! open"
            @class([
                'flex min-h-10 w-full items-center gap-2 rounded-lg border px-3 py-2 text-start text-sm shadow-xs transition',
                'border-red-500' => $invalid,
                'border-zinc-200 border-b-zinc-300/80 bg-white text-zinc-700 dark:border-white/10 dark:bg-white/10 dark:text-zinc-300' => ! $invalid,
            ])
            aria-haspopup="listbox"
            x-bind:aria-expanded="open"
        >
            @if ($selectedOptions->isNotEmpty())
                <span class="flex flex-1 flex-wrap gap-1">
                    @foreach ($selectedOptions as $option)
                        <x-user-flag-badge :name="$option->name" wire:key="selected-option-{{ $option->id }}" />
                    @endforeach
                </span>
            @else
                <span class="flex-1 text-zinc-400 dark:text-zinc-500">{{ $placeholder }}</span>
            @endif
            <flux:icon.chevron-down
                class="ms-auto size-5 shrink-0 text-zinc-400 transition"
                x-bind:class="open && 'rotate-180'"
            />
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition.opacity.duration.150ms
            class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-600 dark:bg-zinc-800"
            role="listbox"
        >
            <div class="border-b border-zinc-200 p-2 dark:border-zinc-600">
                <input
                    type="search"
                    x-model="search"
                    placeholder="{{ $searchPlaceholder }}"
                    class="w-full rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs outline-none focus:border-zinc-400 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-200 dark:focus:border-zinc-500"
                    x-on:keydown.stop
                    x-on:click.stop
                />
            </div>

            <div x-ref="options" class="max-h-48 overflow-y-auto p-2">
                @forelse ($options as $option)
                    @php
                        $searchText = strtolower(trim($option->name.' '.($option->description ?? '')));
                    @endphp
                    <div
                        wire:key="assign-option-{{ $option->id }}"
                        data-search="{{ $searchText }}"
                        x-show="! search || $el.dataset.search.includes(search.toLowerCase())"
                        class="rounded-md px-2 py-1 hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                    >
                        <flux:checkbox
                            wire:model="{{ $wireModel }}"
                            :value="$option->id"
                            :label="$option->name"
                        />
                    </div>
                @empty
                    <p class="px-2 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('No options available.') }}
                    </p>
                @endforelse

                <p
                    x-show="search.length > 0 && Array.from($refs.options.children).every(el => el.offsetParent === null)"
                    class="px-2 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400"
                >
                    {{ $emptyMessage }}
                </p>
            </div>
        </div>
    </div>

    <flux:error name="{{ $errorKey }}" />
</flux:field>
