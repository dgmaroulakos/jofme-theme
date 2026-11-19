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

 function formatCount(count) {
  const i18n = config.i18n || {};
  const template = count === 1 ? i18n.item || '%d item' : i18n.items || '%d items';
  return template.replace('%d', String(count));
 }

 function syncCounts(ids) {
  const count = ids.length;
  document.documentElement.style.setProperty('--jofme-wishlist-count', String(count));
  document.querySelectorAll('[data-jofme-wishlist-count]').forEach((el) => {
   el.textContent = String(count);
   el.classList.toggle('is-empty', count < 1);
  });
  document.querySelectorAll('[data-jofme-wishlist-page-count]').forEach((el) => {
   if (count < 1) {
    el.hidden = true;
    el.textContent = '';
    return;
   }
   el.hidden = false;
   el.textContent = formatCount(count);
  });
 }

 function syncAll(scope) {
  const ids = readIds();
  (scope || document).querySelectorAll('[data-jofme-wishlist]').forEach((button) => syncButton(button, ids));
  syncCounts(ids);
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

 function setPageState(root, state) {
  if (!root) return;
  const status = root.querySelector('[data-jofme-wishlist-status]');
  const empty = root.querySelector('[data-jofme-wishlist-empty]');
  const grid = root.querySelector('[data-jofme-wishlist-grid]');
  if (status) {
   status.hidden = state !== 'loading' && state !== 'error';
   if (state === 'loading') {
    status.textContent = (config.i18n && config.i18n.loading) || 'Loading your wishlist…';
   }
   if (state === 'error') {
    status.textContent = (config.i18n && config.i18n.error) || 'Unable to load your wishlist. Please try again.';
   }
  }
  if (empty) empty.hidden = state !== 'empty';
  if (grid) grid.hidden = state !== 'ready';
 }

 function removeCardForProduct(root, productId) {
  if (!root || !productId) return;
  root.querySelectorAll('.product, li.product').forEach((card) => {
   const button = card.querySelector('[data-jofme-wishlist][data-product-id="' + productId + '"]');
   if (button) card.remove();
  });
  const grid = root.querySelector('[data-jofme-wishlist-grid]');
  const remaining = grid ? grid.querySelectorAll('.product, li.product').length : 0;
  if (remaining < 1) {
   if (grid) grid.innerHTML = '';
   setPageState(root, 'empty');
  }
 }

 async function loadWishlistPage(root) {
  if (!root || root.dataset.loaded === '1') return;
  const ids = readIds();
  syncCounts(ids);

  if (!ids.length) {
   setPageState(root, 'empty');
   root.dataset.loaded = '1';
   return;
  }

  if (!config.ajaxUrl || !config.nonce) {
   setPageState(root, 'error');
   return;
  }

  setPageState(root, 'loading');

  try {
   const body = new URLSearchParams();
   body.set('action', 'jofme_wishlist_products');
   body.set('nonce', config.nonce);
   body.set('ids', JSON.stringify(ids));

   const response = await fetch(config.ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body: body.toString(),
   });
   const payload = await response.json();
   if (!payload || !payload.success) {
    throw new Error('wishlist_ajax_failed');
   }

   const html = (payload.data && payload.data.html) || '';
   const grid = root.querySelector('[data-jofme-wishlist-grid]');
   if (!html || !grid) {
    setPageState(root, 'empty');
    root.dataset.loaded = '1';
    return;
   }

   grid.innerHTML = html;
   setPageState(root, 'ready');
   syncAll(root);
   root.dataset.loaded = '1';
  } catch (_) {
   setPageState(root, 'error');
  }
 }

 function initPages(scope) {
  (scope || document).querySelectorAll('[data-jofme-wishlist-page]').forEach((root) => {
   loadWishlistPage(root);
  });
 }

 document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-jofme-wishlist]');
  if (!button) return;
  event.preventDefault();
  event.stopPropagation();
  const productId = Number(button.dataset.productId || 0);
  if (!productId) return;
  const ids = toggle(productId);
  const page = button.closest('[data-jofme-wishlist-page]');
  if (page && ids.indexOf(productId) < 0) {
   removeCardForProduct(page, productId);
  }
 });

 syncAll();
 initPages();

 if (window.elementorFrontend && window.elementorFrontend.hooks) {
  window.elementorFrontend.hooks.addAction('frontend/element_ready/global', ($el) => {
   const node = $el[0] || $el;
   syncAll(node);
   initPages(node);
  });
 }

 document.addEventListener('elementor/popup/show', () => {
  syncAll();
  initPages();
 });
})();
