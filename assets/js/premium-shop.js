(() => {
 'use strict';
 const catalog = document.querySelector('.jofme-shop');
 const allowed = ['1', '2', '3', '4', 'list'];
 const mobileOnly = ['1', '2'];
 const shopConfig = (typeof window !== 'undefined' && window.jofmeShop) ? window.jofmeShop : {};
 const isMobileCatalog = () => typeof window !== 'undefined' && typeof window.matchMedia === 'function' && window.matchMedia('(max-width: 767px)').matches;
 function viewButtons(root) {
  const scope = root && typeof root.querySelectorAll === 'function' ? root : document;
  if (!scope || typeof scope.querySelectorAll !== 'function') return [];
  return scope.querySelectorAll('.jofme-view button, .jofme-view__btn');
 }
 function normalizeView(view) {
  if (!allowed.includes(view)) return '2';
  if (isMobileCatalog() && !mobileOnly.includes(view)) return '2';
  return view;
 }
 function setView(view) {
  view = normalizeView(view);
  const buttons = viewButtons();
  if (!catalog || !buttons.length) return;
  catalog.dataset.view = view;
  buttons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.view === view)));
 }
 const defaultView = allowed.find(view => document.body.classList.contains(`jofme-default-view-${view}`)) || '3';
 let savedView;
 try { savedView = localStorage.getItem('jofme-catalog-view'); } catch (_) {}
 setView(allowed.includes(savedView) ? savedView : defaultView);
 document.addEventListener('click', (event) => {
  const button = event.target.closest('.jofme-view button, .jofme-view__btn');
  if (!button || (catalog && !catalog.contains(button) && !button.closest('.jofme-catalog-results'))) return;
  const view = normalizeView(button.dataset.view);
  setView(view);
  try { localStorage.setItem('jofme-catalog-view', view); } catch (_) {}
 });
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
  if (!block || typeof block.querySelector !== 'function') return;
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

 /* Dual-range price slider (updates hidden min_price / max_price). */
 function formatPriceLabel(amount, currency, step) {
  const decimals = step < 1 ? 2 : 0;
  const number = Number(amount).toFixed(decimals);
  return currency ? String(currency) + number : number;
 }

 function syncPriceSlider(root) {
  if (!root) return;
  const minInput = root.querySelector('.jofme-price-slider__input--min');
  const maxInput = root.querySelector('.jofme-price-slider__input--max');
  const minHidden = root.querySelector('[data-jofme-price-min]');
  const maxHidden = root.querySelector('[data-jofme-price-max]');
  const minLabel = root.querySelector('[data-jofme-price-min-label]');
  const maxLabel = root.querySelector('[data-jofme-price-max-label]');
  const range = root.querySelector('[data-jofme-price-range]');
  if (!minInput || !maxInput) return;

  const floor = Number(root.dataset.min || minInput.min || 0);
  const ceil = Number(root.dataset.max || maxInput.max || 0);
  const step = Number(root.dataset.step || minInput.step || 1);
  const currency = root.dataset.currency || '';
  let minVal = Number(minInput.value);
  let maxVal = Number(maxInput.value);
  if (minVal > maxVal) {
   if (document.activeElement === minInput) maxVal = minVal;
   else minVal = maxVal;
   minInput.value = String(minVal);
   maxInput.value = String(maxVal);
  }
  if (minHidden) minHidden.value = Math.abs(minVal - floor) > 0.0001 ? String(minVal) : '';
  if (maxHidden) maxHidden.value = Math.abs(maxVal - ceil) > 0.0001 ? String(maxVal) : '';
  if (minLabel) minLabel.textContent = formatPriceLabel(minVal, currency, step);
  if (maxLabel) maxLabel.textContent = formatPriceLabel(maxVal, currency, step);
  if (range && ceil > floor) {
   const left = ((minVal - floor) / (ceil - floor)) * 100;
   const right = ((ceil - maxVal) / (ceil - floor)) * 100;
   range.style.left = left + '%';
   range.style.right = right + '%';
  }
 }

 function commitPriceSlider(root) {
  syncPriceSlider(root);
  const form = root && root.closest('form');
  if (!form) return;
  if (typeof form.requestSubmit === 'function') {
   form.requestSubmit();
   return;
  }
  form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
 }

 function initPriceSliders(scope) {
  (scope || document).querySelectorAll('[data-jofme-price-slider]').forEach((root) => {
   if (!root || root.dataset.ready === '1') return;
   root.dataset.ready = '1';
   const inputs = root.querySelectorAll('.jofme-price-slider__input');
   inputs.forEach((input) => {
    input.addEventListener('input', () => syncPriceSlider(root));
    input.addEventListener('change', () => commitPriceSlider(root));
   });
   syncPriceSlider(root);
  });
 }
 initPriceSliders();
 document.addEventListener('jofme:catalogupdated', () => initPriceSliders());

 /* AJAX catalog filters / sorting / pagination — fetch HTML, swap fragments. */
 if (shopConfig.ajaxFilters !== false) {
  let filterRequest;
  let filterTimer;
  const parser = typeof DOMParser !== 'undefined' ? new DOMParser() : null;

  function catalogResults() {
   return document.querySelector('[data-jofme-catalog-results], #jofme-catalog-results');
  }

  function filtersRoot() {
   return document.querySelector('[data-jofme-ajax-filters], .jofme-filters');
  }

  function buildFilterUrl(form) {
   const action = form.getAttribute('action') || window.location.href;
   const url = new URL(action, window.location.origin);
   const grouped = new Map();
   new FormData(form).forEach((value, key) => {
    if (value === '' || value == null) return;
    const name = String(key).replace(/\[\]$/, '');
    if (!grouped.has(name)) grouped.set(name, []);
    grouped.get(name).push(String(value));
   });
   grouped.forEach((values, name) => {
    if (name.indexOf('filter_') === 0 || name === 'jofme_collection') {
     url.searchParams.set(name, values.join(','));
    } else {
     url.searchParams.set(name, values[values.length - 1]);
    }
   });
   url.searchParams.delete('paged');
   url.searchParams.delete('product-page');
   return url.toString();
  }

  function captureOpenPanels(root) {
   const open = {};
   if (!root) return open;
   root.querySelectorAll('details.jofme-filter-panel[data-filter-key]').forEach((panel) => {
    open[panel.getAttribute('data-filter-key')] = panel.open;
   });
   return open;
  }

  function restoreOpenPanels(root, open) {
   if (!root || !open) return;
   root.querySelectorAll('details.jofme-filter-panel[data-filter-key]').forEach((panel) => {
    const key = panel.getAttribute('data-filter-key');
    if (Object.prototype.hasOwnProperty.call(open, key)) {
     panel.open = !!open[key];
    }
   });
  }

  function closeMobileFilters() {
   if (!window.matchMedia('(max-width: 959px)').matches) return;
   const toggle = document.querySelector('.jofme-shop-sidebar-toggle[aria-expanded="true"]');
   if (toggle) toggle.click();
  }

  function setLoading(isLoading) {
   const shop = document.querySelector('.jofme-shop');
   const results = catalogResults();
   if (shop) shop.classList.toggle('is-ajax-loading', isLoading);
   if (results) {
    results.classList.toggle('is-ajax-loading', isLoading);
    results.setAttribute('aria-busy', isLoading ? 'true' : 'false');
   }
  }

  async function loadCatalog(url, options) {
   const opts = options || {};
   if (!parser || !url) return;
   if (filterRequest) filterRequest.abort();
   const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
   filterRequest = controller;
   setLoading(true);
   const openPanels = captureOpenPanels(filtersRoot());
   try {
    const response = await fetch(url, {
     method: 'GET',
     credentials: 'same-origin',
     headers: {
      Accept: 'text/html',
      'X-Requested-With': 'XMLHttpRequest',
     },
     signal: controller ? controller.signal : undefined,
    });
    if (!response.ok) throw new Error('catalog_http_' + response.status);
    const html = await response.text();
    const doc = parser.parseFromString(html, 'text/html');
    const nextResults = doc.querySelector('[data-jofme-catalog-results], #jofme-catalog-results');
    const currResults = catalogResults();
    if (nextResults && currResults) {
     currResults.replaceWith(document.importNode(nextResults, true));
    }
    const nextFilters = doc.querySelector('[data-jofme-ajax-filters], .jofme-filters');
    const currFilters = filtersRoot();
    if (nextFilters && currFilters) {
     currFilters.replaceWith(document.importNode(nextFilters, true));
     restoreOpenPanels(filtersRoot(), openPanels);
    }
    if (opts.push !== false && window.history && window.history.pushState) {
     window.history.pushState({ jofmeCatalog: 1 }, '', url);
    }
    setView((catalog && catalog.dataset.view) || defaultView);
    document.dispatchEvent(new CustomEvent('jofme:catalogupdated', { detail: { url } }));
    if (opts.closeDrawer) closeMobileFilters();
    const anchor = document.getElementById('jofme-catalog-products') || catalogResults();
    if (opts.scroll !== false && anchor && typeof anchor.scrollIntoView === 'function') {
     try { anchor.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch (_) { anchor.scrollIntoView(true); }
    }
   } catch (error) {
    if (error && error.name === 'AbortError') return;
    if (opts.fallback !== false) {
     window.location.href = url;
     return;
    }
   } finally {
    if (filterRequest === controller) filterRequest = null;
    setLoading(false);
   }
  }

  function scheduleFilterLoad(url, options) {
   clearTimeout(filterTimer);
   filterTimer = setTimeout(() => loadCatalog(url, options), 120);
  }

  document.addEventListener('submit', (event) => {
   const form = event.target.closest('[data-jofme-ajax-filters-form], .jofme-filters__form');
   if (!form) return;
   event.preventDefault();
   scheduleFilterLoad(buildFilterUrl(form), { closeDrawer: true });
  });

  document.addEventListener('change', (event) => {
   const checkbox = event.target.closest('.jofme-filter-panel__checkbox');
   if (!checkbox) return;
   const form = checkbox.closest('[data-jofme-ajax-filters-form], .jofme-filters__form');
   if (!form) return;
   scheduleFilterLoad(buildFilterUrl(form), { closeDrawer: false, scroll: false });
  });

  document.addEventListener('click', (event) => {
   const clear = event.target.closest('.jofme-filter-actions a[href]');
   if (clear) {
    event.preventDefault();
    scheduleFilterLoad(clear.href, { closeDrawer: true });
    return;
   }
   const chip = event.target.closest('.jofme-active-filters a[href]');
   if (chip) {
    event.preventDefault();
    scheduleFilterLoad(chip.href, { closeDrawer: false, scroll: false });
    return;
   }
   const pageLink = event.target.closest('.woocommerce-pagination a[href]');
   const results = catalogResults();
   if (pageLink && ((results && results.contains(pageLink)) || (catalog && catalog.contains(pageLink)))) {
    event.preventDefault();
    scheduleFilterLoad(pageLink.href, { closeDrawer: false });
   }
  });

  document.addEventListener('change', (event) => {
   const select = event.target.closest('.woocommerce-ordering select');
   if (!select) return;
   const form = select.closest('form.woocommerce-ordering');
   if (!form) return;
   event.preventDefault();
   event.stopPropagation();
   const url = new URL(window.location.href);
   url.searchParams.set('orderby', select.value);
   url.searchParams.delete('paged');
   url.searchParams.delete('product-page');
   scheduleFilterLoad(url.toString(), { closeDrawer: false });
  }, true);

  document.addEventListener('submit', (event) => {
   const form = event.target.closest('form.woocommerce-ordering');
   if (!form) return;
   event.preventDefault();
   const select = form.querySelector('select[name="orderby"]') || form.querySelector('select');
   const url = new URL(window.location.href);
   if (select) url.searchParams.set('orderby', select.value);
   url.searchParams.delete('paged');
   url.searchParams.delete('product-page');
   scheduleFilterLoad(url.toString(), { closeDrawer: false });
  });

  window.addEventListener('popstate', () => {
   if (!catalogResults()) return;
   loadCatalog(window.location.href, { push: false, closeDrawer: false, fallback: false, scroll: false });
  });
 }

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
   // Drop add-to-cart so WC_Form_Handler does not also add on the same request.
   data.delete('add-to-cart');
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
