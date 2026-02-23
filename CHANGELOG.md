# Changelog

All notable changes to Grimoire will be documented in this file.

## v1.0.0 - 2026-02-23

### Added

- Tome registration system via `Grimoire::registerTome()` — register top-level documentation sections with id, label, icon, path, and cluster class
- `Grimoire::extendTome()` for adding local content paths to any Tome, with local-first priority on slug collision — enables overriding vendor documentation
- Chapter discovery via `TomeScanner` with YAML frontmatter support (`title`, `order`, `icon`, `hidden`)
- Two locale strategies: suffix (`chapter.fr.md`) and directory (`fr/chapter.md`), with configurable fallback chain
- Inline chapter editing in the panel — toggle between prose view and a Markdown + YAML frontmatter editor without leaving the panel
- `GrimoirePlugin::withDocs()` — opt-in built-in self-documentation Tome, accepts a Closure for conditional display (e.g. local environment only)
- `grimoire:make-tome` Artisan command — scaffolds Cluster stub, index Page stub, and markdown directory
- `grimoire:make-chapter` Artisan command — scaffolds a Page stub and markdown file for an existing Tome
- Navigation group support — configurable label via `GrimoirePlugin::navigationGroup()`; each Tome renders as a standalone navigation item with its registered icon
- Prose theme support — apply a custom CSS class to the chapter content wrapper via `GrimoirePlugin::theme()`
- `grimoire:cache` and `grimoire:clear-cache` commands for production navigation caching
- Breadcrumb support for cluster pages with graceful fallback when cluster routes are not yet registered
- Edit permissions configurable via `config/grimoire.php` closures for both view and edit access
