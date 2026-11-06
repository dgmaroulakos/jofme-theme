(() => {
 'use strict';
 const $ = window.jQuery;
 if (!$ || !$.fn) return;

 function syncSwatches(form) {
  form.querySelectorAll('.jofme-variation-swatches').forEach((wrap) => {
   const select = wrap.querySelector('select');
   if (!select) return;
   const selected = select.value;
   wrap.querySelectorAll('.jofme-swatch[data-value]').forEach((swatch) => {
    const option = Array.from(select.options).find((item) => item.value === swatch.dataset.value);
    const enabled = option && !option.disabled && option.value !== '';
    swatch.disabled = !enabled;
    const isSelected = enabled && selected === swatch.dataset.value;
    swatch.classList.toggle('is-selected', isSelected);
    swatch.setAttribute('aria-pressed', String(isSelected));
   });
  });
 }

 function bindForm(form) {
  if (!form || form.dataset.jofmeSwatches === '1') return;
  form.dataset.jofmeSwatches = '1';

  form.addEventListener('click', (event) => {
   const swatch = event.target.closest('.jofme-variation-swatches .jofme-swatch[data-value]');
   if (!swatch || swatch.disabled) return;
   event.preventDefault();
   const wrap = swatch.closest('.jofme-variation-swatches');
   const select = wrap && wrap.querySelector('select');
   if (!select) return;
   if (select.value === swatch.dataset.value) {
    select.value = '';
   } else {
    select.value = swatch.dataset.value;
   }
   $(select).trigger('change');
   syncSwatches(form);
  });

  $(form).on('woocommerce_update_variation_values woocommerce_variation_has_changed reset_data check_variations', () => {
   syncSwatches(form);
  });

  syncSwatches(form);
 }

 function init() {
  document.querySelectorAll('form.variations_form').forEach(bindForm);
 }

 $(init);
 $(document.body).on('init_variation_form', 'form.variations_form', function () {
  bindForm(this);
 });
})();
