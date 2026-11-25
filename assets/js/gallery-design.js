(() => {
 'use strict';
 const $ = window.jQuery;
 const i18n = window.jofmeGallery || {};

 document.addEventListener('click', (event) => {
  const button = event.target.closest('.jofme-quantity-step');
  if (!button) return;
  const input = button.closest('.quantity') && button.closest('.quantity').querySelector('input.qty');
  if (!input || input.disabled || input.readOnly) return;
  const before = input.value;
  try {
   Number(button.dataset.step) > 0 ? input.stepUp() : input.stepDown();
  } catch (_) {
   return;
  }
  if (input.value !== before) {
   input.dispatchEvent(new Event('input', { bubbles: true }));
   input.dispatchEvent(new Event('change', { bubbles: true }));
  }
 });

 function prefersReducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
 }

 function scrollToElement(target) {
  if (!target) return;
  if (typeof window.jofmeScrollTo === 'function') {
   const headerOffset = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--jofme-header-h')) || 0;
   const top = target.getBoundingClientRect().top + (window.scrollY || 0) - headerOffset - 8;
   window.jofmeScrollTo(top);
   return;
  }
  target.scrollIntoView({
   behavior: prefersReducedMotion() ? 'auto' : 'smooth',
   block: 'start',
  });
 }

 function setCurrentDot(gallery, targetId) {
  gallery.querySelectorAll('.jofme-stacked-gallery__dot').forEach((dot) => {
   const current = dot.dataset.target === targetId;
   if (current) {
    dot.setAttribute('aria-current', 'true');
   } else {
    dot.removeAttribute('aria-current');
   }
  });
 }

 /* ── Lightbox ─────────────────────────────────────────────── */
 let lightboxState = null;

 function ensureLightbox() {
  let dialog = document.getElementById('jofme-gallery-lightbox');
  if (dialog) return dialog;
  if (!document.body || typeof document.createElement !== 'function') return null;

  dialog = document.createElement('dialog');
  dialog.id = 'jofme-gallery-lightbox';
  dialog.setAttribute('aria-label', i18n.image || 'Product image');
  dialog.innerHTML =
   '<div class="jofme-gallery-lightbox__inner">' +
   '<button type="button" class="jofme-gallery-lightbox__close" aria-label="' +
   (i18n.close || 'Close') +
   '">×</button>' +
   '<button type="button" class="jofme-gallery-lightbox__nav jofme-gallery-lightbox__nav--prev" data-dir="-1" aria-label="' +
   (i18n.prev || 'Previous image') +
   '">‹</button>' +
   '<figure class="jofme-gallery-lightbox__figure"><img alt=""></figure>' +
   '<button type="button" class="jofme-gallery-lightbox__nav jofme-gallery-lightbox__nav--next" data-dir="1" aria-label="' +
   (i18n.next || 'Next image') +
   '">›</button>' +
   '<p class="jofme-gallery-lightbox__status" aria-live="polite"></p>' +
   '</div>';
  document.body.appendChild(dialog);

  dialog.querySelector('.jofme-gallery-lightbox__close').addEventListener('click', () => dialog.close());
  dialog.querySelectorAll('.jofme-gallery-lightbox__nav').forEach((nav) => {
   nav.addEventListener('click', () => {
    if (!lightboxState) return;
    showLightboxIndex(lightboxState.index + Number(nav.dataset.dir));
   });
  });
  dialog.addEventListener('click', (event) => {
   if (event.target === dialog) dialog.close();
  });
  dialog.addEventListener('close', () => {
   document.body.classList.remove('jofme-gallery-lightbox-open');
   lightboxState = null;
  });
  document.addEventListener('keydown', (event) => {
   if (!dialog.open || !lightboxState) return;
   if (event.key === 'ArrowLeft') {
    event.preventDefault();
    showLightboxIndex(lightboxState.index - 1);
   } else if (event.key === 'ArrowRight') {
    event.preventDefault();
    showLightboxIndex(lightboxState.index + 1);
   }
  });

  return dialog;
 }

 function showLightboxIndex(index) {
  if (!lightboxState || !lightboxState.sources.length) return;
  const total = lightboxState.sources.length;
  const next = ((index % total) + total) % total;
  lightboxState.index = next;
  const source = lightboxState.sources[next];
  const dialog = lightboxState.dialog;
  const img = dialog.querySelector('.jofme-gallery-lightbox__figure img');
  const status = dialog.querySelector('.jofme-gallery-lightbox__status');
  const prev = dialog.querySelector('.jofme-gallery-lightbox__nav--prev');
  const nextBtn = dialog.querySelector('.jofme-gallery-lightbox__nav--next');

  img.src = source.src;
  img.alt = source.alt || '';
  if (status) {
   const template = i18n.of || '%1$s of %2$s';
   status.textContent = template.replace('%1$s', String(next + 1)).replace('%2$s', String(total));
  }
  if (prev) prev.hidden = total < 2;
  if (nextBtn) nextBtn.hidden = total < 2;
 }

 function openLightbox(sources, index) {
  if (!sources.length) return;
  const dialog = ensureLightbox();
  if (!dialog || typeof dialog.showModal !== 'function') {
   if (sources[index] && sources[index].src) {
    window.location.href = sources[index].src;
   }
   return;
  }
  lightboxState = { dialog, sources, index: index || 0 };
  showLightboxIndex(lightboxState.index);
  if (!dialog.open) {
   dialog.showModal();
   document.body.classList.add('jofme-gallery-lightbox-open');
  }
 }

 function collectLightboxSources(gallery) {
  return Array.from(gallery.querySelectorAll('.jofme-gallery-zoom-link'))
   .filter((link) => {
    const item = link.closest('.jofme-stacked-gallery__item, .jofme-carousel-gallery__slide');
    return !item || !item.classList.contains('jofme-gallery-item--suppressed');
   })
   .map((link) => {
    const img = link.querySelector('img');
    return {
     src: link.getAttribute('href') || (img && img.currentSrc) || (img && img.src) || '',
     alt: (img && img.getAttribute('alt')) || '',
    };
   })
   .filter((item) => item.src);
 }

 function bindLightboxLinks(gallery) {
  if (!gallery || gallery.dataset.jofmeLightbox === '1') return;
  if (typeof gallery.addEventListener !== 'function') return;
  gallery.dataset.jofmeLightbox = '1';
  gallery.addEventListener('click', (event) => {
   const link = event.target.closest('.jofme-gallery-zoom-link');
   if (!link || !gallery.contains(link)) return;
   event.preventDefault();
   const sources = collectLightboxSources(gallery);
   const href = link.getAttribute('href') || '';
   const start = Math.max(
    0,
    sources.findIndex((item) => item.src === href || (href && item.src.endsWith(href)) || (href && href.endsWith(item.src)))
   );
   openLightbox(sources, start);
  });
 }

 function normalizeImageUrl(url) {
  if (!url) return '';
  try {
   const parsed = new URL(url, window.location.origin);
   return (parsed.pathname || '').toLowerCase();
  } catch (_) {
   return String(url).split('?')[0].split('#')[0].toLowerCase();
  }
 }

 function slideImageUrls(slide) {
  const urls = [];
  if (!slide || typeof slide.querySelector !== 'function') return urls;
  const link = slide.querySelector('a.jofme-gallery-zoom-link, a');
  const img = slide.querySelector('img');
  if (link && link.getAttribute('href')) urls.push(link.getAttribute('href'));
  if (img) {
   if (img.getAttribute('src')) urls.push(img.getAttribute('src'));
   if (img.currentSrc) urls.push(img.currentSrc);
   if (img.dataset && img.dataset.originalSrc) urls.push(img.dataset.originalSrc);
  }
  return urls.map(normalizeImageUrl).filter(Boolean);
 }

 function variationGalleryImages(variation) {
  const images = [];
  const seen = {};
  function pushImage(image) {
   if (!image) return;
   const full = image.full_src || image.src || image.url || '';
   if (!full) return;
   const key = String(image.id || image.attachment_id || normalizeImageUrl(full));
   if (seen[key]) return;
   seen[key] = true;
   images.push({
    id: image.id || image.attachment_id || '',
    src: full,
    full_src: image.full_src || full,
    width: image.full_src_w || image.width || '',
    height: image.full_src_h || image.height || '',
    alt: image.alt || '',
   });
  }

  if (variation && Array.isArray(variation.jofme_gallery)) {
   variation.jofme_gallery.forEach(pushImage);
  }
  ['variation_gallery_images', 'variation_gallery', 'gallery_images'].forEach((key) => {
   if (variation && Array.isArray(variation[key])) {
    variation[key].forEach(pushImage);
   }
  });
  if (variation && variation.image) {
   pushImage(
    Object.assign({}, variation.image, {
     id: variation.image_id || variation.image.id || '',
    })
   );
  }
  return images;
 }

 function findIndexForImage(slides, image) {
  if (!image || !Array.isArray(slides)) return -1;
  const candidates = [image.full_src, image.src]
   .map(normalizeImageUrl)
   .filter(Boolean);
  const attachmentId = image.id ? String(image.id) : '';
  return slides.findIndex((slide) => {
   if (!slide || typeof slide.querySelector !== 'function') return false;
   const img = slide.querySelector('img');
   if (attachmentId && img && img.getAttribute('data-attachment_id') === attachmentId) {
    return true;
   }
   const urls = slideImageUrls(slide);
   return urls.some((url) => candidates.some((candidate) => url === candidate || url.endsWith(candidate) || candidate.endsWith(url)));
  });
 }

 function findSlideIndexesForImages(slides, images) {
  const indexes = [];
  (images || []).forEach((image) => {
   const matched = findIndexForImage(slides, image);
   if (matched >= 0 && indexes.indexOf(matched) === -1) {
    indexes.push(matched);
   }
  });
  return indexes;
 }

 function removeVariationTempNodes(gallery) {
  if (!gallery || typeof gallery.querySelectorAll !== 'function') return;
  gallery.querySelectorAll('[data-jofme-variation-temp="1"]').forEach((el) => {
   if (el && typeof el.remove === 'function') el.remove();
  });
 }

 function canBuildDomNodes() {
  return !!(document && typeof document.createElement === 'function');
 }

 function createZoomLink(image, index) {
  if (!canBuildDomNodes()) return null;
  const full = image.full_src || image.src || '';
  const link = document.createElement('a');
  link.className = 'jofme-gallery-zoom-link';
  link.href = full;
  link.dataset = link.dataset || {};
  link.dataset.lightboxIndex = String(index);
  const img = document.createElement('img');
  img.src = full;
  img.alt = image.alt || '';
  img.decoding = 'async';
  img.loading = 'lazy';
  if (typeof img.setAttribute === 'function') {
   if (image.id) img.setAttribute('data-attachment_id', String(image.id));
   if (image.width) img.setAttribute('width', String(image.width));
   if (image.height) img.setAttribute('height', String(image.height));
  }
  if (typeof link.appendChild === 'function') link.appendChild(img);
  return link;
 }

 function createCarouselSlide(image, index) {
  if (!canBuildDomNodes()) return null;
  const figure = document.createElement('figure');
  figure.className = 'jofme-carousel-gallery__slide';
  figure.dataset = figure.dataset || {};
  figure.dataset.index = String(index);
  figure.dataset.jofmeVariationTemp = '1';
  if (typeof figure.setAttribute === 'function') figure.setAttribute('hidden', '');
  const zoom = createZoomLink(image, index);
  if (zoom && typeof figure.appendChild === 'function') figure.appendChild(zoom);
  return figure;
 }

 function createCarouselThumb(image, index) {
  if (!canBuildDomNodes()) return null;
  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'jofme-carousel-gallery__thumb';
  button.dataset = button.dataset || {};
  button.dataset.index = String(index);
  button.dataset.jofmeVariationTemp = '1';
  if (typeof button.setAttribute === 'function') {
   button.setAttribute('role', 'listitem');
   button.setAttribute('aria-label', (i18n.image || 'Product image') + ' ' + String(index + 1));
  }
  const img = document.createElement('img');
  img.src = image.full_src || image.src || '';
  img.alt = '';
  if (image.id && typeof img.setAttribute === 'function') {
   img.setAttribute('data-attachment_id', String(image.id));
  }
  if (typeof button.appendChild === 'function') button.appendChild(img);
  return button;
 }

 function ensureCarouselThumbs(gallery) {
  if (!gallery || typeof gallery.querySelector !== 'function') return null;
  let wrap = gallery.querySelector('.jofme-carousel-gallery__thumbs');
  if (wrap) return wrap;
  if (!canBuildDomNodes() || typeof gallery.appendChild !== 'function') return null;
  wrap = document.createElement('div');
  wrap.className = 'jofme-carousel-gallery__thumbs';
  if (typeof wrap.setAttribute === 'function') wrap.setAttribute('role', 'list');
  gallery.appendChild(wrap);
  return wrap;
 }

 function ensureCarouselNav(gallery) {
  if (!gallery || typeof gallery.querySelector !== 'function' || !canBuildDomNodes()) return;
  const viewport = gallery.querySelector('.jofme-carousel-gallery__viewport');
  if (!viewport || typeof viewport.appendChild !== 'function') return;
  // Append both after the track so they stack above the zoom link.
  if (!gallery.querySelector('.jofme-carousel-gallery__nav--prev')) {
   const prev = document.createElement('button');
   prev.type = 'button';
   prev.className = 'jofme-carousel-gallery__nav jofme-carousel-gallery__nav--prev';
   prev.dataset = prev.dataset || {};
   prev.dataset.dir = '-1';
   if (typeof prev.setAttribute === 'function') prev.setAttribute('aria-label', i18n.prev || 'Previous image');
   prev.textContent = '‹';
   viewport.appendChild(prev);
  }
  if (!gallery.querySelector('.jofme-carousel-gallery__nav--next')) {
   const next = document.createElement('button');
   next.type = 'button';
   next.className = 'jofme-carousel-gallery__nav jofme-carousel-gallery__nav--next';
   next.dataset = next.dataset || {};
   next.dataset.dir = '1';
   if (typeof next.setAttribute === 'function') next.setAttribute('aria-label', i18n.next || 'Next image');
   next.textContent = '›';
   viewport.appendChild(next);
  }
 }

 function createStackedItem(image, index) {
  if (!canBuildDomNodes()) return null;
  const itemId = 'jofme-gallery-variation-' + String(image.id || index + 1) + '-' + String(Date.now());
  const figure = document.createElement('figure');
  figure.className = 'jofme-stacked-gallery__item';
  figure.id = itemId;
  figure.dataset = figure.dataset || {};
  figure.dataset.index = String(index);
  figure.dataset.jofmeVariationTemp = '1';
  const zoom = createZoomLink(image, index);
  if (zoom && typeof figure.appendChild === 'function') figure.appendChild(zoom);
  return figure;
 }

 function createStackedDot(itemId, index) {
  if (!canBuildDomNodes()) return null;
  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'jofme-stacked-gallery__dot';
  button.dataset = button.dataset || {};
  button.dataset.target = itemId;
  button.dataset.jofmeVariationTemp = '1';
  if (typeof button.setAttribute === 'function') {
   button.setAttribute('aria-label', (i18n.image || 'Product image') + ' ' + String(index + 1));
  }
  return button;
 }

 function ensureStackedRail(gallery) {
  if (!gallery || typeof gallery.querySelector !== 'function') return null;
  let rail = gallery.querySelector('.jofme-stacked-gallery__rail');
  if (rail) {
   return (typeof rail.querySelector === 'function' && rail.querySelector('.jofme-stacked-gallery__dots')) || rail;
  }
  if (!canBuildDomNodes()) return null;
  if (typeof gallery.insertBefore !== 'function' && typeof gallery.appendChild !== 'function') return null;
  rail = document.createElement('div');
  rail.className = 'jofme-stacked-gallery__rail';
  rail.dataset = rail.dataset || {};
  rail.dataset.jofmeVariationTemp = '1';
  const nav = document.createElement('nav');
  nav.className = 'jofme-stacked-gallery__dots';
  if (typeof nav.setAttribute === 'function') {
   nav.setAttribute('aria-label', i18n.image || 'Product images');
  }
  if (typeof rail.appendChild === 'function') rail.appendChild(nav);
  if (typeof gallery.insertBefore === 'function') {
   gallery.insertBefore(rail, gallery.firstChild);
  } else {
   gallery.appendChild(rail);
  }
  return nav;
 }

 function stashPrimaryImage(primary) {
  if (!primary || primary.dataset.originalSrc) return;
  primary.dataset.originalSrc = primary.getAttribute('src') || '';
  primary.dataset.originalSrcset = primary.getAttribute('srcset') || '';
  primary.dataset.originalSizes = primary.getAttribute('sizes') || '';
  const link = primary.closest('a');
  if (link) {
   primary.dataset.originalHref = link.getAttribute('href') || '';
  }
 }

 function restorePrimaryImage(primary) {
  if (!primary || !primary.dataset.originalSrc) return;
  primary.setAttribute('src', primary.dataset.originalSrc);
  if (primary.dataset.originalSrcset) {
   primary.setAttribute('srcset', primary.dataset.originalSrcset);
  } else {
   primary.removeAttribute('srcset');
  }
  if (primary.dataset.originalSizes) {
   primary.setAttribute('sizes', primary.dataset.originalSizes);
  } else {
   primary.removeAttribute('sizes');
  }
  const link = primary.closest('a');
  if (link && primary.dataset.originalHref) {
   link.setAttribute('href', primary.dataset.originalHref);
  }
 }

 function applyImageToPrimary(primary, image) {
  if (!primary || !image) return;
  const full = image.full_src || image.src;
  if (!full) return;
  primary.setAttribute('src', full);
  primary.removeAttribute('srcset');
  primary.removeAttribute('sizes');
  if (image.width) primary.setAttribute('width', String(image.width));
  if (image.height) primary.setAttribute('height', String(image.height));
  if (image.alt) primary.setAttribute('alt', image.alt);
  const link = primary.closest('a');
  if (link) {
   link.setAttribute('href', image.full_src || full);
  }
 }

 function setSuppressedItems(items, visibleIndexes) {
  const visible = {};
  (visibleIndexes || []).forEach((index) => {
   visible[index] = true;
  });
  items.forEach((item, index) => {
   if (!item || !item.classList || typeof item.classList.toggle !== 'function') return;
   item.classList.toggle('jofme-gallery-item--suppressed', !visible[index]);
  });
 }

 function clearSuppressedItems(items) {
  items.forEach((item) => {
   if (!item || !item.classList || typeof item.classList.remove !== 'function') return;
   item.classList.remove('jofme-gallery-item--suppressed');
  });
 }

 function setGalleryModeClass(gallery, variationMode, multi) {
  if (!gallery || !gallery.classList) return;
  if (variationMode) {
   gallery.classList.add('is-variation-gallery');
   gallery.classList.toggle('has-variation-multi', !!multi);
  } else {
   gallery.classList.remove('is-variation-gallery', 'has-variation-multi');
  }
 }

 function bindStackedGallery(gallery) {
  if (!gallery || gallery.dataset.jofmeStacked === '1') return;
  gallery.dataset.jofmeStacked = '1';

  let items = Array.from(gallery.querySelectorAll('.jofme-stacked-gallery__item'));
  let dots = Array.from(gallery.querySelectorAll('.jofme-stacked-gallery__dot'));
  if (!items.length) return;

  const primary = gallery.querySelector('.jofme-stacked-gallery__item[data-primary] img');
  stashPrimaryImage(primary);

  function refreshStackedLists() {
   items = Array.from(gallery.querySelectorAll('.jofme-stacked-gallery__item'));
   dots = Array.from(gallery.querySelectorAll('.jofme-stacked-gallery__dot'));
   items.forEach((item, i) => {
    if (!item) return;
    item.dataset = item.dataset || {};
    item.dataset.index = String(i);
    if (typeof item.querySelector !== 'function') return;
    const link = item.querySelector('.jofme-gallery-zoom-link');
    if (link) {
     link.dataset = link.dataset || {};
     link.dataset.lightboxIndex = String(i);
    }
   });
  }

  function activateStackedDot(dot) {
   if (!dot) return;
   if (dot.classList && dot.classList.contains('jofme-gallery-item--suppressed')) return;
   const target = document.getElementById(dot.dataset.target);
   if (!target || (target.classList && target.classList.contains('jofme-gallery-item--suppressed'))) return;
   scrollToElement(target);
   setCurrentDot(gallery, target.id);
  }

  dots.forEach((dot) => {
   if (!dot || typeof dot.addEventListener !== 'function') return;
   dot.addEventListener('click', () => activateStackedDot(dot));
  });

  if (typeof gallery.addEventListener === 'function') {
   gallery.addEventListener('click', (event) => {
    if (!event || !event.target || typeof event.target.closest !== 'function') return;
    const dot = event.target.closest('.jofme-stacked-gallery__dot');
    if (!dot || (typeof gallery.contains === 'function' && !gallery.contains(dot))) return;
    activateStackedDot(dot);
   });
  }

  bindLightboxLinks(gallery);

  let stackedObserver = null;
  const ratios = new Map();
  if (typeof window.IntersectionObserver === 'function') {
   stackedObserver = new window.IntersectionObserver(
    (entries) => {
     entries.forEach((entry) => {
      ratios.set(entry.target.id, entry.intersectionRatio);
     });
     if (!items.length) return;
     let bestId = items[0].id;
     let bestRatio = -1;
     ratios.forEach((ratio, id) => {
      const item = document.getElementById(id);
      if (item && item.classList.contains('jofme-gallery-item--suppressed')) return;
      if (ratio > bestRatio) {
       bestRatio = ratio;
       bestId = id;
      }
     });
     if (bestRatio > 0) {
      setCurrentDot(gallery, bestId);
     }
    },
    {
     root: null,
     threshold: [0.2, 0.4, 0.55, 0.7],
     rootMargin: '-20% 0px -35% 0px',
    }
   );
   items.forEach((item) => stackedObserver.observe(item));
  }

  if (!$) return;

  const form = document.querySelector('form.variations_form');
  if (!form || !primary) return;

  function showParentStackedGallery() {
   removeVariationTempNodes(gallery);
   refreshStackedLists();
   clearSuppressedItems(items);
   clearSuppressedItems(dots);
   restorePrimaryImage(primary);
   scrollToElement(items[0]);
   if (items[0]) setCurrentDot(gallery, items[0].id);
   setGalleryModeClass(gallery, false);
  }

  function showVariationStackedGallery(variation) {
   removeVariationTempNodes(gallery);
   refreshStackedLists();
   restorePrimaryImage(primary);

   const images = variationGalleryImages(variation);
   if (!images.length) return;

   const visible = [];
   images.forEach((image) => {
    let idx = findIndexForImage(items, image);
    if (idx < 0 && typeof gallery.appendChild === 'function') {
     const item = createStackedItem(image, items.length);
     if (item) {
      gallery.appendChild(item);
      const dotsNav = ensureStackedRail(gallery);
      const dot = createStackedDot(item.id, items.length);
      if (dotsNav && typeof dotsNav.appendChild === 'function' && dot) {
       dotsNav.appendChild(dot);
       if (typeof dot.addEventListener === 'function') {
        dot.addEventListener('click', () => activateStackedDot(dot));
       }
      }
      if (stackedObserver && typeof stackedObserver.observe === 'function') {
       stackedObserver.observe(item);
      }
      refreshStackedLists();
      idx = items.length - 1;
     }
    }
    if (idx >= 0 && visible.indexOf(idx) === -1) visible.push(idx);
   });

   if (!visible.length) {
    applyImageToPrimary(primary, images[0]);
    setSuppressedItems(items, [0]);
    setSuppressedItems(dots, [0]);
    setGalleryModeClass(gallery, true, false);
    scrollToElement(items[0]);
    if (items[0]) setCurrentDot(gallery, items[0].id);
    return;
   }

   setSuppressedItems(items, visible);
   setSuppressedItems(
    dots,
    visible
     .map((index) => items[index] && items[index].id)
     .map((id) => dots.findIndex((dot) => dot.dataset.target === id))
     .filter((index) => index >= 0)
   );
   setGalleryModeClass(gallery, true, visible.length > 1);
   scrollToElement(items[visible[0]]);
   if (items[visible[0]]) setCurrentDot(gallery, items[visible[0]].id);
  }

  $(form).on('found_variation', (_, variation) => showVariationStackedGallery(variation));
  $(form).on('reset_data hide_variation', showParentStackedGallery);
 }

 function bindCarouselGallery(gallery) {
  if (!gallery || gallery.dataset.jofmeCarousel === '1') return;
  gallery.dataset.jofmeCarousel = '1';

  let slides = Array.from(gallery.querySelectorAll('.jofme-carousel-gallery__slide'));
  let thumbs = Array.from(gallery.querySelectorAll('.jofme-carousel-gallery__thumb'));
  if (!slides.length) return;

  let index = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
  if (index < 0) index = 0;

  const primary = gallery.querySelector('.jofme-carousel-gallery__slide[data-primary] img');
  stashPrimaryImage(primary);

  function refreshCarouselLists() {
   slides = Array.from(gallery.querySelectorAll('.jofme-carousel-gallery__slide'));
   thumbs = Array.from(gallery.querySelectorAll('.jofme-carousel-gallery__thumb'));
   slides.forEach((slide, i) => {
    if (!slide) return;
    slide.dataset = slide.dataset || {};
    slide.dataset.index = String(i);
    if (typeof slide.querySelector !== 'function') return;
    const link = slide.querySelector('.jofme-gallery-zoom-link');
    if (link) {
     link.dataset = link.dataset || {};
     link.dataset.lightboxIndex = String(i);
    }
   });
   thumbs.forEach((thumb, i) => {
    if (!thumb) return;
    thumb.dataset = thumb.dataset || {};
    thumb.dataset.index = String(i);
   });
  }

  function visibleSlideIndexes() {
   const indexes = [];
   slides.forEach((slide, i) => {
    if (!slide.classList.contains('jofme-gallery-item--suppressed')) indexes.push(i);
   });
   return indexes.length ? indexes : slides.map((_, i) => i);
  }

  function show(next) {
   const visible = visibleSlideIndexes();
   if (!visible.length) return;
   let position = visible.indexOf(index);
   if (position < 0) position = 0;
   if (typeof next === 'number' && visible.indexOf(next) >= 0) {
    index = next;
   } else if (typeof next === 'number') {
    const delta = next > index ? 1 : next < index ? -1 : 0;
    position = (position + delta + visible.length) % visible.length;
    index = visible[position];
   } else {
    index = visible[position] || visible[0];
   }
   gallery.dataset.index = String(index);
   slides.forEach((slide, i) => {
    const active = i === index;
    slide.classList.toggle('is-active', active);
    if (active) {
     slide.removeAttribute('hidden');
    } else {
     slide.setAttribute('hidden', '');
    }
   });
   thumbs.forEach((thumb, i) => {
    const active = i === index;
    thumb.classList.toggle('is-active', active);
    if (active) {
     thumb.setAttribute('aria-current', 'true');
    } else {
     thumb.removeAttribute('aria-current');
    }
   });
   const multiVisible = visible.length > 1;
   gallery.querySelectorAll('.jofme-carousel-gallery__nav').forEach((nav) => {
    nav.hidden = !multiVisible;
   });
   const thumbWrap = gallery.querySelector('.jofme-carousel-gallery__thumbs');
   if (thumbWrap) {
    thumbWrap.hidden = !multiVisible;
   }
  }

  function stepCarousel(dir) {
   const visible = visibleSlideIndexes();
   if (visible.length < 2) return;
   const position = Math.max(0, visible.indexOf(index));
   const nextPos = (position + Number(dir || 0) + visible.length) % visible.length;
   show(visible[nextPos]);
  }

  if (typeof gallery.addEventListener === 'function') {
   gallery.addEventListener('click', (event) => {
    if (!event || !event.target || typeof event.target.closest !== 'function') return;
    const nav = event.target.closest('.jofme-carousel-gallery__nav');
    if (nav && (!gallery.contains || gallery.contains(nav))) {
     event.preventDefault();
     event.stopPropagation();
     stepCarousel(Number(nav.dataset.dir || 0));
     return;
    }
    const thumb = event.target.closest('.jofme-carousel-gallery__thumb');
    if (thumb && (!gallery.contains || gallery.contains(thumb))) {
     if (thumb.classList && thumb.classList.contains('jofme-gallery-item--suppressed')) return;
     event.preventDefault();
     event.stopPropagation();
     const target = Number(thumb.dataset.index || 0);
     if (visibleSlideIndexes().indexOf(target) >= 0) {
      show(target);
     }
    }
   });

   gallery.setAttribute('tabindex', '0');
   gallery.addEventListener('keydown', (event) => {
    if (!event) return;
    if (event.key === 'ArrowLeft') {
     event.preventDefault();
     stepCarousel(-1);
    } else if (event.key === 'ArrowRight') {
     event.preventDefault();
     stepCarousel(1);
    }
   });

   let touchX = null;
   let touchY = null;
   gallery.addEventListener(
    'touchstart',
    (event) => {
     if (!event.changedTouches || !event.changedTouches[0]) return;
     touchX = event.changedTouches[0].clientX;
     touchY = event.changedTouches[0].clientY;
    },
    { passive: true }
   );
   gallery.addEventListener(
    'touchend',
    (event) => {
     if (touchX === null || !event.changedTouches || !event.changedTouches[0]) return;
     const point = event.changedTouches[0];
     const deltaX = point.clientX - touchX;
     const deltaY = point.clientY - (touchY || point.clientY);
     touchX = null;
     touchY = null;
     if (Math.abs(deltaX) < 40 || Math.abs(deltaX) < Math.abs(deltaY)) return;
     if (event.target.closest && event.target.closest('.jofme-carousel-gallery__thumb, .jofme-carousel-gallery__nav')) return;
     stepCarousel(deltaX < 0 ? 1 : -1);
    },
    { passive: true }
   );
  }

  bindLightboxLinks(gallery);
  show(index);

  if (!$) return;
  const form = document.querySelector('form.variations_form');
  if (!form || !primary) return;

  function showParentCarouselGallery() {
   removeVariationTempNodes(gallery);
   refreshCarouselLists();
   clearSuppressedItems(slides);
   clearSuppressedItems(thumbs);
   restorePrimaryImage(primary);
   setGalleryModeClass(gallery, false);
   show(0);
  }

  function showVariationCarouselGallery(variation) {
   removeVariationTempNodes(gallery);
   refreshCarouselLists();
   restorePrimaryImage(primary);

   const images = variationGalleryImages(variation);
   if (!images.length) return;

   const visible = [];
   const track = gallery.querySelector && gallery.querySelector('.jofme-carousel-gallery__track');
   images.forEach((image) => {
    let idx = findIndexForImage(slides, image);
    if (idx < 0 && track && typeof track.appendChild === 'function') {
     ensureCarouselNav(gallery);
     const thumbWrap = ensureCarouselThumbs(gallery);
     const slide = createCarouselSlide(image, slides.length);
     if (slide) {
      track.appendChild(slide);
      const thumb = createCarouselThumb(image, slides.length);
      if (thumbWrap && typeof thumbWrap.appendChild === 'function' && thumb) {
       thumbWrap.appendChild(thumb);
      }
      refreshCarouselLists();
      idx = slides.length - 1;
     }
    }
    if (idx >= 0 && visible.indexOf(idx) === -1) visible.push(idx);
   });

   if (!visible.length) {
    applyImageToPrimary(primary, images[0]);
    setSuppressedItems(slides, [0]);
    setSuppressedItems(thumbs, [0]);
    setGalleryModeClass(gallery, true, false);
    show(0);
    return;
   }

   setSuppressedItems(slides, visible);
   setSuppressedItems(thumbs, visible);
   setGalleryModeClass(gallery, true, visible.length > 1);
   show(visible[0]);
  }

  $(form).on('found_variation', (_, variation) => showVariationCarouselGallery(variation));
  $(form).on('reset_data hide_variation', showParentCarouselGallery);
 }

 document.querySelectorAll('.jofme-stacked-gallery').forEach(bindStackedGallery);
 document.querySelectorAll('.jofme-carousel-gallery').forEach(bindCarouselGallery);
})();
