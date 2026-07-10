<?php

/**
 * @file
 * Jarvis theme settings — color controls.
 */

use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

/**
 * Implements hook_form_system_theme_settings_alter().
 *
 * Each color is a hex textfield paired with a native <input type="color">. The
 * color input is the live sample + picker; JS keeps the two in sync. Only the
 * hex textfield submits (the color input has no name), so there's one stored
 * value per color.
 */
function jarvis_form_system_theme_settings_alter(array &$form, FormStateInterface $form_state) {
  $form['jarvis_colors'] = [
    '#type' => 'details',
    '#title' => t('Colors'),
    '#open' => TRUE,
    '#weight' => -20,
  ];

  foreach (_jarvis_colors() as $key => $info) {
    [$label, , , $default] = $info;
    $val = theme_get_setting($key);
    $val = (is_string($val) && $val !== '') ? $val : $default;
    $swatch = ($val !== '') ? $val : '#ffffff';

    $form['jarvis_colors'][$key] = [
      '#type' => 'textfield',
      '#title' => $label,
      '#default_value' => $val,
      '#size' => 10,
      '#maxlength' => 7,
      '#attributes' => [
        'class' => ['jarvis-color-hex'],
        'pattern' => '#[0-9a-fA-F]{6}',
        'placeholder' => '#ffffff',
        'autocomplete' => 'off',
        'spellcheck' => 'false',
      ],
      '#field_prefix' => Markup::create(
        '<input type="color" class="jarvis-color-pick" aria-label="'
        . htmlspecialchars((string) $label, ENT_QUOTES) . ' picker" value="'
        . htmlspecialchars($swatch, ENT_QUOTES) . '">'
      ),
    ];
  }

  $form['#attached']['library'][] = 'jarvis/color-settings';
  $form['#validate'][] = 'jarvis_color_settings_validate';

  // ---------------------------------------------------------------------------
  // Fonts.
  // ---------------------------------------------------------------------------
  $families = [];
  $json = @file_get_contents(__DIR__ . '/google-fonts.json');
  if ($json) {
    $families = json_decode($json, TRUE)['families'] ?? [];
  }
  $family_options = ['' => t('- None -')];
  foreach (array_keys($families) as $f) {
    $family_options[$f] = $f;
  }

  $form['jarvis_fonts'] = [
    '#type' => 'details',
    '#title' => t('Fonts'),
    '#open' => TRUE,
    '#weight' => -15,
    '#description' => t('Pick any Google font per slot and target it with a CSS selector. On save, the fonts are downloaded and self-hosted — no request to Google is made on the live site.'),
  ];

  $preview_rows = '';
  foreach (_jarvis_fonts() as $key => $info) {
    [$label, $default_selector] = $info;
    $saved_family = (string) (theme_get_setting("jarvis_font_{$key}_family") ?: '');
    $saved_weight = (string) (theme_get_setting("jarvis_font_{$key}_weight") ?: '400');
    $saved_selector = theme_get_setting("jarvis_font_{$key}_selector");
    $saved_selector = ($saved_selector === NULL || $saved_selector === '') ? $default_selector : $saved_selector;

    // Weight options for the initially-saved family (JS repopulates on change).
    $weight_options = [];
    foreach (($families[$saved_family] ?? ['400']) as $v) {
      $weight_options[$v] = $v;
    }
    if (!$weight_options) {
      $weight_options = ['400' => '400'];
    }

    $form['jarvis_fonts'][$key] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['jarvis-font-row']],
    ];
    $form['jarvis_fonts'][$key]["jarvis_font_{$key}_family"] = [
      '#type' => 'select',
      '#title' => $label,
      '#options' => $family_options,
      '#default_value' => $saved_family,
      '#attributes' => ['class' => ['jarvis-font-family'], 'data-slot' => $key],
    ];
    $form['jarvis_fonts'][$key]["jarvis_font_{$key}_weight"] = [
      '#type' => 'select',
      '#title' => t('Weight / style'),
      '#options' => $weight_options,
      '#default_value' => $saved_weight,
      // Options are rebuilt client-side per family, so skip server validation.
      '#validated' => TRUE,
      '#attributes' => ['class' => ['jarvis-font-weight'], 'data-slot' => $key, 'data-selected' => $saved_weight],
    ];
    $form['jarvis_fonts'][$key]["jarvis_font_{$key}_selector"] = [
      '#type' => 'textfield',
      '#title' => t('CSS selector'),
      '#default_value' => $saved_selector,
      '#attributes' => ['class' => ['jarvis-font-selector'], 'autocomplete' => 'off', 'spellcheck' => 'false'],
    ];

    $preview_rows .= '<div class="jarvis-font-preview-row"><span class="jarvis-font-preview-label">'
      . htmlspecialchars((string) $label, ENT_QUOTES) . '</span>'
      . '<span class="jarvis-font-preview-sample" data-slot="' . htmlspecialchars($key, ENT_QUOTES) . '">'
      . 'The quick brown fox jumps over the lazy dog 0123456789</span></div>';
  }

  $form['jarvis_fonts']['preview'] = [
    '#type' => 'item',
    '#title' => t('Preview'),
    '#markup' => Markup::create('<div class="jarvis-font-preview">' . $preview_rows . '</div>'),
    '#weight' => 100,
  ];

  $form['jarvis_fonts']['#attached']['library'][] = 'jarvis/font-settings';
  $form['jarvis_fonts']['#attached']['drupalSettings']['jarvisFonts']['families'] = $families;
  $form['#submit'][] = 'jarvis_font_settings_submit';

  // ---------------------------------------------------------------------------
  // Font sizes.
  // ---------------------------------------------------------------------------
  $form['jarvis_font_sizes'] = [
    '#type' => 'details',
    '#title' => t('Font sizes'),
    '#open' => TRUE,
    '#weight' => -14,
    '#description' => t('Base size is in px and sets the root size; headings are in rem so they scale with it. Mobile values apply below 768px.'),
  ];
  foreach (['' => t('Desktop'), 'mobile_' => t('Mobile (under 768px)')] as $prefix => $group_label) {
    $group = $prefix === '' ? 'desktop' : 'mobile';
    $form['jarvis_font_sizes'][$group] = [
      '#type' => 'fieldset',
      '#title' => $group_label,
    ];
    foreach (_jarvis_font_sizes() as $key => $info) {
      [$label, $d_default, $m_default, $unit] = $info;
      $default = $prefix === '' ? $d_default : $m_default;
      $val = theme_get_setting("jarvis_fs_{$prefix}{$key}");
      $form['jarvis_font_sizes'][$group]["jarvis_fs_{$prefix}{$key}"] = [
        '#type' => 'number',
        '#title' => $label,
        '#default_value' => is_numeric($val) ? $val : $default,
        // Unit plus the live preview sized by js/fs-settings.js — in the
        // field suffix so it sits inline next to the input.
        '#field_suffix' => Markup::create(
          $unit . ' <span class="jarvis-fs-preview" data-group="' . $group . '" data-slot="' . $key . '" aria-hidden="true"></span>'
        ),
        '#min' => $unit === 'px' ? 10 : 0.5,
        '#max' => $unit === 'px' ? 32 : 8,
        '#step' => $unit === 'px' ? 1 : 0.005,
        '#required' => TRUE,
        '#attributes' => [
          'class' => ['jarvis-fs-input'],
          'data-group' => $group,
          'data-slot' => $key,
        ],
      ];
    }
  }
  $form['jarvis_font_sizes']['#attached']['library'][] = 'jarvis/fs-settings';
}

