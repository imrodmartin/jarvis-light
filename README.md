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
contrib modules to `web/modules/contrib`.

If you clone the repo by hand instead, the directory **must** be named
`jarvis` — that is the theme's machine name, and both the install commands
and the recipe's config reference it. Then run, **in this order**:

```bash
# 1. Enable Canvas + the theme FIRST and rebuild the cache.
#    Canvas registers its parametrized image style and the theme's SDC
#    components during this rebuild — the recipe's config and demo content
#    reference them, so they must exist before the recipe runs.
drush pm:install canvas canvas_field_component
drush theme:install jarvis
drush cache:rebuild

# 2. Apply the recipe, then rebuild again.
#    The path is relative to the DOCROOT (web/), not the project root — see below.
drush recipe themes/custom/jarvis/recipe
drush cache:rebuild
```

> **Recipe paths are relative to the docroot.** Before it bootstraps, Drush
> `chdir()`s to the Drupal root, and `drush recipe` resolves its argument
> against that. So from a standard project layout the path is
> `themes/custom/jarvis/recipe` — **not** `web/themes/custom/jarvis/recipe`,
> which fails with `The supplied path ... is not a directory` no matter which
> directory you run it from. This is plain Drush behaviour, not specific to
> any container setup.
>
> An absolute path always works and is the safer choice under DDEV, Lando, or
> any other containerised Drush:
>
> ```bash
> ddev drush recipe /var/www/html/web/themes/custom/jarvis/recipe
> ```

> **The recipe applies once.** Canvas normalises the component-tree keys when
> it saves config (`'0:43c8216d-…'` is stored as `43c8216d-…`), so the config
> in the database no longer matches the exported recipe files. A second
> `drush recipe` therefore aborts with `exists already and does not match`,
> even on a site where nothing was touched. If an apply fails partway, roll
> the database back rather than re-running.

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
- Imports demo content: the **Test Blog** node, the **Welcome** Canvas page
  (aliased to `/test-page`) + its main-menu link, and three media items (two
  images and one remote video)

Canvas auto-discovers the theme's SDC components (card, hero, image, section,
etc.) on cache rebuild — they are not shipped as config, so the content
template and Canvas page pin the component versions of *this* theme release.

## License

GPL-2.0-or-later
