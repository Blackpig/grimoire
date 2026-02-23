<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire;

use BlackpigCreatif\Grimoire\Facades\Grimoire;
use BlackpigCreatif\Grimoire\Filament\Clusters\GrimoireDocumentationCluster;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;

class GrimoirePlugin implements Plugin
{
    protected string $navigationGroup = '';

    protected string $theme = '';

    /**
     * Whether to register Grimoire's own built-in self-documentation Tome.
     * Off by default — opt in with ->withDocs().
     * A Closure receives the authenticated user and must return bool.
     *
     * @var bool|Closure(mixed): bool
     */
    protected bool | Closure $withDocs = false;

    public function getId(): string
    {
        return 'grimoire';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    /**
     * Override the navigation group label.
     */
    public function navigationGroup(string $label): static
    {
        $this->navigationGroup = $label;

        return $this;
    }

    /**
     * Set a custom prose theme CSS class to append to the chapter wrapper.
     */
    public function theme(string $cssClass): static
    {
        $this->theme = $cssClass;

        return $this;
    }

    /**
     * Register Grimoire's own built-in self-documentation Tome in this panel.
     *
     * Accepts a bool or a Closure that receives the authenticated user and returns bool.
     * The Closure is evaluated at request time (inside canAccess()), so auth() is available.
     *
     * Examples:
     *   ->withDocs()                                    // always shown
     *   ->withDocs(fn ($user) => $user->isSuperAdmin()) // shown only to superadmins
     *   ->withDocs(app()->isLocal())                    // shown only in local environment
     *
     * @param  bool|Closure(mixed): bool  $condition
     */
    public function withDocs(bool | Closure $condition = true): static
    {
        $this->withDocs = $condition;

        return $this;
    }

    /**
     * Evaluate the withDocs condition against the given user.
     * Called from GrimoireDocumentationCluster::canAccess().
     */
    public function shouldShowDocs(mixed $user): bool
    {
        if ($this->withDocs instanceof Closure) {
            return (bool) ($this->withDocs)($user);
        }

        return $this->withDocs;
    }

    public function register(Panel $panel): void
    {
        $group = $this->navigationGroup ?: config('grimoire.navigation_group', 'Help');
        $theme = $this->theme ?: config('grimoire.theme', '');

        // Merge plugin-level overrides into config so pages and clusters can read them.
        config([
            'grimoire.navigation_group' => $group,
            'grimoire.theme' => $theme,
        ]);

        if ($this->withDocs !== false) {
            // Register the Grimoire self-documentation Tome in the registry.
            Grimoire::registerTome(
                id: 'grimoire',
                label: 'Grimoire Documentation',
                icon: 'heroicon-o-book-open',
                path: dirname(__DIR__) . '/resources/grimoire/getting-started',
                clusterClass: GrimoireDocumentationCluster::class,
                slug: 'grimoire',
            );

            // Discover the built-in Cluster and Chapter Page stubs from inside the package.
            $panel->discoverClusters(
                in: __DIR__ . '/Filament/Clusters',
                for: 'BlackpigCreatif\\Grimoire\\Filament\\Clusters',
            );

            $panel->discoverPages(
                in: __DIR__ . '/Filament/Pages',
                for: 'BlackpigCreatif\\Grimoire\\Filament\\Pages',
            );
        }

        // Discover host-app generated Cluster stubs from app/Filament/Grimoire/Clusters/.
        // The grimoire:make-tome Artisan command generates these stubs.
        $hostClusterPath = app_path('Filament/Grimoire/Clusters');

        if (is_dir($hostClusterPath)) {
            $panel->discoverClusters(
                in: $hostClusterPath,
                for: 'App\\Filament\\Grimoire\\Clusters',
            );
        }

        // Discover host-app generated Chapter Page stubs from app/Filament/Grimoire/Pages/.
        // grimoire:make-tome generates an index stub; grimoire:make-chapter adds more.
        $hostPagePath = app_path('Filament/Grimoire/Pages');

        if (is_dir($hostPagePath)) {
            $panel->discoverPages(
                in: $hostPagePath,
                for: 'App\\Filament\\Grimoire\\Pages',
            );
        }

    }

    public function boot(Panel $panel): void
    {
        //
    }
}
