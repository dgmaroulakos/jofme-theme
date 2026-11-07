(() => {
 'use strict';
 const catalog = document.querySelector('.jofme-shop');
 const controls = document.querySelectorAll('.jofme-view button');
 const allowed = ['1', '2', '3', '4', 'list'];
 const mobileOnly = ['1', '2'];
 const isMobileCatalog = () => typeof window !== 'undefined' && typeof window.matchMedia === 'function' && window.matchMedia('(max-width: 767px)').matches;
 function normalizeView(view) {
  if (!allowed.includes(view)) return '2';
  if (isMobileCatalog() && !mobileOnly.includes(view)) return '2';
  return view;
 }
 function setView(view) {
  view = normalizeView(view);
  if (!catalog || !controls.length) return;
  catalog.dataset.view = view;
  controls.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.view === view)));
 }
 const defaultView = allowed.find(view => document.body.classList.contains(`jofme-default-view-${view}`)) || '3';
 let savedView;
 try { savedView = localStorage.getItem('jofme-catalog-view'); } catch (_) {}
 setView(allowed.includes(savedView) ? savedView : defaultView);
 controls.forEach(button => button.addEventListener('click', () => {
  const view = normalizeView(button.dataset.view);
  setView(view);
  try { localStorage.setItem('jofme-catalog-view', view); } catch (_) {}
 }));
 if (typeof window !== 'undefined' && typeof window.addEventListener === 'function') {
  window.addEventListener('resize', () => {
   if (!catalog) return;
   setView(catalog.dataset.view || defaultView);
  });
 }

 /* Shop sidebar off-canvas (mobile / tablet). */
 const panel = document.querySelector('.jofme-shop-sidebar-panel');
 const toggle = document.querySelector('.jofme-shop-sidebar-toggle');
 if (panel && toggle && document.body.classList.contains('jofme-shop-sidebar-offcanvas')) {
  const layout = panel.parentElement;
  const placeholder = document.createComment('jofme-shop-sidebar');
  const backdrop = document.createElement('div');
  backdrop.className = 'jofme-shop-sidebar-backdrop';
  backdrop.hidden = true;
  document.body.appendChild(backdrop);
  let closeTimer;
  const mobile = () => window.matchMedia('(max-width: 959px)').matches;
  const focusable = () => [...panel.querySelectorAll('button, a[href], input, select, textarea, [tabindex]:not([tabindex="-1"])')].filter((el) => {
   if (!el.getClientRects().length || el.disabled) return false;
   // Ignore Woo's nested overlay controls — filters are expanded inline.
   if (el.classList.contains('wc-block-product-filters__open-overlay') || el.classList.contains('wc-block-product-filters__close-overlay')) return false;
   return true;
  });
  function expandWidgetFilters() {
   // Force Woo Product Filters widgets to render inline inside our panel
   // instead of requiring a nested "Filter products" overlay click.
   panel.querySelectorAll('.wc-block-product-filters').forEach((block) => {
    block.classList.add('is-filter-drawer-disabled', 'is-overlay-opened', 'jofme-filters-inline');
   });
   panel.querySelectorAll('details').forEach((details) => {
    details.open = true;
   });
  }
  expandWidgetFilters();
  let lockedScrollY = 0;
  function lockPageScroll() {
   lockedScrollY = window.scrollY || window.pageYOffset || 0;
   document.documentElement.classList.add('jofme-sidebar-open');
   document.body.classList.add('jofme-sidebar-open');
   document.body.style.position = 'fixed';
   document.body.style.top = `-${lockedScrollY}px`;
   document.body.style.left = '0';
   document.body.style.right = '0';
   document.body.style.width = '100%';
  }
  function unlockPageScroll() {
   document.documentElement.classList.remove('jofme-sidebar-open');
   document.body.classList.remove('jofme-sidebar-open');
   document.body.style.position = '';
   document.body.style.top = '';
   document.body.style.left = '';
   document.body.style.right = '';
   document.body.style.width = '';
   window.scrollTo(0, lockedScrollY);
  }
  function detach() {
   if (panel.classList.contains('is-detached')) return;
   layout.insertBefore(placeholder, panel);
   document.body.appendChild(panel);
   panel.classList.add('is-detached');
  }
  function restore() {
   if (!panel.classList.contains('is-detached') || !placeholder.parentNode) return;
   placeholder.parentNode.insertBefore(panel, placeholder);
   placeholder.remove();
   panel.classList.remove('is-detached');
  }
  function closeSidebar() {
   clearTimeout(closeTimer);
   unlockPageScroll();
   toggle.setAttribute('aria-expanded', 'false');
   backdrop.hidden = true;
   closeTimer = setTimeout(() => {
    if (!mobile()) restore();
    else if (!document.body.classList.contains('jofme-sidebar-open')) {
     // Keep detached while mobile so fixed positioning stays outside sticky ancestors.
    }
    toggle.focus();
   }, 280);
  }
  function openSidebar() {
   clearTimeout(closeTimer);
   if (!mobile()) return;
   detach();
   expandWidgetFilters();
   backdrop.hidden = false;
   lockPageScroll();
   toggle.setAttribute('aria-expanded', 'true');
   const first = focusable()[0] || panel.querySelector('.jofme-shop-sidebar-close');
   if (first) first.focus();
  }
  toggle.addEventListener('click', () => {
   if (toggle.getAttribute('aria-expanded') === 'true') closeSidebar();
   else openSidebar();
  });
  const closeButton = panel.querySelector('.jofme-shop-sidebar-close');
  if (closeButton) closeButton.addEventListener('click', closeSidebar);
  backdrop.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', (event) => {
   if (event.key === 'Escape' && document.body.classList.contains('jofme-sidebar-open')) {
    event.preventDefault();
    closeSidebar();
   }
  });
  panel.addEventListener('keydown', (event) => {
   if (event.key !== 'Tab' || !document.body.classList.contains('jofme-sidebar-open')) return;
   const items = focusable();
   if (!items.length) return;
   const first = items[0];
   const last = items[items.length - 1];
   if (event.shiftKey && document.activeElement === first) {
    event.preventDefault();
    last.focus();
   } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault();
    first.focus();
   }
  });
  window.addEventListener('resize', () => {
   if (!mobile()) {
    panel.querySelectorAll('.wc-block-product-filters').forEach((block) => {
     block.classList.remove('is-overlay-opened', 'jofme-filters-inline');
    });
    if (document.body.classList.contains('jofme-sidebar-open')) {
     clearTimeout(closeTimer);
     unlockPageScroll();
     toggle.setAttribute('aria-expanded', 'false');
     backdrop.hidden = true;
     restore();
    }
   }
  });
 }

 const dialog = document.getElementById('jofme-preview-dialog');
 if (!dialog) return;
 const content = dialog.querySelector('.jofme-preview-content');
 let trigger, request;
 dialog.querySelector('.jofme-preview-close').addEventListener('click', () => dialog.close());
 dialog.addEventListener('click', event => {
  if (event.target !== dialog) return;
  const bounds = dialog.getBoundingClientRect();
  if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
 });
 dialog.addEventListener('close', () => {
  if (request) request.abort();
  document.body.classList.remove('jofme-preview-open');
  if (trigger && trigger.isConnected) trigger.focus();
 });
 document.addEventListener('click', async event => {
  const button = event.target.closest('.jofme-preview');
  if (!button) return;
  if (typeof dialog.showModal !== 'function') { window.location.href = button.closest('li.product').querySelector('a').href; return; }
  trigger = button;
  if (request) request.abort();
  request = new AbortController();
  content.textContent = jofmeShop.loading;
  content.setAttribute('aria-busy', 'true');
  dialog.removeAttribute('aria-labelledby');
  dialog.showModal();
  document.body.classList.add('jofme-preview-open');
  try {
   const body = new URLSearchParams({action: 'jofme_quick_view', nonce: jofmeShop.nonce, product: button.dataset.product});
   const response = await fetch(jofmeShop.url, {method: 'POST', body, signal: request.signal, credentials: 'same-origin'});
   const result = await response.json();
   if (!response.ok || !result.success) throw new Error('preview');
   content.innerHTML = result.data.html;
   dialog.setAttribute('aria-labelledby', 'jofme-preview-title');
  } catch (error) { if (error.name !== 'AbortError') content.textContent = jofmeShop.error; }
  finally { content.removeAttribute('aria-busy'); }
 });
})();
