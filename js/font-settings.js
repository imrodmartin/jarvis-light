/**
 * @file
 * Jarvis font settings: populate weight options per family + live preview.
 *
 * The preview loads fonts from Google's CDN (admin convenience only). The live
 * site uses the self-hosted copies written on save — see theme-settings.php.
 */
(function (Drupal, drupalSettings, once) {
  'use strict';

  function parseVariant(variant) {
    var m = String(variant).match(/(\d+)(i)?/);
    return { weight: m ? m[1] : '400', italic: !!(m && m[2]) };
  }

  function loadPreviewFont(family, variant) {
    if (!family) {
      return;
    }
    var v = parseVariant(variant);
    var id = 'jarvis-gf-' + family.replace(/\W/g, '') + v.weight + (v.italic ? 'i' : '');
    if (document.getElementById(id)) {
      return;
    }
    var link = document.createElement('link');
    link.id = id;
    link.rel = 'stylesheet';
    link.href = 'https://fonts.googleapis.com/css2?family=' +
      encodeURIComponent(family).replace(/%20/g, '+') +
      (v.italic ? ':ital,wght@1,' + v.weight : ':wght@' + v.weight) + '&display=swap';
    document.head.appendChild(link);
  }

  Drupal.behaviors.jarvisFonts = {
    attach: function (context) {
      var families = ((drupalSettings.jarvisFonts || {}).families) || {};

      once('jarvisFonts', '.jarvis-font-family', context).forEach(function (familySel) {
        var slot = familySel.getAttribute('data-slot');
        var weightSel = document.querySelector('.jarvis-font-weight[data-slot="' + slot + '"]');
        var sample = document.querySelector('.jarvis-font-preview-sample[data-slot="' + slot + '"]');
        var wanted = weightSel.getAttribute('data-selected') || weightSel.value;

        function fillWeights() {
          var variants = families[familySel.value] || ['400'];
          weightSel.innerHTML = '';
          variants.forEach(function (variant) {
            var opt = document.createElement('option');
            opt.value = variant;
            opt.textContent = variant;
            if (variant === wanted) {
              opt.selected = true;
            }
            weightSel.appendChild(opt);
          });
        }

        function updatePreview() {
          if (!sample) {
            return;
          }
          loadPreviewFont(familySel.value, weightSel.value);
          var v = parseVariant(weightSel.value);
          sample.style.fontFamily = familySel.value ? "'" + familySel.value + "'" : '';
          sample.style.fontWeight = v.weight;
          sample.style.fontStyle = v.italic ? 'italic' : 'normal';
        }

        fillWeights();
        updatePreview();

        familySel.addEventListener('change', function () {
          wanted = null;
          fillWeights();
          updatePreview();
        });
        weightSel.addEventListener('change', updatePreview);
      });
    }
  };
})(Drupal, drupalSettings, once);
