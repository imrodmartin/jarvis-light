# Jarvis Light

DXPR-style SDC theme with Bootstrap 5, built for Drupal Canvas and Layout Builder.
Ships a recipe that pulls in and enables everything it needs.

> **Frozen snapshot.** This is `jarvis-light`, the theme we use in training.


## Requirements

- PHP `>= 8.3`
- Drupal `^11 || ^12`
- Composer + Drush
- Contrib modules (pulled automatically by Composer): `canvas`, `canvas_field_component`, `focal_point` (→ `crop`), `twig_tweak`

## Install

Register this repo as a Composer VCS source, then require it:

```bash
composer config repositories.jarvis-light '{"type":"vcs","url":"https://github.com/imrodmartin/jarvis-light","no-api":true}'
composer require imrodmartin/jarvis-light
```

`no-api` makes Composer clone over git instead of the GitHub API — it avoids the
unauthenticated 60-calls/hour API rate limit (and the occasional `502`) that
otherwise blocks the install.

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

> **DDEV (or any containerised Drush):** pass the recipe as an **absolute
> container path**, since `ddev drush` resolves relative paths from the
> container working dir:
>
> ```bash
> ddev drush recipe /var/www/html/web/themes/custom/jarvis/recipe
> ```

> **Order matters.** If you apply the recipe *before* enabling Canvas + the
> theme and rebuilding, the import fails with
> `getParametrizedImageStyle(): ... null returned` or `Missing component
> source` — Canvas needs the intermediate `cache:rebuild` to register its
> image style and the theme's components.

## What the recipe sets up

- Enables the remaining modules: `twig_tweak`, `focal_point` (→ `crop`),
  `media`, `media_library`, `image`, `menu_ui`, `menu_link_content`,
  `datetime`, `options`, `path`
- Sets Jarvis as the default theme and places its blocks in the correct
  regions (via config actions on the auto-created theme blocks)
- Sets the site front page to `/test-page` (the demo Canvas page) — change it
  under **Configuration → Basic site settings** after install if unwanted
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
