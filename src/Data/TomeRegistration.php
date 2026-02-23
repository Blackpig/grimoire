<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Data;

/**
 * Represents a registered Tome — a top-level documentation section.
 *
 * @property-read list<string> $paths All filesystem paths contributing content to this Tome (vendor + local extensions).
 */
final class TomeRegistration
{
    /** @var list<string> */
    private array $paths;

    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $icon,
        public readonly string $clusterClass,
        string $path,
        public readonly ?string $slug = null,
    ) {
        $this->paths = [$path];
    }

    /**
     * Add an additional path to this Tome (used by extendTome()).
     * Local extension paths are prepended so they take priority on slug collision.
     */
    public function addPath(string $path): void
    {
        // Local extension paths are prepended to give them resolution priority.
        array_unshift($this->paths, $path);
    }

    /**
     * All registered filesystem paths for this Tome, in priority order (local first).
     *
     * @return list<string>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * The URL-safe slug for this Tome, derived from the ID if not explicitly set.
     * Using the ID (not the label) ensures the slug matches the cluster's own fallback
     * in GrimoireTomeCluster::getSlug(), which also uses the ID when the registration
     * is not yet available (e.g. during Filament's route registration phase).
     */
    public function getSlug(): string
    {
        return $this->slug ?? str($this->id)->replace('.', '-')->slug()->toString();
    }

    /**
     * Whether the given filesystem path is a vendor path (i.e. inside vendor/).
     * Vendor chapters are always read-only in the panel.
     */
    public function isVendorPath(string $filePath): bool
    {
        return str_contains($filePath, '/vendor/');
    }
}
