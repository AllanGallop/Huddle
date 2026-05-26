<?php

namespace App\Livewire\Wiki;

use App\Models\WikiDirectory;
use App\Models\WikiPage;
use App\Models\WikiPageVersion;
use App\Support\WikiMarkdown;
use App\Support\WikiNavigation;
use App\Support\WikiPathResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

class Show extends Component
{
    public string $path = '';

    public ?int $viewingVersionId = null;

    public bool $showDirectoryModal = false;

    public string $directory_name = '';

    public ?int $directory_parent_id = null;

    public function mount(?string $path = null): void
    {
        $this->path = trim((string) $path, '/');
    }

    #[Computed]
    public function resolved(): array
    {
        return app(WikiPathResolver::class)->resolve($this->path);
    }

    #[Computed]
    public function navigation()
    {
        return app(WikiNavigation::class);
    }

    #[Computed]
    public function canManage(): bool
    {
        return Auth::user()->canManageWiki();
    }

    #[Computed]
    public function renderedHtml(): string
    {
        $version = $this->activeVersion();

        if (! $version) {
            return '';
        }

        return app(WikiMarkdown::class)->toHtml($version->body);
    }

    public function activeVersion(): ?WikiPageVersion
    {
        if ($this->resolved['type'] !== WikiPathResolver::TYPE_PAGE) {
            return null;
        }

        /** @var WikiPage $page */
        $page = $this->resolved['page']->loadMissing('latestVersion', 'versions');

        if ($this->viewingVersionId) {
            return $page->versions->firstWhere('id', $this->viewingVersionId)
                ?? $page->latestVersion;
        }

        return $page->latestVersion;
    }

    public function title(): string
    {
        return match ($this->resolved['type']) {
            WikiPathResolver::TYPE_PAGE => $this->activeVersion()?->title ?? __('Wiki'),
            WikiPathResolver::TYPE_DIRECTORY => $this->resolved['directory']->name,
            default => __('Wiki'),
        };
    }

    public function viewVersion(int $versionId): void
    {
        $this->viewingVersionId = $versionId;
    }

    public function viewLatest(): void
    {
        $this->viewingVersionId = null;
    }

    public function openDirectoryModal(?int $parentId = null): void
    {
        $this->directory_name = '';
        $this->directory_parent_id = $parentId;
        $this->showDirectoryModal = true;
    }

    public function saveDirectory(): void
    {
        abort_unless($this->canManage, 403);

        $validated = $this->validate([
            'directory_name' => ['required', 'string', 'max:255'],
            'directory_parent_id' => ['nullable', 'exists:wiki_directories,id'],
        ]);

        $slug = Str::slug($validated['directory_name']);
        $parentId = $validated['directory_parent_id'];

        $base = $slug;
        $counter = 2;
        while (WikiDirectory::query()->where('parent_id', $parentId)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        WikiDirectory::create([
            'parent_id' => $parentId,
            'name' => $validated['directory_name'],
            'slug' => $slug,
        ]);

        $this->showDirectoryModal = false;
        unset($this->navigation);
        session()->flash('status', __('Directory created.'));
    }

    public function updatedViewingVersionId(mixed $value): void
    {
        $this->viewingVersionId = $value ? (int) $value : null;
    }

    public function render()
    {
        if ($this->resolved['type'] === WikiPathResolver::TYPE_NOT_FOUND) {
            abort(404);
        }

        if ($this->resolved['type'] === WikiPathResolver::TYPE_PAGE) {
            $this->resolved['page']->load(['versions.author', 'latestVersion.author']);
        }

        return view('livewire.wiki.show');
    }
}
