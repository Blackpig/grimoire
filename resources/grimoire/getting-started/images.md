---
title: Images
order: 4
icon: heroicon-o-photo
---

# Images

Store documentation screenshots and images in `public/images/grimoire/` (the default location, configurable in `config/grimoire.php`).

## Referencing images in Markdown

```markdown
![Hero Block settings panel](/images/grimoire/hero-block-settings.png)
```

Since these are served from `public/`, they're referenced by URL path directly.

## Configuration

```php
// config/grimoire.php
'images_path' => public_path('images/grimoire'),
'images_url'  => '/images/grimoire',
```

You can change both the storage path and the public URL if your setup uses a CDN or different public directory structure.

## Tips

- Use `.png` for screenshots with UI elements (crisp edges, transparency).
- Use `.jpg` for photographs.
- Keep image file sizes reasonable — these are docs, not a gallery.
- Name images descriptively: `hero-block-settings-panel.png` not `screenshot1.png`.
