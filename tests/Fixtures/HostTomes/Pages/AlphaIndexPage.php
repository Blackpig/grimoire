<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Tests\Fixtures\HostTomes\Pages;

use BlackpigCreatif\Grimoire\Filament\Pages\GrimoireChapterPage;
use BlackpigCreatif\Grimoire\Tests\Fixtures\HostTomes\Clusters\AlphaCluster;

class AlphaIndexPage extends GrimoireChapterPage
{
    public static string $tomeId = 'alpha';

    public static string $chapterSlug = 'index';

    protected static ?string $cluster = AlphaCluster::class;
}
