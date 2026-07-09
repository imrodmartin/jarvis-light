/**
 * @file
 * Auto-darkens the hero overlay until white text passes WCAG AA contrast.
 * SDC auto-attaches this sibling file as the hero component library.
 */
(function () {
  'use strict';

  var NEED = 4.5;   // WCAG AA, normal text (subheading is the tightest case)
  var TEXT = 1;     // relative luminance of #fff text

  // sRGB channel (0-255) -> linear
  function chan(c) {
    c /= 255;
    return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
  }
  function lum(r, g, b) {
    return 0.2126 * chan(r) + 0.7152 * chan(g) + 0.0722 * chan(b);
  }
  function contrast(l1, l2) {
    return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
  }

  // Smallest black-overlay alpha (>= floor) that makes white text pass on [r,g,b].
  // A black overlay in sRGB space scales each channel by (1 - a).
  function neededAlpha(r, g, b, floor) {
    var a = floor;
    while (a < 0.95) {
      if (contrast(TEXT, lum(r * (1 - a), g * (1 - a), b * (1 - a))) >= NEED) break;
      a += 0.02;
    }
    return a;
  }

  function tune(hero) {
    if (hero.dataset.jarvisContrast) return;
    hero.dataset.jarvisContrast = '1';
    // Auto-darkening assumes white text; dark text is handled by the black region in CSS.
    if (hero.classList.contains('jarvis-hero--text-dark')) return;
    var overlay = hero.querySelector('.jarvis-hero__overlay');
    if (!overlay) return;
    var m = (hero.style.backgroundImage || '').match(/url\(["']?(.*?)["']?\)/);
    if (!m) return;

    var floor = parseFloat(overlay.style.opacity) || 0;
    var img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = function () {
      try {
        var cv = document.createElement('canvas');
        cv.width = cv.height = 16;
        var cx = cv.getContext('2d');
        cx.drawImage(img, 0, 0, 16, 16);
        var d = cx.getImageData(0, 0, 16, 16).data;
        var r = 0, g = 0, b = 0, n = 0;
        for (var i = 0; i < d.length; i += 4) { r += d[i]; g += d[i + 1]; b += d[i + 2]; n++; }
        overlay.style.opacity = neededAlpha(r / n, g / n, b / n, floor).toFixed(2);
      } catch (e) {
        // Cross-origin image taints the canvas -> can't sample. Fail safe: force dark.
        overlay.style.opacity = Math.max(floor, 0.6).toFixed(2);
      }
    };
    img.src = m[1];
  }

  function run() {
    var heroes = document.querySelectorAll('.jarvis-hero[style*="background-image"]');
    for (var i = 0; i < heroes.length; i++) tune(heroes[i]);
  }

  if (typeof document === 'undefined') return;  // node self-check path
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();

// ponytail: node self-check for the contrast math. `node hero.js` runs it; browser skips.
if (typeof module !== 'undefined' && module.exports) {
  var chan = function (c) { c /= 255; return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); };
  var lum = function (r, g, b) { return 0.2126 * chan(r) + 0.7152 * chan(g) + 0.0722 * chan(b); };
  var contrast = function (a, b) { return (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05); };
  var need = function (r, g, b, f) { var a = f; while (a < 0.95) { if (contrast(1, lum(r * (1 - a), g * (1 - a), b * (1 - a))) >= 4.5) break; a += 0.02; } return a; };
  var assert = require('assert');
  assert.strictEqual(Math.round(contrast(1, lum(0, 0, 0))), 21);   // white on black = 21:1
  assert.ok(contrast(1, lum(255, 255, 255)) === 1);                // white on white = 1:1
  assert.ok(need(0, 0, 0, 0) === 0);                               // black image needs none
  [[255, 255, 255], [230, 225, 210], [200, 180, 150]].forEach(function (c) {
    var a = need(c[0], c[1], c[2], 0);                             // light images -> darkened until text passes
    assert.ok(a > 0);
    assert.ok(contrast(1, lum(c[0] * (1 - a), c[1] * (1 - a), c[2] * (1 - a))) >= 4.5);
  });
  assert.ok(need(230, 225, 210, 0.8) === 0.8);                     // respects user floor when already dark enough
  console.log('hero contrast self-check ok');
}
