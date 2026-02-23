<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Services;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\Highlight\CommonMark\HighlightExtension;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Themes\CssTheme;

/**
 * Converts raw Markdown file content into rendered HTML.
 *
 * Pipeline:
 *   1. Parse and strip YAML frontmatter
 *   2. Render Markdown to HTML via league/commonmark (GFM + syntax highlighting)
 *   3. Syntax-highlight fenced code blocks and inline code via tempest/highlight
 */
final class MarkdownRenderer
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $this->converter = $this->buildConverter();
    }

    /**
     * Render a raw Markdown string (including any frontmatter) to HTML.
     *
     * @return array{html: string, matter: array<string, mixed>}
     */
    public function render(string $rawContent): array
    {
        $document = YamlFrontMatter::parse($rawContent);

        /** @var array<string, mixed> $matter */
        $matter = $document->matter();
        $body = $document->body();

        $html = $this->converter->convert($body)->getContent();

        return [
            'html' => $html,
            'matter' => $matter,
        ];
    }

    /**
     * Render a Markdown file from disk by its absolute path.
     *
     * @return array{html: string, matter: array<string, mixed>}
     */
    public function renderFile(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return ['html' => '', 'matter' => []];
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            return ['html' => '', 'matter' => []];
        }

        return $this->render($content);
    }

    private function buildConverter(): MarkdownConverter
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => PHP_INT_MAX,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HighlightExtension(new Highlighter(new CssTheme)));

        return new MarkdownConverter($environment);
    }
}