/**
 * Submit handler: download the chosen Google fonts and write the stylesheet.
 *
 * For each slot we fetch the css2 payload (Chrome UA → woff2 with unicode-range
 * subsets), download every referenced woff2 into public://jarvis-fonts/, rewrite
 * the url() to the local copy, and append a selector rule. Everything is written
 * to jarvis-fonts.css, which hook_preprocess_html attaches on the live site.
 */
function jarvis_font_settings_submit(array &$form, FormStateInterface $form_state) {
  /** @var \Drupal\Core\File\FileSystemInterface $fs */
  $fs = \Drupal::service('file_system');
  $dir = 'public://jarvis-fonts';
  $fs->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

  $client = \Drupal::httpClient();
  $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';
  $url_gen = \Drupal::service('file_url_generator');

  $faces = [];
  $rules = [];
  $fetched = [];
  foreach (array_keys(_jarvis_fonts()) as $key) {
    $family = trim((string) $form_state->getValue("jarvis_font_{$key}_family"));
    $variant = (string) ($form_state->getValue("jarvis_font_{$key}_weight") ?: '400');
    $selector = trim((string) $form_state->getValue("jarvis_font_{$key}_selector"));
    if ($family === '' || $selector === '') {
      continue;
    }

    $v = _jarvis_font_variant($variant);
    // Selector rule. Strip braces so the setting can't break out of the block;
    // family comes from our shipped list so it is safe to quote directly.
    $sel = str_replace(['{', '}'], '', $selector);
    $fam_safe = str_replace("'", '', $family);
    $style = $v['italic'] ? 'italic' : 'normal';
    $rules[] = "$sel{font-family:'$fam_safe',sans-serif;font-weight:{$v['weight']};font-style:$style;}";

    // Download the @font-face payload once per family+variant.
    $id = "$family|$variant";
    if (isset($fetched[$id])) {
      continue;
    }
    $fetched[$id] = TRUE;

    try {
      $css = (string) $client->get(_jarvis_font_css_url($family, $variant), [
        'headers' => ['User-Agent' => $ua],
        'timeout' => 20,
      ])->getBody();
    }
    catch (\Throwable $e) {
      \Drupal::messenger()->addWarning(t('Could not fetch %f from Google Fonts: @m', ['%f' => $family, '@m' => $e->getMessage()]));
      continue;
    }

    // Download each woff2 and point the url() at the local copy.
    $css = preg_replace_callback('#url\((https://fonts\.gstatic\.com/[^)]+\.woff2)\)#', function ($m) use ($client, $fs, $dir, $ua, $url_gen) {
      $src = $m[1];
      $name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string) parse_url($src, PHP_URL_PATH)));
      $dest = "$dir/$name";
      if (!file_exists((string) $fs->realpath($dest))) {
        try {
          $bin = (string) $client->get($src, ['headers' => ['User-Agent' => $ua], 'timeout' => 20])->getBody();
          $fs->saveData($bin, $dest, FileExists::Replace);
        }
        catch (\Throwable $e) {
          return $m[0];
        }
      }
      return 'url(' . $url_gen->generateString($dest) . ')';
    }, $css);

    $faces[] = $css;
  }

  $out = implode("\n", $faces) . "\n\n" . implode("\n", $rules) . "\n";
  $fs->saveData($out, "$dir/jarvis-fonts.css", FileExists::Replace);
  \Drupal::messenger()->addStatus(t('Fonts downloaded and self-hosted.'));
}

