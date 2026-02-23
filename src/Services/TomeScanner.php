<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Services;

use BlackpigCreatif\Grimoire\Data\ChapterData;
use BlackpigCreatif\Grimoire\Data\TomeRegistration;
use Spatie\YamlFrontMatter\YamlFrontMatter;

/**
 * Scans registered Tome directories to discover Chapters.
 *
 * The scanner merges all registered paths for a Tome, resolving slug collisions
 * by giving priority to paths registered first (local extensions take priority
 * over vendor paths because extendTome() prepends rather than appends).
 */
final class TomeScanner
{
    public function __construct(
        private readonly LocaleResolver $localeResolver,
    ) {}

    /**
     * Scan a Tome and return all discovered Chapters, sorted by their order frontmatter.
     *
     * @return list<ChapterData>
     */
    public function scanTome(TomeRegistration $tome): array
    {
        /** @var array<string, ChapterData> $discovered Keyed by slug for deduplication */
        $discovered = [];

        foreach ($tome->getPaths() as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $files = $this->findMarkdownFiles($path);

            foreach ($files as $file) {
                $slug = $this->extractSlug($file, $path);

                // Skip if this slug was already resolved from a higher-priority path.
                if (isset($discovered[$slug])) {
                    continue;
                }

                $chapter = $this->parseChapter($file, $slug, $tome->isVendorPath($file));

                if ($chapter !== null) {
                    $discovered[$slug] = $chapter;
                }
            }
        }

        // Sort by order ascending, then alphabetically by title for stability.
        usort(
            $discovered,
            static fn (ChapterData $a, ChapterData $b) => $a->order !== $b->order
                ? $a->order <=> $b->order
                : $a->title <=> $b->title
        );

        return array_values($discovered);
    }

    /**
     * Resolve the .md file for a specific chapter slug within a Tome,
     * respecting locale strategy and path priority.
     *
     * @return string|null The absolute path to the resolved .md file.
     */
    public function resolveChapterFile(TomeRegistration $tome, string $slug): ?string
    {
        foreach ($tome->getPaths() as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $resolved = $this->localeResolver->resolve($path, $slug);

            if ($resolved !== null) {
                return $resolved;
            }
        }

        return null;
    }

    /**
     * Find all markdown files in a directory, excluding locale-variant files
     * when the suffix strategy is active (they appear as the base slug).
     *
     * @return list<string>
     */
    private function findMarkdownFiles(string $directory): array
    {
        $files = [];
        $iterator = new \DirectoryIterator($directory);

        foreach ($iterator as $file) {
            if ($file->isDot() || $file->isDir() || $file->getExtension() !== 'md') {
                continue;
            }

            $files[] = $file->getRealPath();
        }

        sort($files);

        return $files;
    }

    /**
     * Extract the canonical slug from an absolute file path.
     *
     * For suffix-strategy files (e.g. hero-block.en.md), the slug is 'hero-block'.
     * For plain files (e.g. hero-block.md), the slug is 'hero-block'.
     */
    private function extractSlug(string $filePath, string $baseDirectory): string
    {
        $filename = pathinfo($filePath, PATHINFO_FILENAME); // strips .md
        $relativePath = ltrim(str_replace($baseDirectory, '', $filePath), '/');

        // If the filename contains another dot, it may be a locale suffix (e.g. hero-block.en).
        // Strip the locale suffix to get the canonical slug.
        if (substr_count($filename, '.') >= 1) {
            $parts = explode('.', $filename);
            // Last part is likely a locale code (2-3 chars). Strip it.
            if (strlen(end($parts)) <= 3) {
                array_pop($parts);

                return implode('.', $parts);
            }
        }

        return $filename;
    }

    /**
     * Parse a Markdown file and extract its frontmatter into a ChapterData object.
     */
    private function parseChapter(string $filePath, string $slug, bool $isVendor): ?ChapterData
    {
        $content = @file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        $document = YamlFrontMatter::parse($content);

        $title = $document->matter('title') ?? $this->slugToTitle($slug);
        $order = (int) ($document->matter('order') ?? ($slug === 'index' ? 0 : 999));
        $icon = $document->matter('icon');
        $hidden = (bool) ($document->matter('hidden') ?? false);

        return new ChapterData(
            slug: $slug,
            title: $title,
            order: $order,
            filePath: $filePath,
            icon: $icon ?: null,
            hidden: $hidden,
            isVendor: $isVendor,
        );
    }

    /**
     * Convert a slug to a human-readable title as a fallback.
     */
    private function slugToTitle(string $slug): string
    {
        return str($slug)->replace('-', ' ')->title()->toString();
    }
}
