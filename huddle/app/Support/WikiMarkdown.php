<?php

namespace App\Support;

use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;

class WikiMarkdown
{
    public function __construct(
        protected WikiPathResolver $pathResolver,
    ) {}

    public function toHtml(string $markdown): string
    {
        $markdown = $this->replaceWikiLinks($markdown);
        $html = Str::markdown($markdown);
        $html = $this->replaceMermaidBlocks($html);
        $html = $this->replacePdfLinks($html);

        return Purify::config('wiki')->clean($html);
    }

    protected function replaceWikiLinks(string $markdown): string
    {
        return (string) preg_replace_callback(
            '/\[\[([^\]|]+)(?:\|([^\]]+))?\]\]/',
            function (array $matches): string {
                $path = trim($matches[1]);
                $label = trim($matches[2] ?? $matches[1]);
                $page = $this->pathResolver->findPageByPath($path);

                if ($page) {
                    $url = $page->url();
                } else {
                    $url = route('wiki.show', $path);
                }

                return '['.str_replace(['[', ']'], ['\\[', '\\]'], $label).']('.$url.')';
            },
            $markdown,
        );
    }

    protected function replaceMermaidBlocks(string $html): string
    {
        return (string) preg_replace_callback(
            '/<pre><code class="language-mermaid">(.*?)<\/code><\/pre>/s',
            fn (array $matches): string => '<div class="mermaid">'.html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5).'</div>',
            $html,
        );
    }

    protected function replacePdfLinks(string $html): string
    {
        return (string) preg_replace_callback(
            '/<p><a href="([^"]+\.pdf(?:\?[^"]*)?)">([^<]+)<\/a><\/p>/i',
            function (array $matches): string {
                $url = $matches[1];
                $label = $matches[2];

                if (! $this->isAllowedWikiAssetUrl($url)) {
                    return '<p><a href="'.e($url).'" target="_blank" rel="noopener noreferrer">'.e($label).'</a></p>';
                }

                return '<div class="wiki-pdf">'
                    .'<iframe src="'.e($url).'" title="'.e($label).'" loading="lazy"></iframe>'
                    .'<p><a href="'.e($url).'" target="_blank" rel="noopener noreferrer">'.e($label).'</a></p>'
                    .'</div>';
            },
            $html,
        );
    }

    protected function isAllowedWikiAssetUrl(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! str_starts_with($path, '/wiki-file/')) {
            return false;
        }

        $assetPath = ltrim(substr($path, strlen('/wiki-file/')), '/');

        return str_starts_with($assetPath, 'wiki/');
    }
}
