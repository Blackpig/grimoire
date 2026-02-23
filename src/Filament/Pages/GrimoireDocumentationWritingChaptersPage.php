<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Filament\Pages;

use BlackpigCreatif\Grimoire\Filament\Clusters\GrimoireDocumentationCluster;

/**
 * Built-in Chapter Page: Grimoire self-docs — writing-chapters.
 */
final class GrimoireDocumentationWritingChaptersPage extends GrimoireChapterPage
{
    public static string $tomeId = 'grimoire';

    public static string $chapterSlug = 'writing-chapters';

    protected static ?string $cluster = GrimoireDocumentationCluster::class;
}
