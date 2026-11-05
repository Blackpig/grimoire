<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Tests\Fixtures\HostTomes\Pages;

use BlackpigCreatif\Grimoire\Filament\Pages\GrimoireChapterPage;
use BlackpigCreatif\Grimoire\Tests\Fixtures\HostTomes\Clusters\BetaCluster;

class BetaIndexPage extends GrimoireChapterPage
{
    public static string $tomeId = 'beta';

    public static string $chapterSlug = 'index';

    protected static ?string $cluster = BetaCluster::class;
}
