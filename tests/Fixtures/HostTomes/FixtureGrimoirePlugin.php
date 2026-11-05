<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Tests\Fixtures\HostTomes;

use BlackpigCreatif\Grimoire\GrimoirePlugin;

class FixtureGrimoirePlugin extends GrimoirePlugin
{
    protected function getHostDirectories(): array
    {
        return [
            __DIR__ . '/Clusters' => 'BlackpigCreatif\\Grimoire\\Tests\\Fixtures\\HostTomes\\Clusters',
            __DIR__ . '/Pages' => 'BlackpigCreatif\\Grimoire\\Tests\\Fixtures\\HostTomes\\Pages',
        ];
    }
}
