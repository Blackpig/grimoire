---
title: Extending a Tome
order: 2
icon: heroicon-o-arrow-path
---

# Extending a Tome

You can add local chapters to any Tome — including those that ship with packages — without modifying the vendor files.

## Adding local chapters to a package Tome

In your `AppServiceProvider::boot()`:

```php
use BlackpigCreatif\Grimoire\Facades\Grimoire;

Grimoire::extendTome(
    id: 'atelier.blocks',
    path: resource_path('grimoire/atelier/blocks'),
);
```

Then create your local chapter files in that directory:

```
resources/
  grimoire/
    atelier/
      blocks/
        faq-block.md
        product-listing.md
```

These chapters appear alongside the package's original chapters in navigation. **If a local file has the same slug as a vendor file, the local file takes priority** — allowing you to override vendor documentation.

## Override vs. extend

| Scenario | What to do |
|---|---|
| Add new chapters | `extendTome()` + create `.md` files |
| Replace a specific chapter | `extendTome()` + create a `.md` file with the same slug |
| Replace all vendor chapters | `grimoire:publish {tome-id}` |

## Publishing vendor docs

To copy all of a Tome's vendor files into `resources/grimoire/` for local editing:

```bash
php artisan grimoire:publish atelier.blocks
```

Published files take priority over vendor files automatically.
