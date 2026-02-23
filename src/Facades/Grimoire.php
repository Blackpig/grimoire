<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BlackpigCreatif\Grimoire\Grimoire
 *
 * @method static void registerTome(string $id, string $label, string $icon, string $path, string $clusterClass, ?string $slug = null)
 * @method static void extendTome(string $id, string $path)
 */
class Grimoire extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BlackpigCreatif\Grimoire\Grimoire::class;
    }
}
