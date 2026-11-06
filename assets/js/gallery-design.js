(() => {
 'use strict';
 document.addEventListener('click', event => {
  const button = event.target.closest('.jofme-quantity-step');
  if (!button) return;
  const input = button.closest('.quantity').querySelector('input.qty');
  if (!input || input.disabled || input.readOnly) return;
  const before = input.value;
  try { Number(button.dataset.step) > 0 ? input.stepUp() : input.stepDown(); } catch (_) { return; }
  if (input.value !== before) { input.dispatchEvent(new Event('input', {bubbles:true})); input.dispatchEvent(new Event('change', {bubbles:true})); }
 });
 function prepareThumbs() {
  document.querySelectorAll('.woocommerce-product-gallery .flex-control-thumbs img').forEach((image,index) => {
   image.tabIndex = 0; image.setAttribute('role','button');
   if (!image.getAttribute('aria-label')) image.setAttribute('aria-label', `${jofmeGallery.image} ${index+1}`);
   image.setAttribute('aria-pressed', String(image.classList.contains('flex-active')));
  });
 }
 document.addEventListener('keydown', event => {
  if (event.target.matches('.flex-control-thumbs img') && ['Enter',' '].includes(event.key)) { event.preventDefault(); event.target.click(); }
 });
 const observed = new WeakSet();
 function watchGalleries() {
  document.querySelectorAll('.woocommerce-product-gallery').forEach(gallery => {
   if (observed.has(gallery)) return;
   observed.add(gallery);
   new MutationObserver(prepareThumbs).observe(gallery, {childList:true,subtree:true,attributes:true,attributeFilter:['class']});
  });
  prepareThumbs();
 }
 // WooCommerce can replace the complete gallery when selecting a variation.
 if (window.jQuery) window.jQuery(document).on('wc-product-gallery-after-init', '.woocommerce-product-gallery', watchGalleries);
 watchGalleries();
})();
