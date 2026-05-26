<?php

namespace App\Support;

use Illuminate\Support\Str;

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

        return $html;
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
}
