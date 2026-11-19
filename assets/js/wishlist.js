(() => {
 'use strict';

 const config = window.jofmeWishlist || {};
 const storageKey = config.storageKey || 'jofme_wishlist_ids';

 function readIds() {
  try {
   const raw = window.localStorage.getItem(storageKey);
   const parsed = raw ? JSON.parse(raw) : [];
   if (!Array.isArray(parsed)) return [];
   return parsed.map((id) => Number(id)).filter((id) => id > 0);
  } catch (_) {
   return [];
  }
 }

 function writeIds(ids) {
  try {
   window.localStorage.setItem(storageKey, JSON.stringify(ids));
  } catch (_) {
   /* ignore quota / private mode */
  }
 }

 function syncButton(button, ids) {
  if (!button) return;
  const productId = Number(button.dataset.productId || 0);
  if (!productId) return;
  const active = ids.indexOf(productId) >= 0;
  button.classList.toggle('is-active', active);
  button.setAttribute('aria-pressed', active ? 'true' : 'false');
  const label = active
   ? button.dataset.labelRemove || (config.i18n && config.i18n.remove) || 'Remove from wishlist'
   : button.dataset.labelAdd || (config.i18n && config.i18n.add) || 'Add to wishlist';
  button.setAttribute('aria-label', label);
 }

 function syncAll(scope) {
  const ids = readIds();
  (scope || document).querySelectorAll('[data-jofme-wishlist]').forEach((button) => syncButton(button, ids));
  document.documentElement.style.setProperty('--jofme-wishlist-count', String(ids.length));
  document.dispatchEvent(
   new CustomEvent('jofme:wishlistchange', {
    detail: { ids },
   })
  );
 }

 function toggle(productId) {
  const ids = readIds();
  const index = ids.indexOf(productId);
  if (index >= 0) {
   ids.splice(index, 1);
  } else {
   ids.push(productId);
  }
  writeIds(ids);
  syncAll();
  return ids;
 }

 document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-jofme-wishlist]');
  if (!button) return;
  event.preventDefault();
  event.stopPropagation();
  const productId = Number(button.dataset.productId || 0);
  if (!productId) return;
  toggle(productId);
 });

 syncAll();

 if (window.elementorFrontend && window.elementorFrontend.hooks) {
  window.elementorFrontend.hooks.addAction('frontend/element_ready/global', ($el) => {
   syncAll($el[0] || $el);
  });
 }

 document.addEventListener('elementor/popup/show', () => syncAll());
})();
