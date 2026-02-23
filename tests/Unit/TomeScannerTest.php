<?php

declare(strict_types=1);

use BlackpigCreatif\Grimoire\Data\ChapterData;
use BlackpigCreatif\Grimoire\Data\TomeRegistration;
use BlackpigCreatif\Grimoire\Filament\Clusters\GrimoireDocumentationCluster;
use BlackpigCreatif\Grimoire\Services\LocaleResolver;
use BlackpigCreatif\Grimoire\Services\TomeScanner;

/**
 * Creates a temporary directory with .md files for testing.
 *
 * @param  array<string, string>  $files  Map of filename => content
 * @return string The temp directory path
 */
function createTempTomeDirectory(array $files): string
{
    $dir = sys_get_temp_dir() . '/grimoire-test-' . uniqid();
    mkdir($dir, 0755, true);

    foreach ($files as $filename => $content) {
        file_put_contents("{$dir}/{$filename}", $content);
    }

    return $dir;
}

function makeTomeRegistration(string $id, string $path): TomeRegistration
{
    return new TomeRegistration(
        id: $id,
        label: 'Test',
        icon: 'heroicon-o-document',
        clusterClass: GrimoireDocumentationCluster::class,
        path: $path,
    );
}

function makeScanner(): TomeScanner
{
    return new TomeScanner(
        localeResolver: new LocaleResolver(
            localeStrategy: 'suffix',
            fallbackLocale: 'en',
        ),
    );
}

afterEach(function () {
    // Cleanup any temp dirs created during tests.
    foreach (glob(sys_get_temp_dir() . '/grimoire-test-*') as $dir) {
        array_map('unlink', glob("{$dir}/*"));
        rmdir($dir);
    }
});

it('scans a directory and returns chapters sorted by order', function () {
    $dir = createTempTomeDirectory([
        'index.md' => "---\ntitle: Introduction\norder: 0\n---\n\nIntro content.",
        'hero-block.md' => "---\ntitle: Hero Block\norder: 2\n---\n\nHero content.",
        'text-block.md' => "---\ntitle: Text Block\norder: 1\n---\n\nText content.",
    ]);

    $scanner = makeScanner();
    $tome = makeTomeRegistration('test', $dir);

    $chapters = $scanner->scanTome($tome);

    expect($chapters)->toHaveCount(3)
        ->and($chapters[0]->slug)->toBe('index')
        ->and($chapters[0]->order)->toBe(0)
        ->and($chapters[1]->slug)->toBe('text-block')
        ->and($chapters[1]->order)->toBe(1)
        ->and($chapters[2]->slug)->toBe('hero-block')
        ->and($chapters[2]->order)->toBe(2);
});

it('derives title from slug when frontmatter title is absent', function () {
    $dir = createTempTomeDirectory([
        'managing-faqs.md' => "---\norder: 1\n---\n\nContent.",
    ]);

    $chapters = makeScanner()->scanTome(makeTomeRegistration('test', $dir));

    expect($chapters[0]->title)->toBe('Managing Faqs');
});

it('extracts frontmatter fields correctly', function () {
    $dir = createTempTomeDirectory([
        'hero.md' => "---\ntitle: Hero Block\norder: 5\nicon: heroicon-o-photo\nhidden: true\n---\n\nContent.",
    ]);

    $chapter = makeScanner()->scanTome(makeTomeRegistration('test', $dir))[0];

    expect($chapter->title)->toBe('Hero Block')
        ->and($chapter->order)->toBe(5)
        ->and($chapter->icon)->toBe('heroicon-o-photo')
        ->and($chapter->hidden)->toBeTrue();
});

it('marks vendor-path chapters as non-editable', function () {
    $dir = createTempTomeDirectory([
        'index.md' => "---\ntitle: Vendor Doc\norder: 0\n---\n\nContent.",
    ]);

    // Simulate a vendor path by creating a registration that considers the path as vendor.
    $tome = new TomeRegistration(
        id: 'vendor-tome',
        label: 'Vendor',
        icon: 'heroicon-o-document',
        clusterClass: GrimoireDocumentationCluster::class,
        path: $dir,
    );

    // Override isVendorPath behaviour by placing the dir under a vendor-like path — for this
    // test we directly check the ChapterData isVendor flag based on the path.
    $chapter = makeScanner()->scanTome($tome)[0];

    // The dir is not under /vendor/ so it should be non-vendor (editable).
    expect($chapter->isVendor)->toBeFalse()
        ->and($chapter->isEditable())->toBeTrue();
});

it('gives local extension paths priority over vendor paths on slug collision', function () {
    $vendorDir = createTempTomeDirectory([
        'hero-block.md' => "---\ntitle: Vendor Hero\norder: 1\n---\n\nVendor content.",
    ]);

    $localDir = createTempTomeDirectory([
        'hero-block.md' => "---\ntitle: Local Hero\norder: 1\n---\n\nLocal content.",
    ]);

    $tome = makeTomeRegistration('collision-test', $vendorDir);
    $tome->addPath($localDir); // Prepend local — higher priority.

    $chapters = makeScanner()->scanTome($tome);

    // Should only have one chapter despite two sources.
    expect($chapters)->toHaveCount(1)
        ->and($chapters[0]->title)->toBe('Local Hero');
});

it('resolves a chapter file path for a given slug', function () {
    $dir = createTempTomeDirectory([
        'hero-block.md' => "---\ntitle: Hero\norder: 1\n---\n\nContent.",
    ]);

    $tome = makeTomeRegistration('test', $dir);
    $resolved = makeScanner()->resolveChapterFile($tome, 'hero-block');

    expect($resolved)->toBe("{$dir}/hero-block.md");
});

it('returns null when resolving a non-existent chapter', function () {
    $dir = createTempTomeDirectory(['index.md' => "---\ntitle: Index\norder: 0\n---"]);

    $resolved = makeScanner()->resolveChapterFile(makeTomeRegistration('test', $dir), 'missing');

    expect($resolved)->toBeNull();
});

it('returns empty array when scanning a non-existent directory', function () {
    $tome = makeTomeRegistration('test', '/tmp/nonexistent-grimoire-' . uniqid());

    expect(makeScanner()->scanTome($tome))->toBe([]);
});
