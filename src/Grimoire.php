<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire;

use BlackpigCreatif\Grimoire\Services\TomeRegistry;

/**
 * Main Grimoire API class, accessible via the Grimoire facade.
 *
 * Provides a clean, expressive interface for registering and extending Tomes
 * from service providers across all installed packages.
 *
 * @method static void registerTome(string $id, string $label, string $icon, string $path, string $clusterClass, ?string $slug = null)
 * @method static void extendTome(string $id, string $path)
 */
class Grimoire
{
    public function __construct(
        private readonly TomeRegistry $registry,
    ) {}

    /**
     * Register a new Tome with the Grimoire system.
     *
     * @param  class-string  $clusterClass  The Filament Cluster stub class representing this Tome.
     * @param  string|null  $slug  URL-safe slug (derived from label if omitted).
     */
    public function registerTome(
        string $id,
        string $label,
        string $icon,
        string $path,
        string $clusterClass,
        ?string $slug = null,
    ): void {
        $this->registry->registerTome(
            id: $id,
            label: $label,
            icon: $icon,
            path: $path,
            clusterClass: $clusterClass,
            slug: $slug,
        );
    }

    /**
     * Extend an existing Tome with additional local content.
     *
     * Local chapters take priority over the Tome's original (vendor) chapters
     * on slug collision, enabling overrides of vendor documentation.
     */
    public function extendTome(string $id, string $path): void
    {
        $this->registry->extendTome($id, $path);
    }
}
