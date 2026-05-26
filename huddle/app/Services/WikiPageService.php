<?php

namespace App\Services;

use App\Models\User;
use App\Models\WikiPage;
use App\Models\WikiPageVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WikiPageService
{
    /**
     * @param  array{title: string, body: string, slug?: string, wiki_directory_id?: ?int, change_summary?: ?string}  $data
     */
    public function create(array $data, User $user): WikiPage
    {
        return DB::transaction(function () use ($data, $user): WikiPage {
            $page = WikiPage::create([
                'wiki_directory_id' => $data['wiki_directory_id'] ?? null,
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['slug'] ?? $data['title'], $data['wiki_directory_id'] ?? null),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->createVersion($page, $data['title'], $data['body'], $user, $data['change_summary'] ?? __('Initial version'));

            return $page->load('latestVersion');
        });
    }

    /**
     * @param  array{title: string, body: string, slug?: string, wiki_directory_id?: ?int, change_summary?: ?string}  $data
     */
    public function update(WikiPage $page, array $data, User $user): WikiPage
    {
        return DB::transaction(function () use ($page, $data, $user): WikiPage {
            $slug = $data['slug'] ?? $page->slug;
            $directoryId = array_key_exists('wiki_directory_id', $data)
                ? $data['wiki_directory_id']
                : $page->wiki_directory_id;

            if ($slug !== $page->slug || $directoryId !== $page->wiki_directory_id) {
                $slug = $this->uniqueSlug($slug, $directoryId, $page->id);
            }

            $page->update([
                'title' => $data['title'],
                'slug' => $slug,
                'wiki_directory_id' => $directoryId,
                'updated_by' => $user->id,
            ]);

            $this->createVersion(
                $page,
                $data['title'],
                $data['body'],
                $user,
                $data['change_summary'] ?? null,
            );

            return $page->fresh(['latestVersion']);
        });
    }

    public function restoreVersion(WikiPage $page, WikiPageVersion $version, User $user, ?string $changeSummary = null): WikiPage
    {
        return $this->update($page, [
            'title' => $version->title,
            'body' => $version->body,
            'change_summary' => $changeSummary ?? __('Restored from version :n', ['n' => $version->version_number]),
        ], $user);
    }

    protected function createVersion(
        WikiPage $page,
        string $title,
        string $body,
        User $user,
        ?string $changeSummary,
    ): WikiPageVersion {
        $versionNumber = (int) $page->versions()->max('version_number') + 1;

        return $page->versions()->create([
            'version_number' => $versionNumber,
            'title' => $title,
            'body' => $body,
            'change_summary' => $changeSummary,
            'created_by' => $user->id,
        ]);
    }

    protected function uniqueSlug(string $source, ?int $directoryId, ?int $ignorePageId = null): string
    {
        $base = Str::slug($source);
        $slug = $base;
        $counter = 2;

        while ($this->slugExists($slug, $directoryId, $ignorePageId)) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?int $directoryId, ?int $ignorePageId): bool
    {
        return WikiPage::query()
            ->where('wiki_directory_id', $directoryId)
            ->where('slug', $slug)
            ->when($ignorePageId, fn ($q) => $q->where('id', '!=', $ignorePageId))
            ->exists();
    }
}
