(() => {
 'use strict';
 function update(image) {
  if (!(image instanceof HTMLImageElement) || !image.matches('.jofme-hover-image')) return;
  image.classList.toggle('is-ready', image.complete && image.naturalWidth > 0);
 }
 function refresh() {
  document.querySelectorAll('.jofme-hover-image').forEach(update);
 }
 refresh();
 document.addEventListener('load', (event) => update(event.target), true);
 document.addEventListener('error', (event) => update(event.target), true);
 // Lazy-loaded gallery images often decode after first paint / scroll.
 if (typeof window !== 'undefined' && window.addEventListener) {
  window.addEventListener('load', refresh);
 }
 document.addEventListener('scroll', refresh, { passive: true, once: true });
})();
