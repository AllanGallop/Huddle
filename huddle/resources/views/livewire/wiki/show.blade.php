<div class="flex h-full w-full flex-1 flex-col gap-6 lg:flex-row lg:gap-8" x-data x-init="$nextTick(() => window.initWikiMermaid?.())">
    <aside class="w-full shrink-0 lg:w-64">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-3 flex items-center justify-between gap-2">
                <flux:heading size="sm">{{ __('Pages') }}</flux:heading>
                @if ($this->canManage)
                    <flux:button size="sm" variant="ghost" :href="route('wiki.edit')" wire:navigate title="{{ __('New page') }}">
                        <x-material-icon name="add" class="text-[1.125rem]" />
                    </flux:button>
                @endif
            </div>
            <x-wiki-tree
                :directories="$this->navigation->rootDirectories()"
                :pages="$this->navigation->rootPages()"
                :current-path="$path"
            />
        </div>
    </aside>

    <div class="min-w-0 flex-1">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-huddle-comp/40 bg-huddle-comp/10 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($this->resolved['type'] === 'home')
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
                <flux:heading size="xl" class="inline-flex items-center gap-2">
                    <x-material-icon name="menu_book" class="text-[1.75rem] text-huddle-primary" />
                    {{ __('Wiki') }}
                </flux:heading>
                <flux:text class="mt-2">{{ __('Community knowledge base. Browse directories or pages from the sidebar.') }}</flux:text>

                @if ($this->canManage)
                    <div class="mt-6 flex flex-wrap gap-2">
                        <flux:button variant="primary" :href="route('wiki.edit')" wire:navigate>
                            {{ __('New page') }}
                        </flux:button>
                        <flux:button variant="ghost" wire:click="openDirectoryModal">
                            {{ __('New directory') }}
                        </flux:button>
                    </div>
                @endif

                @php
                    $rootDirs = $this->navigation->rootDirectories();
                    $rootPages = $this->navigation->rootPages();
                @endphp

                @if ($rootDirs->isNotEmpty() || $rootPages->isNotEmpty())
                    <div class="mt-8 grid gap-6 sm:grid-cols-2">
                        @if ($rootDirs->isNotEmpty())
                            <div>
                                <flux:heading size="sm" class="mb-3">{{ __('Directories') }}</flux:heading>
                                <ul class="space-y-2">
                                    @foreach ($rootDirs as $dir)
                                        <li>
                                            <flux:link :href="route('wiki.show', $dir->fullPath())" wire:navigate class="inline-flex items-center gap-2">
                                                <x-material-icon name="folder" class="text-[1.125rem]" />
                                                {{ $dir->name }}
                                            </flux:link>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if ($rootPages->isNotEmpty())
                            <div>
                                <flux:heading size="sm" class="mb-3">{{ __('Pages') }}</flux:heading>
                                <ul class="space-y-2">
                                    @foreach ($rootPages as $p)
                                        <li>
                                            <flux:link :href="route('wiki.show', $p->fullPath())" wire:navigate>
                                                {{ $p->title }}
                                            </flux:link>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @if ($this->resolved['type'] === 'directory')
            @php $directory = $this->navigation->loadDirectoryTree($this->resolved['directory']); @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <flux:heading size="xl" class="inline-flex items-center gap-2">
                            <x-material-icon name="folder" class="text-[1.75rem] text-huddle-primary" />
                            {{ $directory->name }}
                        </flux:heading>
                        <flux:text class="mt-1 text-sm text-zinc-500">/{{ $directory->fullPath() }}</flux:text>
                    </div>
                    @if ($this->canManage)
                        <flux:button variant="ghost" size="sm" wire:click="openDirectoryModal({{ $directory->id }})">
                            {{ __('Add subdirectory') }}
                        </flux:button>
                    @endif
                </div>

                <div class="mt-8 space-y-6">
                    @if ($directory->pages->isNotEmpty())
                        <div>
                            <flux:heading size="sm" class="mb-3">{{ __('Pages') }}</flux:heading>
                            <ul class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                                @foreach ($directory->pages as $p)
                                    <li class="px-4 py-3">
                                        <flux:link :href="route('wiki.show', $p->fullPath())" wire:navigate class="font-medium">
                                            {{ $p->title }}
                                        </flux:link>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($directory->children->isNotEmpty())
                        <div>
                            <flux:heading size="sm" class="mb-3">{{ __('Subdirectories') }}</flux:heading>
                            <ul class="space-y-2">
                                @foreach ($directory->children as $child)
                                    <li>
                                        <flux:link :href="route('wiki.show', $child->fullPath())" wire:navigate class="inline-flex items-center gap-2">
                                            <x-material-icon name="folder" class="text-[1.125rem]" />
                                            {{ $child->name }}
                                        </flux:link>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($directory->pages->isEmpty() && $directory->children->isEmpty())
                        <flux:text>{{ __('This directory is empty.') }}</flux:text>
                    @endif
                </div>
            </div>
        @endif

        @if ($this->resolved['type'] === 'page')
            @php
                $wikiPage = $this->resolved['page'];
                $version = $this->activeVersion();
            @endphp
            <article class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 p-5 dark:border-zinc-700 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <flux:heading size="xl">{{ $version?->title }}</flux:heading>
                            <flux:text class="mt-1 text-sm text-zinc-500">/{{ $wikiPage->fullPath() }}</flux:text>
                        </div>
                        @if ($this->canManage)
                            <flux:button variant="ghost" size="sm" :href="route('wiki.edit', $wikiPage->fullPath())" wire:navigate>
                                <x-material-icon name="edit" class="me-1 text-[1rem]" />
                                {{ __('Edit') }}
                            </flux:button>
                        @endif
                    </div>

                    @if ($wikiPage->versions->count() > 1)
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <flux:text class="text-sm text-zinc-500">{{ __('Version') }}:</flux:text>
                            <flux:select wire:model.live="viewingVersionId" class="max-w-xs">
                                <flux:select.option value="">{{ __('Latest') }} (v{{ $wikiPage->latestVersion?->version_number }})</flux:select.option>
                                @foreach ($wikiPage->versions as $v)
                                    <flux:select.option :value="$v->id">v{{ $v->version_number }} — {{ $v->created_at->format('j M Y H:i') }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            @if ($viewingVersionId)
                                <flux:button size="sm" variant="ghost" wire:click="viewLatest">{{ __('View latest') }}</flux:button>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="wiki-content p-5 sm:p-8" wire:key="wiki-body-{{ $viewingVersionId ?? 'latest' }}">
                    {!! $this->renderedHtml !!}
                </div>

                @if ($version)
                    <div class="border-t border-zinc-200 px-5 py-3 text-sm text-zinc-500 dark:border-zinc-700 sm:px-6">
                        {{ __('Version :n', ['n' => $version->version_number]) }}
                        · {{ $version->author?->name }}
                        · {{ $version->created_at->format('j M Y H:i') }}
                        @if ($version->change_summary)
                            · {{ $version->change_summary }}
                        @endif
                    </div>
                @endif
            </article>
        @endif
    </div>

    <flux:modal wire:model="showDirectoryModal" class="md:max-w-md">
        <form wire:submit="saveDirectory" class="space-y-4">
            <flux:heading size="lg">{{ __('New directory') }}</flux:heading>
            <flux:input wire:model="directory_name" :label="__('Name')" required />
            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showDirectoryModal', false)">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

@script
<script>
    $wire.on('$refresh', () => window.initWikiMermaid?.());
    Livewire.hook('morph.updated', () => window.initWikiMermaid?.());
</script>
@endscript
