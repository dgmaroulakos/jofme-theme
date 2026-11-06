(() => {
 'use strict';
 function update(image) {
  if (!(image instanceof HTMLImageElement) || !image.classList.contains('jofme-hover-image')) return;
  const ready = image.complete && image.naturalWidth > 0;
  image.classList.toggle('is-ready', ready);
 }
 function refresh() {
  document.querySelectorAll('.jofme-hover-image').forEach(update);
 }
 refresh();
 document.addEventListener('load', (event) => update(event.target), true);
 document.addEventListener('error', (event) => update(event.target), true);
 // Lazy-loaded gallery images often decode after first paint / scroll.
 window.addEventListener('load', refresh);
 document.addEventListener('scroll', refresh, { passive: true, once: true });
})();
