# Jarvis

DXPR-style SDC theme with Bootstrap 5, built for Drupal Canvas and Layout Builder.
Ships a recipe that pulls in and enables everything it needs.

## Requirements

- Drupal `^11 || ^12`
- Composer + Drush
- Contrib modules (pulled automatically by Composer): `canvas`, `canvas_field_component`, `focal_point` (→ `crop`), `twig_tweak`

## Install

Add this repo as a Composer source in your project's `composer.json`, then require it:

```jsonc
// composer.json
"repositories": [
    { "type": "vcs", "url": "https://github.com/imrodmartin/jarvis" }
]
```

```bash
composer require imrodmartin/jarvis
```

Composer installs the theme to `web/themes/custom/jarvis` and downloads the
contrib modules to `web/modules/contrib`. Then apply the bundled recipe:

```bash
drush theme:enable jarvis
drush recipe web/themes/custom/jarvis/recipe
drush cache:rebuild
```

The recipe enables the modules, sets Jarvis as the default theme, and imports
its base config.

## What the recipe sets up

- Enables `twig_tweak`, `focal_point`, `canvas`, `canvas_field_component`
- Sets Jarvis as the default theme
- Imports base config: custom image styles (`hero_banner`, `wide`), the
  `focal_point` crop type, and theme settings

Canvas auto-discovers the theme's SDC components (card, hero, image, section,
etc.) on cache rebuild — they are not shipped as config.

### Not bundled (site-specific)

Region layouts, block placements, and Canvas content templates depend on your
site's content types and content, so they are intentionally left out. Build
them per site, or export and add them to `recipe/config/` yourself.

## License

GPL-2.0-or-later
