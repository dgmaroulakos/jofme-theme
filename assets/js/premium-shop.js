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

 /* Category/term description: expand beyond 20 words on mobile. */
 document.querySelectorAll('.jofme-term-description').forEach((block) => {
  const button = block.querySelector('.jofme-term-description__more');
  const label = button && button.querySelector('.jofme-term-description__more-label');
  if (!button || !label) return;
  button.addEventListener('click', () => {
   const open = !block.classList.contains('is-expanded');
   block.classList.toggle('is-expanded', open);
   button.setAttribute('aria-expanded', String(open));
   label.textContent = open ? (jofmeShop.less || 'Less') : (jofmeShop.more || 'More');
  });
 });

 const dialog = document.getElementById('jofme-preview-dialog');
 if (!dialog) return;
 const content = dialog.querySelector('.jofme-preview-content');
 const $ = window.jQuery;
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

 document.addEventListener('click', event => {
  const step = event.target.closest('#jofme-preview-dialog .jofme-quantity-step');
  if (!step) return;
  const input = step.closest('.quantity') && step.closest('.quantity').querySelector('input.qty');
  if (!input || input.disabled || input.readOnly) return;
  const before = input.value;
  try {
   Number(step.dataset.step) > 0 ? input.stepUp() : input.stepDown();
  } catch (_) {
   return;
  }
  if (input.value !== before) {
   input.dispatchEvent(new Event('input', { bubbles: true }));
   input.dispatchEvent(new Event('change', { bubbles: true }));
  }
 });

 function applyMainImage(img, data) {
  if (!img || !data || !data.src) return;
  img.setAttribute('src', data.src);
  if (data.srcset) {
   img.setAttribute('srcset', data.srcset);
  } else {
   img.removeAttribute('srcset');
  }
  if (data.sizes) {
   img.setAttribute('sizes', data.sizes);
  } else {
   img.removeAttribute('sizes');
  }
  if (data.attachment) {
   img.dataset.attachment_id = String(data.attachment);
  }
 }

 function bindPreviewGallery(root) {
  const gallery = root.querySelector('.jofme-preview-gallery');
  if (!gallery) return;
  const main = gallery.querySelector('.jofme-preview-gallery__main .wp-post-image');
  if (!main) return;

  main.dataset.originalSrc = main.getAttribute('src') || '';
  main.dataset.originalSrcset = main.getAttribute('srcset') || '';
  main.dataset.originalSizes = main.getAttribute('sizes') || '';

  gallery.querySelectorAll('.jofme-preview-gallery__thumb').forEach(thumb => {
   thumb.addEventListener('click', () => {
    applyMainImage(main, {
     src: thumb.dataset.src,
     srcset: thumb.dataset.srcset,
     sizes: thumb.dataset.sizes,
     attachment: thumb.dataset.attachment,
    });
    gallery.querySelectorAll('.jofme-preview-gallery__thumb').forEach(item => {
     const active = item === thumb;
     item.classList.toggle('is-active', active);
     if (active) item.setAttribute('aria-current', 'true');
     else item.removeAttribute('aria-current');
    });
   });
  });

  const form = root.querySelector('form.variations_form');
  if (!$ || !form) return;

  $(form).on('found_variation.jofmePreview', (_, variation) => {
   if (!variation || !variation.image || !variation.image.src) return;
   applyMainImage(main, {
    src: variation.image.src,
    srcset: variation.image.srcset || '',
    sizes: variation.image.sizes || '',
    attachment: variation.image_id || '',
   });
  });
  $(form).on('reset_data.jofmePreview hide_variation.jofmePreview', () => {
   applyMainImage(main, {
    src: main.dataset.originalSrc,
    srcset: main.dataset.originalSrcset,
    sizes: main.dataset.originalSizes,
   });
  });
 }

 function ajaxUrl(endpoint) {
  if (window.wc_add_to_cart_params && wc_add_to_cart_params.wc_ajax_url) {
   return wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', endpoint);
  }
  return '/?wc-ajax=' + endpoint;
 }

 function bindPreviewCart(root) {
  const form = root.querySelector('form.cart');
  if (!form || form.dataset.jofmeAjaxCart === '1') return;
  form.dataset.jofmeAjaxCart = '1';

  form.addEventListener('submit', async event => {
   event.preventDefault();
   const submit = form.querySelector('[type="submit"]');
   if (submit && submit.classList.contains('disabled')) return;

   const data = new FormData(form);
   if (!data.get('product_id') && data.get('add-to-cart')) {
    data.set('product_id', data.get('add-to-cart'));
   }
   if (!data.get('quantity')) {
    data.set('quantity', '1');
   }

   if (submit) {
    submit.disabled = true;
    submit.classList.add('loading');
   }

   try {
    const response = await fetch(ajaxUrl('add_to_cart'), {
     method: 'POST',
     body: data,
     credentials: 'same-origin',
    });
    const result = await response.json();
    if (result && result.error && result.product_url) {
     window.location = result.product_url;
     return;
    }
    if (!response.ok || (result && result.error)) {
     throw new Error('cart');
    }
    if ($ && result && result.fragments) {
     $.each(result.fragments, (selector, html) => {
      $(selector).replaceWith(html);
     });
     $(document.body).trigger('added_to_cart', [result.fragments, result.cart_hash, $(submit)]);
     $(document.body).trigger('wc_fragment_refresh');
    }
    if (submit) {
     submit.classList.remove('loading');
     submit.classList.add('added');
     const label = jofmeShop.added || 'Added to cart';
     if (!submit.dataset.originalText) {
      submit.dataset.originalText = submit.textContent;
     }
     submit.textContent = label;
     window.setTimeout(() => {
      submit.disabled = false;
      submit.textContent = submit.dataset.originalText || submit.textContent;
      submit.classList.remove('added');
     }, 1800);
    }
   } catch (_) {
    form.submit();
   } finally {
    if (submit && !submit.classList.contains('added')) {
     submit.disabled = false;
     submit.classList.remove('loading');
    }
   }
  });
 }

 function initPreviewProduct(root) {
  if (!root) return;
  if ($ && $.fn.wc_variation_form) {
   $(root).find('form.variations_form').each(function () {
    const $form = $(this);
    if ($form.data('product_variations') === 'null') {
     $form.data('product_variations', []);
    }
    $form.wc_variation_form();
    $form.trigger('check_variations');
   });
  }
  bindPreviewGallery(root);
  bindPreviewCart(root);
 }

 document.addEventListener('click', async event => {
  const button = event.target.closest('.jofme-preview');
  if (!button) return;
  event.preventDefault();
  if (typeof dialog.showModal !== 'function') {
   const link = button.closest('li.product') && button.closest('li.product').querySelector('a');
   if (link) window.location.href = link.href;
   return;
  }
  trigger = button;
  if (request) request.abort();
  request = new AbortController();
  content.textContent = jofmeShop.loading;
  content.setAttribute('aria-busy', 'true');
  dialog.removeAttribute('aria-labelledby');
  dialog.showModal();
  document.body.classList.add('jofme-preview-open');
  try {
   const body = new URLSearchParams({ action: 'jofme_quick_view', nonce: jofmeShop.nonce, product: button.dataset.product });
   const response = await fetch(jofmeShop.url, { method: 'POST', body, signal: request.signal, credentials: 'same-origin' });
   const result = await response.json();
   if (!response.ok || !result.success) throw new Error('preview');
   content.innerHTML = result.data.html;
   dialog.setAttribute('aria-labelledby', 'jofme-preview-title');
   initPreviewProduct(content);
  } catch (error) {
   if (error.name !== 'AbortError') content.textContent = jofmeShop.error;
  } finally {
   content.removeAttribute('aria-busy');
  }
 });
})();
