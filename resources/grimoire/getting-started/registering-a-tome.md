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

Add the plugin to every panel that should show your own Tomes (the stubs generated into `app/Filament/Grimoire`). Tomes shipped by other packages are registered by those packages' plugins and appear without it.

```php
->plugins([
    \BlackpigCreatif\Grimoire\GrimoirePlugin::make()
        ->navigationGroup('Help')   // optional — default is 'Help'
        ->tomes(['my-docs'])        // optional — only these Tomes in this panel
        ->withDocs()                // optional — show Grimoire's own documentation
        ->theme('prose-slate'),     // optional — custom CSS class on the chapter wrapper
])
```

Each option applies to that panel only.

## Theming — adding Grimoire styles

Grimoire ships its own self-contained CSS for rendering Markdown as readable prose. Without it, chapter pages will display unstyled HTML.

Add the import to your panel's theme CSS file (typically `resources/css/filament/admin/theme.css`):

```css
@import '../../../../vendor/blackpig-creatif/grimoire/resources/css/grimoire.css';
```

Then rebuild your panel's assets:

```bash
npm run build
```

> **Tip:** The Grimoire stylesheet uses Filament's CSS custom properties (`--color-gray-*`, etc.) for colours, so it automatically adapts to light and dark mode without any extra configuration.
