<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Filament\Pages;

use BlackpigCreatif\Grimoire\Filament\Clusters\GrimoireDocumentationCluster;

/**
 * Built-in Chapter Page: Grimoire self-docs — registering-a-tome.
 */
final class GrimoireDocumentationRegisteringATomePage extends GrimoireChapterPage
{
    public static string $tomeId = 'grimoire';

    public static string $chapterSlug = 'registering-a-tome';

    protected static ?string $cluster = GrimoireDocumentationCluster::class;
}
