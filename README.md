# Grimoire

[![Latest Version on Packagist](https://img.shields.io/packagist/v/blackpig-creatif/grimoire.svg?style=flat-square)](https://packagist.org/packages/blackpig-creatif/grimoire)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/blackpig-creatif/grimoire/run-tests.yml?branch=5.x&label=tests&style=flat-square)](https://github.com/blackpig-creatif/grimoire/actions?query=workflow%3Arun-tests+branch%3A5.x)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/blackpig-creatif/grimoire/fix-php-code-style-issues.yml?branch=5.x&label=code%20style&style=flat-square)](https://github.com/blackpig-creatif/grimoire/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3A5.x)
[![Total Downloads](https://img.shields.io/packagist/dt/blackpig-creatif/grimoire.svg?style=flat-square)](https://packagist.org/packages/blackpig-creatif/grimoire)

**In-panel documentation for FilamentPHP v5 applications.**

Grimoire lets you embed rich, versioned documentation directly inside your Filament admin panel. Organise content into *Tomes* (top-level books) and *Chapters* (individual pages), author in Markdown with YAML frontmatter, and edit chapters inline without leaving the panel.

---

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- Filament v5

---

## Installation

```bash
composer require blackpig-creatif/grimoire
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag="grimoire-config"
```

---

## Plugin Registration

Register the plugin in your Filament panel provider:

```php
use BlackpigCreatif\Grimoire\GrimoirePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            GrimoirePlugin::make()
                ->navigationGroup('Help')    // optional — defaults to 'Help'
                ->withDocs(),                // optional — enables Grimoire's own built-in docs
        ]);
}
```

---

## Registering a Tome

A Tome is a top-level documentation section. Register one in your `AppServiceProvider::boot()`:

```php
use BlackpigCreatif\Grimoire\Facades\Grimoire;

Grimoire::registerTome(
    id: 'my-docs',
    label: 'My Documentation',
    icon: 'heroicon-o-book-open',
    path: resource_path('grimoire/my-docs'),
    clusterClass: \App\Filament\Grimoire\Clusters\MyDocsCluster::class,
);
```

Then scaffold the required Filament stubs:

```bash
php artisan grimoire:make-tome my-docs "My Documentation"
```

---

## Adding Chapters

```bash
php artisan grimoire:make-chapter my-docs my-chapter "My Chapter Title"
```

This generates a Filament page stub and a Markdown file at `resources/grimoire/my-docs/my-chapter.md`. Add content with YAML frontmatter:

```markdown
---
title: My Chapter
order: 1
icon: heroicon-o-document-text
---

# My Chapter

Content goes here.
```

---

## Extending a Tome

Override or add chapters to any Tome (including vendor Tomes) from your app:

```php
Grimoire::extendTome(
    id: 'my-docs',
    path: resource_path('grimoire/my-docs-local'),
);
```

Local chapters take priority over vendor chapters on slug collision — useful for overriding package documentation with app-specific notes.

---

## Inline Editing

Authenticated users with edit permission can edit chapter content directly in the panel. Click **Edit** on any chapter page to switch to an inline editor with a Markdown editor for content and a YAML editor for frontmatter.

Edit permissions are configurable in `config/grimoire.php`:

```php
'permissions' => [
    'view' => fn ($user) => true,
    'edit' => fn ($user) => $user->is_admin,
],
```

---

## Locale Support

Grimoire supports two locale strategies for multilingual documentation. Configure in `config/grimoire.php`:

```php
'locale_strategy' => 'suffix',   // 'suffix' or 'directory'
'fallback_locale'  => 'en',
```

**Suffix strategy** — place locale variants alongside the default file:

```
my-chapter.md       ← locale-neutral fallback
my-chapter.fr.md    ← French
my-chapter.de.md    ← German
```

**Directory strategy** — organise by locale subdirectory:

```
my-chapter.md
fr/my-chapter.md
de/my-chapter.md
```

Grimoire resolves files in order: current locale → fallback locale → locale-neutral.

---

## Built-in Self-Documentation

Enable Grimoire's own documentation Tome inside your panel with `->withDocs()`:

```php
GrimoirePlugin::make()->withDocs()

// Or conditionally — e.g. local environment only:
GrimoirePlugin::make()->withDocs(fn ($user) => app()->isLocal())
```

---

## Caching

For production, cache the navigation tree:

```bash
php artisan grimoire:cache
php artisan grimoire:clear-cache
```

---

## Testing

```bash
composer test
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Blackpig Creatif](https://github.com/blackpig-creatif)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
