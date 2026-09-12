<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="bg-transparent">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
