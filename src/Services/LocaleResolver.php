<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Services;

/**
 * Resolves the correct Markdown file for a given slug, respecting the
 * configured locale strategy and falling back gracefully.
 */
final class LocaleResolver
{
    public function __construct(
        private readonly string $localeStrategy,
        private readonly string $fallbackLocale,
    ) {}

    /**
     * Resolve the best-matching .md file path for a given base directory and slug.
     *
     * Tries current locale first, then fallback locale, then the locale-neutral file.
     *
     * @param  string  $directory  The directory to look in.
     * @param  string  $slug  The chapter slug (e.g. 'hero-block').
     * @return string|null The resolved absolute file path, or null if not found.
     */
    public function resolve(string $directory, string $slug): ?string
    {
        $locale = app()->getLocale();

        return match ($this->localeStrategy) {
            'directory' => $this->resolveDirectory($directory, $slug, $locale),
            default => $this->resolveSuffix($directory, $slug, $locale),
        };
    }

    private function resolveSuffix(string $directory, string $slug, string $locale): ?string
    {
        $candidates = array_unique(array_filter([
            "{$directory}/{$slug}.{$locale}.md",
            $locale !== $this->fallbackLocale ? "{$directory}/{$slug}.{$this->fallbackLocale}.md" : null,
            "{$directory}/{$slug}.md",
        ]));

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function resolveDirectory(string $directory, string $slug, string $locale): ?string
    {
        $candidates = array_unique(array_filter([
            "{$directory}/{$locale}/{$slug}.md",
            $locale !== $this->fallbackLocale ? "{$directory}/{$this->fallbackLocale}/{$slug}.md" : null,
            "{$directory}/{$slug}.md",
        ]));

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
