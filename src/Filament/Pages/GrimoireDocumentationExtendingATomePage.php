<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Filament\Pages;

use BlackpigCreatif\Grimoire\Filament\Clusters\GrimoireDocumentationCluster;

/**
 * Built-in Chapter Page: Grimoire self-docs — extending-a-tome.
 */
final class GrimoireDocumentationExtendingATomePage extends GrimoireChapterPage
{
    public static string $tomeId = 'grimoire';

    public static string $chapterSlug = 'extending-a-tome';

    protected static ?string $cluster = GrimoireDocumentationCluster::class;
}
