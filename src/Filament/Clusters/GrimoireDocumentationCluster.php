<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Filament\Clusters;

use BlackpigCreatif\Grimoire\GrimoirePlugin;

/**
 * Built-in Cluster stub for Grimoire's own self-documentation Tome.
 *
 * This Cluster lives inside the package itself — no host app stub generation
 * is required. The GrimoirePlugin registers it directly with the panel.
 *
 * Visibility is controlled by the withDocs() condition passed to GrimoirePlugin,
 * which may be a bool or a Closure receiving the authenticated user.
 */
final class GrimoireDocumentationCluster extends GrimoireTomeCluster
{
    public static string $tomeId = 'grimoire';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return GrimoirePlugin::get()->shouldShowDocs($user);
    }
}
