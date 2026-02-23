<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Navigation Group
    |--------------------------------------------------------------------------
    |
    | The label for the navigation group shown in the Filament sidebar.
    | Override this in your AppServiceProvider to customise the grouping.
    |
    */
    'navigation_group' => 'Help',

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Closures that receive the authenticated user and return a boolean.
    |
    | view — controls who can read documentation chapters (default: all users).
    | edit — controls who can edit local .md files in-panel (default: nobody).
    |
    | Example:
    |   'edit' => fn ($user) => $user->hasRole('admin'),
    |
    */
    'permissions' => [
        'view' => fn ($user) => true,
        'edit' => fn ($user) => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale Strategy
    |--------------------------------------------------------------------------
    |
    | How Grimoire resolves locale-specific Markdown files.
    |
    | 'suffix'    — files are named hero-block.en.md, hero-block.fr.md (default)
    | 'directory' — files live in locale subdirectories: en/hero-block.md
    |
    */
    'locale_strategy' => 'suffix',

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | When no file matches the current application locale, Grimoire falls back
    | to this locale before giving up.
    |
    */
    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Image Storage
    |--------------------------------------------------------------------------
    |
    | Where documentation screenshots and images are stored and served from.
    | Reference images in Markdown as: ![Alt text](/images/grimoire/file.png)
    |
    */
    'images_path' => public_path('images/grimoire'),
    'images_url' => '/images/grimoire',

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | In production, Grimoire caches the scanned navigation tree to
    | bootstrap/cache/grimoire.php for performance. Set to false locally
    | (or via GRIMOIRE_CACHE=false in your .env) to rescan on every request.
    |
    */
    'cache' => env('GRIMOIRE_CACHE', true),

    /*
    |--------------------------------------------------------------------------
    | Prose Theme
    |--------------------------------------------------------------------------
    |
    | An optional CSS class string appended to the chapter prose wrapper.
    | Use this to apply a custom Tailwind Typography theme (e.g. 'prose-brand').
    | Can also be set fluently on GrimoirePlugin::make()->theme('prose-brand').
    |
    */
    'theme' => '',

];
