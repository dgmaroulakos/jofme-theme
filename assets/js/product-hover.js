(() => {
 'use strict';
 function update(image) {
  if (!image.matches('.jofme-hover-image')) return;
  image.classList.toggle('is-ready', image.complete && image.naturalWidth > 0);
 }
 document.querySelectorAll('.jofme-hover-image').forEach(update);
 document.addEventListener('load', event => {
  if (event.target instanceof HTMLImageElement) update(event.target);
 }, true);
 document.addEventListener('error', event => {
  if (event.target instanceof HTMLImageElement) update(event.target);
 }, true);
})();
