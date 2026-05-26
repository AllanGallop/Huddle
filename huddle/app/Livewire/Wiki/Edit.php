<?php

namespace App\Livewire\Wiki;

use App\Models\WikiDirectory;
use App\Models\WikiPage;
use App\Models\WikiPageVersion;
use App\Services\WikiPageService;
use App\Support\WikiPathResolver;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public ?WikiPage $page = null;

    public string $path = '';

    public string $title = '';

    public string $slug = '';

    public string $body = '';

    public string $change_summary = '';

    public ?int $wiki_directory_id = null;

    public $uploadedImage;

    public ?int $restoreVersionId = null;

    public function mount(?string $path = null): void
    {
        abort_unless(Auth::user()->canManageWiki(), 403);

        $this->path = trim((string) $path, '/');

        if ($this->path !== '') {
            $resolved = app(WikiPathResolver::class)->resolve($this->path);

            if ($resolved['type'] !== WikiPathResolver::TYPE_PAGE) {
                abort(404);
            }

            $this->page = $resolved['page']->load('latestVersion', 'versions');
            $version = $this->page->latestVersion;
            $this->title = $version?->title ?? $this->page->title;
            $this->slug = $this->page->slug;
            $this->body = $version?->body ?? '';
            $this->wiki_directory_id = $this->page->wiki_directory_id;
        }
    }

    #[Computed]
    public function directories()
    {
        return WikiDirectory::query()->orderBy('name')->get();
    }

    #[Computed]
    public function versions()
    {
        return $this->page?->versions()->with('author')->orderByDesc('version_number')->get() ?? collect();
    }

    public function title(): string
    {
        return $this->page ? __('Edit wiki page') : __('New wiki page');
    }

    public function updatedUploadedImage(): void
    {
        $this->validate([
            'uploadedImage' => ['image', 'max:5120'],
        ]);

        $path = $this->uploadedImage->store('wiki/'.now()->format('Y/m'), 'public');
        $url = asset('storage/'.$path);

        $this->body .= "\n\n".'![]('.$url.')';
        $this->uploadedImage = null;

        session()->flash('status', __('Image inserted into content.'));
    }

    public function loadVersion(int $versionId): void
    {
        $version = WikiPageVersion::query()->where('wiki_page_id', $this->page?->id)->findOrFail($versionId);
        $this->title = $version->title;
        $this->body = $version->body;
        $this->restoreVersionId = $versionId;
    }

    public function save(WikiPageService $service): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'body' => ['required', 'string'],
            'change_summary' => ['nullable', 'string', 'max:500'],
            'wiki_directory_id' => ['nullable', 'exists:wiki_directories,id'],
        ]);

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'body' => $validated['body'],
            'wiki_directory_id' => $validated['wiki_directory_id'] ?: null,
            'change_summary' => $validated['change_summary'] ?: null,
        ];

        if ($this->page) {
            $page = $service->update($this->page, $data, Auth::user());
        } else {
            $page = $service->create($data, Auth::user());
        }

        session()->flash('status', __('Wiki page saved.'));
        $this->redirect(route('wiki.show', $page->fullPath()), navigate: true);
    }

    public function restoreVersion(WikiPageService $service): void
    {
        if (! $this->page || ! $this->restoreVersionId) {
            return;
        }

        $version = WikiPageVersion::query()
            ->where('wiki_page_id', $this->page->id)
            ->findOrFail($this->restoreVersionId);

        $service->restoreVersion($this->page, $version, Auth::user(), $this->change_summary ?: null);

        session()->flash('status', __('Version restored as a new revision.'));
        $this->redirect(route('wiki.show', $this->page->fullPath()), navigate: true);
    }

    public function render()
    {
        return view('livewire.wiki.edit');
    }
}
