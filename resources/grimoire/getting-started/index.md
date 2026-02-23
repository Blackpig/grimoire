---
title: Documentation System
order: 0
icon: heroicon-o-book-open
---

# Grimoire — In-Panel Documentation

**Grimoire** is a FilamentPHP plugin that adds a rich, file-based documentation system directly to your admin panel.

Documentation lives in `.md` files on disk — no database required. Authors write Markdown, Grimoire renders it beautifully inside the panel with full syntax highlighting and a clean navigation structure.

## How It Works

- **Tomes** are top-level documentation sections (e.g. "Block Builder", "Website Management"). Each Tome appears as a navigation cluster in the sidebar.
- **Chapters** are individual Markdown documents within a Tome. Adding a `.md` file to the right directory makes it appear automatically — no extra commands needed.
- **The Registry** collects Tome registrations from service providers across all installed packages and builds the complete navigation tree at boot time.

## Getting Started

1. [Registering a Tome](registering-a-tome) — How to create your first Tome and wire it up
2. [Extending a Tome](extending-a-tome) — Add local chapters to a package's Tome
3. [Writing Chapters](writing-chapters) — Markdown tips and frontmatter reference
4. [Images](images) — Where to store documentation screenshots
