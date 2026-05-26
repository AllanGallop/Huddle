<?php

namespace App\Support;

use App\Models\WikiDirectory;
use App\Models\WikiPage;

class WikiPathResolver
{
    public const TYPE_HOME = 'home';

    public const TYPE_DIRECTORY = 'directory';

    public const TYPE_PAGE = 'page';

    public const TYPE_NOT_FOUND = 'not_found';

    /**
     * @return array{type: string, directory?: WikiDirectory, page?: WikiPage}
     */
    public function resolve(?string $path): array
    {
        $path = trim((string) $path, '/');

        if ($path === '') {
            return ['type' => self::TYPE_HOME];
        }

        $segments = explode('/', $path);

        if (count($segments) === 1) {
            $page = WikiPage::query()
                ->whereNull('wiki_directory_id')
                ->where('slug', $segments[0])
                ->first();

            if ($page) {
                return ['type' => self::TYPE_PAGE, 'page' => $page];
            }

            $directory = WikiDirectory::query()
                ->whereNull('parent_id')
                ->where('slug', $segments[0])
                ->first();

            if ($directory) {
                return ['type' => self::TYPE_DIRECTORY, 'directory' => $directory];
            }

            return ['type' => self::TYPE_NOT_FOUND];
        }

        $directory = null;

        foreach (array_slice($segments, 0, -1) as $segment) {
            $directory = WikiDirectory::query()
                ->where('parent_id', $directory?->id)
                ->where('slug', $segment)
                ->first();

            if (! $directory) {
                return ['type' => self::TYPE_NOT_FOUND];
            }
        }

        $pageSlug = $segments[array_key_last($segments)];

        $page = WikiPage::query()
            ->where('wiki_directory_id', $directory?->id)
            ->where('slug', $pageSlug)
            ->first();

        if ($page) {
            return ['type' => self::TYPE_PAGE, 'page' => $page, 'directory' => $directory];
        }

        return ['type' => self::TYPE_NOT_FOUND];
    }

    public function findPageByPath(string $path): ?WikiPage
    {
        $resolved = $this->resolve($path);

        return $resolved['type'] === self::TYPE_PAGE ? $resolved['page'] : null;
    }
}
