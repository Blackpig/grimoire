---
title: Registering a Tome
order: 1
icon: heroicon-o-plus-circle
---

# Registering a Tome

A Tome is a top-level documentation section. To create one, you need:

1. A **Cluster stub class** in `app/Filament/Grimoire/Clusters/`
2. A **Chapter Page stub class** in `app/Filament/Grimoire/Pages/`
3. A **content directory** with an `index.md` file

The `grimoire:make-tome` Artisan command handles all three automatically.

## Using the Artisan command

```bash
php artisan grimoire:make-tome "Website Management"
```

This creates:

```
app/Filament/Grimoire/Clusters/WebsiteManagementCluster.php
app/Filament/Grimoire/Pages/WebsiteManagementChapterPage.php
resources/grimoire/website-management/index.md
```

It also prints the snippet you need to add to your `AppServiceProvider`.

## Manual registration

Add to your `AppServiceProvider::boot()`:

```php
use BlackpigCreatif\Grimoire\Facades\Grimoire;

Grimoire::registerTome(
    id: 'website',
    label: 'Website Management',
    icon: 'heroicon-o-globe-alt',
    path: resource_path('grimoire/website-management'),
    clusterClass: \App\Filament\Grimoire\Clusters\WebsiteManagementCluster::class,
);
```

## Registering from a package

Packages that ship their own documentation register their Tome in their `ServiceProvider::boot()`:

```php
use BlackpigCreatif\Grimoire\Facades\Grimoire;

Grimoire::registerTome(
    id: 'atelier.blocks',
    label: 'Block Builder',
    icon: 'heroicon-o-squares-2x2',
    path: __DIR__ . '/../resources/grimoire/blocks',
    clusterClass: \BlackpigCreatif\Atelier\Filament\Clusters\AtelierBlocksCluster::class,
);
```

The Cluster stub lives inside the package itself — no host app action required.

## Registering the plugin

Finally, add the Grimoire plugin to your panel provider:

```php
->plugins([
    \BlackpigCreatif\Grimoire\GrimoirePlugin::make()
        ->navigationGroup('Help'),
])
```
