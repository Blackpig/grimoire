<?php

declare(strict_types=1);

use BlackpigCreatif\Grimoire\Services\LocaleResolver;

function makeSuffixResolver(string $fallback = 'en'): LocaleResolver
{
    return new LocaleResolver(localeStrategy: 'suffix', fallbackLocale: $fallback);
}

function makeDirectoryResolver(string $fallback = 'en'): LocaleResolver
{
    return new LocaleResolver(localeStrategy: 'directory', fallbackLocale: $fallback);
}

function createLocaleTestDir(array $files): string
{
    $dir = sys_get_temp_dir() . '/grimoire-locale-' . uniqid();
    mkdir($dir, 0755, true);

    foreach ($files as $filename => $content) {
        // Support subdirectories.
        $path = "{$dir}/{$filename}";
        $subDir = dirname($path);

        if (! is_dir($subDir)) {
            mkdir($subDir, 0755, true);
        }

        file_put_contents($path, $content);
    }

    return $dir;
}

afterEach(function () {
    $cleanDir = static function (string $dir) use (&$cleanDir): void {
        foreach (glob("{$dir}/*") ?: [] as $item) {
            is_dir($item) ? $cleanDir($item) : unlink($item);
        }
        rmdir($dir);
    };

    foreach (glob(sys_get_temp_dir() . '/grimoire-locale-*') ?: [] as $dir) {
        $cleanDir($dir);
    }
});

// --- Suffix strategy ---

it('resolves locale-suffix file for current locale', function () {
    $dir = createLocaleTestDir([
        'hero-block.en.md' => 'English',
        'hero-block.fr.md' => 'French',
    ]);

    app()->setLocale('fr');

    $resolved = makeSuffixResolver()->resolve($dir, 'hero-block');

    expect($resolved)->toBe("{$dir}/hero-block.fr.md");
});

it('falls back to fallback locale when current locale file is missing', function () {
    $dir = createLocaleTestDir([
        'hero-block.en.md' => 'English only',
    ]);

    app()->setLocale('de');

    $resolved = makeSuffixResolver('en')->resolve($dir, 'hero-block');

    expect($resolved)->toBe("{$dir}/hero-block.en.md");
});

it('falls back to plain .md file when no locale file exists', function () {
    $dir = createLocaleTestDir([
        'hero-block.md' => 'No locale',
    ]);

    app()->setLocale('fr');

    $resolved = makeSuffixResolver()->resolve($dir, 'hero-block');

    expect($resolved)->toBe("{$dir}/hero-block.md");
});

it('returns null when no matching file exists', function () {
    $dir = createLocaleTestDir([]);

    $resolved = makeSuffixResolver()->resolve($dir, 'hero-block');

    expect($resolved)->toBeNull();
});

// --- Directory strategy ---

it('resolves directory-strategy file for current locale', function () {
    $dir = createLocaleTestDir([
        'en/hero-block.md' => 'English',
        'fr/hero-block.md' => 'French',
    ]);

    app()->setLocale('fr');

    $resolved = makeDirectoryResolver()->resolve($dir, 'hero-block');

    expect($resolved)->toBe("{$dir}/fr/hero-block.md");
});

it('falls back to fallback locale directory when locale dir is missing', function () {
    $dir = createLocaleTestDir([
        'en/hero-block.md' => 'English fallback',
    ]);

    app()->setLocale('de');

    $resolved = makeDirectoryResolver('en')->resolve($dir, 'hero-block');

    expect($resolved)->toBe("{$dir}/en/hero-block.md");
});
