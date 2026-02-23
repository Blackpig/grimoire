<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Filament\Clusters;

use BackedEnum;
use BlackpigCreatif\Grimoire\Data\TomeRegistration;
use BlackpigCreatif\Grimoire\Services\TomeRegistry;
use Filament\Clusters\Cluster;
use Filament\Panel;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Base Cluster class for all Grimoire Tomes.
 *
 * All Tome stub Clusters (both package-internal and host-app-generated) extend
 * this class and declare a single static property: $tomeId.
 *
 * This base class reads that ID, looks up the TomeRegistration in the registry,
 * and configures Filament navigation (label, icon, group) dynamically.
 */
abstract class GrimoireTomeCluster extends Cluster
{
    /**
     * The unique Tome ID. Must be set on every concrete stub subclass.
     */
    public static string $tomeId = '';

    public static function getNavigationLabel(): string
    {
        return static::getTomeRegistration()?->label ?? parent::getNavigationLabel();
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return static::getTomeRegistration()?->label ?? parent::getClusterBreadcrumb();
    }

    /**
     * @param  array<string, string>  $breadcrumbs
     * @return array<string, string>
     */
    public static function unshiftClusterBreadcrumbs(array $breadcrumbs): array
    {
        $label = static::getClusterBreadcrumb();

        if ($label === null) {
            return $breadcrumbs;
        }

        try {
            $url = static::getUrl();
        } catch (RouteNotFoundException | \InvalidArgumentException) {
            $url = '#';
        }

        return [
            ...[$url => $label],
            ...$breadcrumbs,
        ];
    }

    public static function getNavigationIcon(): string | BackedEnum | null
    {
        return static::getTomeRegistration()?->icon ?? parent::getNavigationIcon();
    }

    public static function getNavigationGroup(): ?string
    {
        return config('grimoire.navigation_group', 'Help');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        $registration = static::getTomeRegistration();

        if ($registration !== null) {
            return $registration->getSlug();
        }

        return str(static::$tomeId)->replace('.', '-')->slug()->toString();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        $viewPermission = config('grimoire.permissions.view', fn ($u) => true);

        return (bool) $viewPermission($user);
    }

    /**
     * Retrieve the TomeRegistration for this Cluster's tomeId.
     */
    protected static function getTomeRegistration(): ?TomeRegistration
    {
        if (static::$tomeId === '') {
            return null;
        }

        return app(TomeRegistry::class)->find(static::$tomeId);
    }
}
