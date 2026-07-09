/**
 * @file
 * Auto-darkens the background-card overlay until light text passes WCAG AA.
 * Same contrast math as hero.js (verified by that file's node self-check).
 * SDC auto-attaches this sibling file as the card component library.
 */
(function () {
  'use strict';

  var NEED = 4.5;   // WCAG AA, normal text
  var TEXT = 1;     // relative luminance of #fff text

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
  function neededAlpha(r, g, b, floor) {
    var a = floor;
    while (a < 0.95) {
      if (contrast(TEXT, lum(r * (1 - a), g * (1 - a), b * (1 - a))) >= NEED) break;
      a += 0.02;
    }
    return a;
  }

  function tune(card) {
    if (card.dataset.jarvisContrast) return;
    card.dataset.jarvisContrast = '1';
    // Auto-darkening assumes light text; dark text is handled by the black region in CSS.
    if (card.classList.contains('jarvis-card--text-dark')) return;
    var overlay = card.querySelector('.jarvis-card__overlay');
    if (!overlay) return;
    var m = (card.style.backgroundImage || '').match(/url\(["']?(.*?)["']?\)/);
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
    var cards = document.querySelectorAll('.jarvis-card--background[style*="background-image"]');
    for (var i = 0; i < cards.length; i++) tune(cards[i]);
  }

  if (typeof document === 'undefined') return;
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();
