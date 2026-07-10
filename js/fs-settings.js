/**
 * @file
 * Live previews for the font-size settings.
 *
 * Each number input has a sibling preview span. Previews assume the browser
 * default of 100%: <html> gets the group's base px setting, so a heading
 * preview is rem-value x that group's base px; the base preview is its own
 * px value.
 */
(function (Drupal, once) {
  'use strict';

  function update(form) {
    ['desktop', 'mobile'].forEach(function (group) {
      var base = form.querySelector('.jarvis-fs-input[data-group="' + group + '"][data-slot="base"]');
      var basePx = base ? parseFloat(base.value) || 16 : 16;
      form.querySelectorAll('.jarvis-fs-input[data-group="' + group + '"]').forEach(function (input) {
        var preview = form.querySelector('.jarvis-fs-preview[data-group="' + group + '"][data-slot="' + input.dataset.slot + '"]');
        if (!preview) {
          return;
        }
        var v = parseFloat(input.value);
        var px = input.dataset.slot === 'base' ? v : v * basePx;
        if (v > 0) {
          preview.style.fontSize = px + 'px';
          preview.textContent = 'The quick brown fox (' + Math.round(px * 100) / 100 + 'px)';
        }
      });
    });
  }

  Drupal.behaviors.jarvisFsPreview = {
    attach: function (context) {
      once('jarvis-fs-preview', 'form', context).forEach(function (form) {
        if (!form.querySelector('.jarvis-fs-input')) {
          return;
        }
        form.addEventListener('input', function (e) {
          if (e.target.classList.contains('jarvis-fs-input')) {
            update(form);
          }
        });
        update(form);
      });
    }
  };
})(Drupal, once);
