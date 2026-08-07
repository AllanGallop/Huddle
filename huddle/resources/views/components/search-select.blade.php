@props([
    'options',
    'selectedId' => null,
    'wireModel' => null,
    'name' => null,
    'label' => null,
    'placeholder' => __('Search and select…'),
    'searchPlaceholder' => __('Search…'),
    'emptyMessage' => __('No matching results.'),
    'allowClear' => false,
    'clearLabel' => __('None'),
    'emptyValue' => null,
    'optionLabel' => 'name',
    'optionSublabel' => null,
    'errorName' => null,
    'required' => false,
])

@php
    $errorKey = $errorName ?? $wireModel ?? $name;
    $invalid = $errorKey && ($errors->has($errorKey) || $errors->has($errorKey.'.*'));
    $selected = collect($options)->first(function ($option) use ($selectedId) {
        if ($selectedId === null || $selectedId === '') {
            return false;
        }

        return (string) data_get($option, 'id') === (string) $selectedId;
    });
    $selectedLabel = $selected
        ? (string) data_get($selected, $optionLabel)
        : null;
    $selectedSublabel = $selected && $optionSublabel
        ? (string) data_get($selected, $optionSublabel)
        : null;
    $initialValue = $selectedId === null || $selectedId === '' ? '' : (string) $selectedId;
@endphp

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div
        wire:ignore.self
        x-data="{
            open: false,
            search: '',
            value: @js($initialValue),
            label: @js($selectedLabel),
            sublabel: @js($selectedSublabel),
            emptyValue: @js($emptyValue),
            select(id, label, sublabel = null) {
                const isEmpty = id === null || id === '';
                this.value = isEmpty ? '' : String(id);
                this.label = isEmpty ? null : label;
                this.sublabel = isEmpty ? null : sublabel;
                this.open = false;
                this.search = '';
                @if ($wireModel)
                    $wire.set(@js($wireModel), isEmpty ? this.emptyValue : (Number.isNaN(Number(id)) ? id : Number(id)));
                @endif
            },
            clear() {
                this.select('', null, null);
            }
        }"
        x-on:keydown.escape.window="open = false"
        x-on:click.outside="open = false"
        class="relative"
    >
        @if ($name)
            <input type="hidden" name="{{ $name }}" x-bind:value="value" @if ($required) required @endif />
        @endif

        <button
            type="button"
            x-on:click="open = ! open; if (open) $nextTick(() => $refs.search?.focus())"
            @class([
                'flex min-h-10 w-full items-center gap-2 rounded-lg border px-3 py-2 text-start text-sm shadow-xs transition',
                'border-red-500' => $invalid,
                'border-zinc-200 border-b-zinc-300/80 bg-white text-zinc-700 dark:border-white/10 dark:bg-white/10 dark:text-zinc-300' => ! $invalid,
            ])
            aria-haspopup="listbox"
            x-bind:aria-expanded="open"
        >
            <span class="min-w-0 flex-1">
                <template x-if="label">
                    <span class="block truncate font-medium text-zinc-900 dark:text-white" x-text="label"></span>
                </template>
                <template x-if="label && sublabel">
                    <span class="block truncate text-xs text-zinc-500" x-text="sublabel"></span>
                </template>
                <template x-if="! label">
                    <span class="text-zinc-400 dark:text-zinc-500">{{ $placeholder }}</span>
                </template>
            </span>
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
                    x-ref="search"
                    x-model="search"
                    placeholder="{{ $searchPlaceholder }}"
                    class="w-full rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs outline-none focus:border-zinc-400 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-200 dark:focus:border-zinc-500"
                    x-on:keydown.stop
                    x-on:click.stop
                />
            </div>

            <div x-ref="options" class="max-h-56 overflow-y-auto p-1">
                @if ($allowClear)
                    <button
                        type="button"
                        x-on:click="clear()"
                        class="flex w-full items-center rounded-md px-3 py-2 text-start text-sm text-zinc-500 hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                    >
                        {{ $clearLabel }}
                    </button>
                @endif

                @forelse ($options as $option)
                    @php
                        $id = data_get($option, 'id');
                        $primary = (string) data_get($option, $optionLabel);
                        $secondary = $optionSublabel ? (string) data_get($option, $optionSublabel) : null;
                        $searchText = strtolower(trim($primary.' '.($secondary ?? '').' '.(data_get($option, 'description') ?? '')));
                    @endphp
                    <button
                        type="button"
                        wire:key="search-select-option-{{ $id }}"
                        data-search="{{ $searchText }}"
                        x-show="! search || $el.dataset.search.includes(search.toLowerCase())"
                        x-on:click="select(@js((string) $id), @js($primary), @js($secondary))"
                        class="flex w-full flex-col rounded-md px-3 py-2 text-start hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                        x-bind:class="value === @js((string) $id) && 'bg-huddle-primary/10'"
                        role="option"
                    >
                        <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $primary }}</span>
                        @if ($secondary)
                            <span class="text-xs text-zinc-500">{{ $secondary }}</span>
                        @endif
                    </button>
                @empty
                    <p class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('No options available.') }}
                    </p>
                @endforelse

                <p
                    x-show="search.length > 0 && Array.from($refs.options.querySelectorAll('[data-search]')).every(el => el.offsetParent === null)"
                    class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400"
                >
                    {{ $emptyMessage }}
                </p>
            </div>
        </div>
    </div>

    @if ($errorKey)
        <flux:error name="{{ $errorKey }}" />
    @endif
</flux:field>
