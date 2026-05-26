@php
    $dirPath = $directory->fullPath();
    $isActive = $currentPath === $dirPath || str_starts_with($currentPath, $dirPath.'/');
@endphp

<li>
    <a
        href="{{ route('wiki.show', $dirPath) }}"
        wire:navigate
        @class([
            'block rounded-md px-2 py-1.5 transition',
            'font-medium text-huddle-primary' => $isActive,
            'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' => ! $isActive,
        ])
        style="padding-left: {{ 0.5 + ($depth * 0.75) }}rem"
    >
        <x-material-icon name="folder" class="me-1 inline text-[1rem] align-middle opacity-70" />
        {{ $directory->name }}
    </a>

    @if ($directory->pages->isNotEmpty() || $directory->children->isNotEmpty())
        <ul class="mt-1 space-y-1 border-s border-zinc-200 ps-2 dark:border-zinc-700" style="margin-left: {{ 0.5 + ($depth * 0.75) }}rem">
            @foreach ($directory->pages as $page)
                @php $pagePath = $page->fullPath(); @endphp
                <li>
                    <a
                        href="{{ route('wiki.show', $pagePath) }}"
                        wire:navigate
                        @class([
                            'block rounded-md px-2 py-1.5 transition',
                            'bg-huddle-primary/10 font-medium text-huddle-primary' => $currentPath === $pagePath,
                            'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' => $currentPath !== $pagePath,
                        ])
                    >
                        <x-material-icon name="article" class="me-1 inline text-[1rem] align-middle opacity-70" />
                        {{ $page->title }}
                    </a>
                </li>
            @endforeach

            @foreach ($directory->children as $child)
                @include('components.wiki-tree-directory', ['directory' => $child, 'currentPath' => $currentPath, 'depth' => $depth + 1])
            @endforeach
        </ul>
    @endif
</li>
