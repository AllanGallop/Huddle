<?php

namespace App\Support;

use App\Models\WikiDirectory;
use App\Models\WikiPage;
use Illuminate\Support\Collection;

class WikiNavigation
{
    /**
     * @return Collection<int, WikiDirectory>
     */
    public function rootDirectories(): Collection
    {
        $all = WikiDirectory::query()
            ->with('pages')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->buildDirectoryTree($all, null);
    }

    /**
     * @return Collection<int, WikiPage>
     */
    public function rootPages(): Collection
    {
        return WikiPage::query()
            ->whereNull('wiki_directory_id')
            ->orderBy('title')
            ->get();
    }

    public function loadDirectoryTree(WikiDirectory $directory): WikiDirectory
    {
        $all = WikiDirectory::query()->with('pages')->get()->keyBy('id');
        $directory->setRelation('children', $this->buildDirectoryTree($all, $directory->id));
        $directory->load('pages');

        return $directory;
    }

    /**
     * @param  Collection<int, WikiDirectory>  $directories
     * @return Collection<int, WikiDirectory>
     */
    protected function buildDirectoryTree(Collection $directories, ?int $parentId): Collection
    {
        return $directories
            ->where('parent_id', $parentId)
            ->values()
            ->each(function (WikiDirectory $directory) use ($directories): void {
                $directory->setRelation(
                    'children',
                    $this->buildDirectoryTree($directories, $directory->id),
                );
            });
    }
}
