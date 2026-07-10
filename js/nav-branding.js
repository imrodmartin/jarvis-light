/**
 * @file
 * Canvas renders the primary-menu blocks (menu + branding) as one blob with
 * UUID keys, so the Twig split by block id in page.html.twig never matches and
 * the logo ships inside the offcanvas drawer — invisible on mobile until the
 * hamburger is tapped. Move the logo link into .jarvis-branding-wrap so it is
 * always visible; the menu stays in the drawer.
 */
(function () {
  'use strict';

  var logo = document.querySelector('.jarvis-primary-nav .offcanvas-body a[rel="home"]');
  var wrap = document.querySelector('.jarvis-branding-wrap');
  if (logo && wrap) wrap.appendChild(logo);
})();