/**
 * WCAG relative-luminance contrast ratio between two #rrggbb colors.
 *
 * Same math as js/contrast.js; used for the non-blocking AA warning below.
 */
function _jarvis_contrast_ratio(string $a, string $b): float {
  $lum = function (string $hex): float {
    $c = [];
    foreach ([1, 3, 5] as $i) {
      $v = hexdec(substr($hex, $i, 2)) / 255;
      $c[] = $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    }
    return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
  };
  $l1 = $lum($a);
  $l2 = $lum($b);
  return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
}

/**
 * Validate handler: reject anything that isn't a #rrggbb hex (or empty).
 *
 * Also warns (non-blocking) when a text/background pair falls below WCAG AA
 * (4.5:1) so low-contrast picks are a conscious choice, not an accident.
 */
function jarvis_color_settings_validate(array &$form, FormStateInterface $form_state) {
  foreach (array_keys(_jarvis_colors()) as $key) {
    $val = (string) $form_state->getValue($key);
    if ($val !== '' && !preg_match('/^#[0-9a-fA-F]{6}$/', $val)) {
      $form_state->setErrorByName($key, t('%v is not a valid hex color (use #rrggbb).', ['%v' => $val]));
    }
  }

  $pairs = [
    ['jarvis_color_title_text', 'jarvis_color_title_bg', 'Title text on title background'],
    ['jarvis_color_footer_text', 'jarvis_color_footer_bg', 'Footer text on footer background'],
    ['jarvis_color_header_text', 'jarvis_color_header_bg', 'Header text on header background'],
    ['jarvis_color_header_link', 'jarvis_color_header_bg', 'Header links on header background'],
  ];
  foreach ($pairs as [$fg_key, $bg_key, $label]) {
    $fg = (string) $form_state->getValue($fg_key);
    $bg = (string) $form_state->getValue($bg_key);
    if (preg_match('/^#[0-9a-fA-F]{6}$/', $fg) && preg_match('/^#[0-9a-fA-F]{6}$/', $bg)) {
      $ratio = _jarvis_contrast_ratio($fg, $bg);
      if ($ratio < 4.5) {
        \Drupal::messenger()->addWarning(t('@pair contrast is @ratio:1 — below the WCAG AA minimum of 4.5:1.', [
          '@pair' => $label,
          '@ratio' => round($ratio, 1),
        ]));
      }
    }
  }
}
