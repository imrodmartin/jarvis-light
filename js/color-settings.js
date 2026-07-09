/**
 * Two-way sync between each hex textfield and its native color picker on the
 * theme settings form. The color input doubles as the live sample swatch.
 */
((Drupal, once) => {
  Drupal.behaviors.jarvisColorSettings = {
    attach(context) {
      once('jarvis-color', '.jarvis-color-hex', context).forEach((hex) => {
        const wrap = hex.closest('.form-item') || hex.parentNode;
        const pick = wrap.querySelector('.jarvis-color-pick');
        if (!pick) return;
        // Picker -> hex box.
        pick.addEventListener('input', () => { hex.value = pick.value; });
        // Hex box -> picker/swatch, only once it's a full 6-digit hex.
        hex.addEventListener('input', () => {
          if (/^#[0-9a-fA-F]{6}$/.test(hex.value)) pick.value = hex.value;
        });
      });
    },
  };
})(Drupal, once);
