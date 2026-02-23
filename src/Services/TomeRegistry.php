<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Services;

use BlackpigCreatif\Grimoire\Data\TomeRegistration;
use InvalidArgumentException;

/**
 * Central registry for all Tomes in the Grimoire system.
 *
 * Service providers across all installed packages register their Tomes here
 * during boot. The plugin then reads this registry when building navigation.
 *
 * This class is bound as a singleton in GrimoireServiceProvider.
 */
final class TomeRegistry
{
    /** @var array<string, TomeRegistration> */
    private array $tomes = [];

    /**
     * Register a new Tome.
     *
     * @param  class-string  $clusterClass  The Filament Cluster class that represents this Tome.
     * @param  string|null  $slug  Optional URL slug (derived from label if omitted).
     */
    public function registerTome(
        string $id,
        string $label,
        string $icon,
        string $path,
        string $clusterClass,
        ?string $slug = null,
    ): void {
        if (isset($this->tomes[$id])) {
            throw new InvalidArgumentException("A Tome with id '{$id}' is already registered. Use extendTome() to add additional paths.");
        }

        $this->tomes[$id] = new TomeRegistration(
            id: $id,
            label: $label,
            icon: $icon,
            clusterClass: $clusterClass,
            path: $path,
            slug: $slug,
        );
    }

    /**
     * Extend an existing Tome with an additional path.
     *
     * The extended path's chapters take priority over the original path
     * when slug collisions occur — allowing local overrides of vendor docs.
     */
    public function extendTome(string $id, string $path): void
    {
        if (! isset($this->tomes[$id])) {
            throw new InvalidArgumentException("Cannot extend Tome '{$id}': no such Tome is registered. Call registerTome() first.");
        }

        $this->tomes[$id]->addPath($path);
    }

    /**
     * Retrieve a single registered Tome by its ID.
     */
    public function find(string $id): ?TomeRegistration
    {
        return $this->tomes[$id] ?? null;
    }

    /**
     * Retrieve all registered Tomes, keyed by their ID.
     *
     * @return array<string, TomeRegistration>
     */
    public function all(): array
    {
        return $this->tomes;
    }

    /**
     * Whether any Tomes have been registered.
     */
    public function isEmpty(): bool
    {
        return empty($this->tomes);
    }
}
