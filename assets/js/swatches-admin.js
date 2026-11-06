(() => {
 'use strict';
 document.querySelectorAll('.jofme-swatch-picker').forEach((wrap) => {
  const text = wrap.querySelector('[data-swatch-hex]');
  const picker = wrap.querySelector('[data-swatch-picker]');
  if (!text || !picker) return;
  picker.addEventListener('input', () => {
   text.value = picker.value;
  });
  text.addEventListener('input', () => {
   if (/^#[0-9A-Fa-f]{6}$/.test(text.value)) {
    picker.value = text.value;
   }
  });
 });
})();
