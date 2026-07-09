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
contrib modules to `web/modules/contrib`. Then run, **in this order**:

```bash
# 1. Enable Canvas + the theme FIRST and rebuild the cache.
#    Canvas registers its parametrized image style and the theme's SDC
#    components during this rebuild — the recipe's config and demo content
#    reference them, so they must exist before the recipe runs.
drush pm:install canvas canvas_field_component
drush theme:install jarvis
drush cache:rebuild

# 2. Apply the recipe, then rebuild again.
drush recipe web/themes/custom/jarvis/recipe
drush cache:rebuild
```

> **Order matters.** If you apply the recipe *before* enabling Canvas + the
> theme and rebuilding, the import fails with
> `getParametrizedImageStyle(): ... null returned` or `Missing component
> source` — Canvas needs the intermediate `cache:rebuild` to register its
> image style and the theme's components.

## What the recipe sets up

- Enables the remaining modules: `twig_tweak`, `focal_point` (→ `crop`),
  `media`, `media_library`, `menu_ui`, `menu_link_content`, `datetime`,
  `options`, `path`
- Sets Jarvis as the default theme and places its blocks in the correct
  regions (via config actions on the auto-created theme blocks)
- Imports base config: custom image styles (`hero_banner`, `wide`), the
  `focal_point` crop type, `media` types + fields, and theme settings
- Creates the **Jarvis Sample** content type (fields, form/view displays, and
  a Canvas content template for its full view)
- Imports demo content: the **Test Blog** node, the **Test Page** Canvas page
  (`/test-page`) + its main-menu link, and all five media items

Canvas auto-discovers the theme's SDC components (card, hero, image, section,
etc.) on cache rebuild — they are not shipped as config, so the content
template and Canvas page pin the component versions of *this* theme release.

## License

GPL-2.0-or-later
