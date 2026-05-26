@props(['directories', 'pages', 'currentPath' => ''])

<ul class="space-y-1 text-sm">
    <li>
        <a
            href="{{ route('wiki.show') }}"
            wire:navigate
            @class([
                'block rounded-md px-2 py-1.5 transition',
                'bg-huddle-primary/10 font-medium text-huddle-primary' => $currentPath === '',
                'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' => $currentPath !== '',
            ])
        >
            {{ __('Home') }}
        </a>
    </li>

    @foreach ($pages as $page)
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

    @foreach ($directories as $directory)
        @include('components.wiki-tree-directory', ['directory' => $directory, 'currentPath' => $currentPath, 'depth' => 0])
    @endforeach
</ul>
