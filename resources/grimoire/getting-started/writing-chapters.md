---
title: Writing Chapters
order: 3
icon: heroicon-o-pencil
---

# Writing Chapters

Chapters are standard Markdown files with optional YAML frontmatter at the top.

## Frontmatter reference

```yaml
---
title: Hero Block          # Required. Display name in navigation and page heading.
order: 2                   # Required. Sort position within the Tome (lower = first).
icon: heroicon-o-photo     # Optional. Heroicon name shown next to the nav item.
hidden: false              # Optional. Hides from navigation but the URL remains accessible.
---
```

- **`index.md`** is the Tome's landing page. Its `title` and `icon` define the Tome's navigation label.
- If `title` is omitted, Grimoire derives it from the filename (e.g. `hero-block` → `Hero Block`).
- If `order` is omitted, the chapter appears after all ordered chapters.

## Adding a new chapter

```bash
php artisan grimoire:make-chapter website "Managing FAQs" --order=3
```

Or create the file manually in `resources/grimoire/{tome-slug}/`:

```
resources/grimoire/website-management/managing-faqs.md
```

The chapter appears in navigation automatically on the next request — no commands needed.

## Markdown features

Grimoire renders full GitHub Flavoured Markdown (GFM):

- **Tables**
- **Task lists**
- **Fenced code blocks** with syntax highlighting
- **Strikethrough**
- **Autolinks**

## Code blocks

Use fenced code blocks with a language identifier for syntax highlighting:

````markdown
```php
public function handle(): int
{
    return self::SUCCESS;
}
```
````

Supported languages include: `php`, `html`, `js`, `ts`, `css`, `bash`, `json`, `yaml`, and many more.

## Editing in the panel

If you have the `edit` permission configured, an **Edit** button appears on each local chapter. Vendor chapters (from `vendor/`) are always read-only.
