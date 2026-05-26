<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:link :href="$page ? route('wiki.show', $page->fullPath()) : route('wiki.show')" wire:navigate class="inline-flex items-center gap-1 text-sm">
            <x-material-icon name="arrow_back" class="text-[1rem]" />
            {{ __('Back to wiki') }}
        </flux:link>
        <flux:heading size="xl" class="mt-2">{{ $this->title() }}</flux:heading>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-huddle-comp/40 bg-huddle-comp/10 px-4 py-3 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
                <flux:input wire:model="title" :label="__('Title')" required />
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="slug" :label="__('URL slug')" required placeholder="my-page" />
                    <flux:select wire:model="wiki_directory_id" :label="__('Directory')">
                        <flux:select.option value="">{{ __('Root') }}</flux:select.option>
                        @foreach ($this->directories as $dir)
                            <flux:select.option :value="$dir->id">{{ $dir->fullPath() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <flux:textarea wire:model="body" :label="__('Content (Markdown)')" rows="18" class="mt-4 font-mono text-sm" required />
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <flux:input type="file" wire:model="uploadedFile" accept="image/*,.pdf,application/pdf" :label="__('Insert image or PDF')" />
                    <flux:text class="text-sm text-zinc-500">{{ __('Uploading adds an image embed or inline PDF viewer at the end of the content.') }}</flux:text>
                </div>
                <flux:input wire:model="change_summary" :label="__('Change summary (optional)')" class="mt-4" placeholder="{{ __('What changed in this revision?') }}" />
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                @if ($page && $restoreVersionId)
                    <flux:button type="button" variant="ghost" wire:click="restoreVersion" wire:confirm="{{ __('Restore this version as a new revision?') }}">
                        {{ __('Restore loaded version') }}
                    </flux:button>
                @endif
                <flux:button type="submit" variant="primary">
                    {{ __('Save page') }}
                </flux:button>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                <flux:heading size="sm" class="mb-2">{{ __('Markdown help') }}</flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    <strong>{{ __('Wiki links') }}:</strong> <code>[[page-slug]]</code> or <code>[[guides/setup|Label]]</code><br>
                    <strong>{{ __('Mermaid') }}:</strong> fenced code block with language <code>mermaid</code><br>
                    <strong>{{ __('Images / files') }}:</strong> <code>![alt](url)</code> or <code>[file.pdf](url)</code> (PDF links render inline)
                </flux:text>
            </div>

            @if ($page && $this->versions->isNotEmpty())
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-3">{{ __('Version history') }}</flux:heading>
                    <ul class="max-h-80 space-y-2 overflow-y-auto text-sm">
                        @foreach ($this->versions as $version)
                            <li wire:key="ver-{{ $version->id }}" class="flex items-center justify-between gap-2 rounded-md border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                                <div>
                                    <span class="font-medium">v{{ $version->version_number }}</span>
                                    <span class="text-zinc-500">· {{ $version->created_at->format('j M Y') }}</span>
                                </div>
                                <flux:button type="button" size="sm" variant="ghost" wire:click="loadVersion({{ $version->id }})">
                                    {{ __('Load') }}
                                </flux:button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </form>
</div>
