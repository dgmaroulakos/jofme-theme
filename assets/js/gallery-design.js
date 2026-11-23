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
  gallery.querySelectorAll('.jofme-gallery-zoom-link').forEach((link) => {
   link.addEventListener('click', (event) => {
    event.preventDefault();
    const sources = collectLightboxSources(gallery);
    const index = Number(link.dataset.lightboxIndex);
    const start = Number.isFinite(index) ? index : Math.max(0, sources.findIndex((item) => item.src === link.href));
    openLightbox(sources, start < 0 ? 0 : start);
   });
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
  const link = slide.querySelector('a.jofme-gallery-zoom-link, a');
  const img = slide.querySelector('img');
  if (link && link.getAttribute('href')) urls.push(link.getAttribute('href'));
  if (img) {
   if (img.getAttribute('src')) urls.push(img.getAttribute('src'));
   if (img.currentSrc) urls.push(img.currentSrc);
   if (img.dataset.originalSrc) urls.push(img.dataset.originalSrc);
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
  if (variation && variation.image) {
   pushImage(
    Object.assign({}, variation.image, {
     id: variation.image_id || variation.image.id || '',
    })
   );
  }
  return images;
 }

 function findSlideIndexesForImages(slides, images) {
  const indexes = [];
  images.forEach((image) => {
   const candidates = [image.full_src, image.src]
    .map(normalizeImageUrl)
    .filter(Boolean);
   const attachmentId = image.id ? String(image.id) : '';
   const matched = slides.findIndex((slide) => {
    const img = slide.querySelector('img');
    if (attachmentId && img && img.getAttribute('data-attachment_id') === attachmentId) {
     return true;
    }
    const urls = slideImageUrls(slide);
    return urls.some((url) => candidates.some((candidate) => url === candidate || url.endsWith(candidate) || candidate.endsWith(url)));
   });
   if (matched >= 0 && indexes.indexOf(matched) === -1) {
    indexes.push(matched);
   }
  });
  return indexes;
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
   item.classList.toggle('jofme-gallery-item--suppressed', !visible[index]);
  });
 }

 function clearSuppressedItems(items) {
  items.forEach((item) => item.classList.remove('jofme-gallery-item--suppressed'));
 }

 function bindStackedGallery(gallery) {
  if (!gallery || gallery.dataset.jofmeStacked === '1') return;
  gallery.dataset.jofmeStacked = '1';

  const items = Array.from(gallery.querySelectorAll('.jofme-stacked-gallery__item'));
  const dots = Array.from(gallery.querySelectorAll('.jofme-stacked-gallery__dot'));
  if (!items.length) return;

  const primary = gallery.querySelector('.jofme-stacked-gallery__item[data-primary] img');
  stashPrimaryImage(primary);

  dots.forEach((dot) => {
   dot.addEventListener('click', () => {
    const target = document.getElementById(dot.dataset.target);
    if (!target || target.classList.contains('jofme-gallery-item--suppressed')) return;
    scrollToElement(target);
    setCurrentDot(gallery, target.id);
   });
  });

  bindLightboxLinks(gallery);

  if (typeof window.IntersectionObserver === 'function' && items.length > 1) {
   const ratios = new Map();
   const observer = new window.IntersectionObserver(
    (entries) => {
     entries.forEach((entry) => {
      ratios.set(entry.target.id, entry.intersectionRatio);
     });
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
   items.forEach((item) => observer.observe(item));
  }

  if (!$) return;

  const form = document.querySelector('form.variations_form');
  if (!form || !primary) return;

  function showParentStackedGallery() {
   clearSuppressedItems(items);
   clearSuppressedItems(dots);
   restorePrimaryImage(primary);
   scrollToElement(items[0]);
   if (items[0]) setCurrentDot(gallery, items[0].id);
   gallery.classList.remove('is-variation-gallery', 'has-variation-multi');
  }

  function showVariationStackedGallery(variation) {
   const images = variationGalleryImages(variation);
   if (!images.length) return;

   const matched = findSlideIndexesForImages(items, images);
   let visible = matched;
   if (matched.length) {
    restorePrimaryImage(primary);
   } else {
    applyImageToPrimary(primary, images[0]);
    visible = [0];
   }

   setSuppressedItems(items, visible);
   setSuppressedItems(
    dots,
    visible
     .map((index) => items[index] && items[index].id)
     .map((id) => dots.findIndex((dot) => dot.dataset.target === id))
     .filter((index) => index >= 0)
   );
   gallery.classList.add('is-variation-gallery');
   gallery.classList.toggle('has-variation-multi', visible.length > 1);
   scrollToElement(items[visible[0]]);
   if (items[visible[0]]) setCurrentDot(gallery, items[visible[0]].id);
  }

  $(form).on('found_variation', (_, variation) => showVariationStackedGallery(variation));
  $(form).on('reset_data hide_variation', showParentStackedGallery);
 }

 function bindCarouselGallery(gallery) {
  if (!gallery || gallery.dataset.jofmeCarousel === '1') return;
  gallery.dataset.jofmeCarousel = '1';

  const slides = Array.from(gallery.querySelectorAll('.jofme-carousel-gallery__slide'));
  const thumbs = Array.from(gallery.querySelectorAll('.jofme-carousel-gallery__thumb'));
  if (!slides.length) return;

  let index = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
  if (index < 0) index = 0;

  const primary = gallery.querySelector('.jofme-carousel-gallery__slide[data-primary] img');
  stashPrimaryImage(primary);

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

  gallery.addEventListener('click', (event) => {
   const nav = event.target.closest('.jofme-carousel-gallery__nav');
   if (nav && gallery.contains(nav)) {
    event.preventDefault();
    event.stopPropagation();
    const visible = visibleSlideIndexes();
    const position = Math.max(0, visible.indexOf(index));
    const nextPos = (position + Number(nav.dataset.dir || 0) + visible.length) % visible.length;
    show(visible[nextPos]);
    return;
   }
   const thumb = event.target.closest('.jofme-carousel-gallery__thumb');
   if (thumb && gallery.contains(thumb)) {
    if (thumb.classList.contains('jofme-gallery-item--suppressed')) return;
    event.preventDefault();
    event.stopPropagation();
    show(Number(thumb.dataset.index || 0));
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
    if (event.target.closest('.jofme-carousel-gallery__thumb, .jofme-carousel-gallery__nav')) return;
    const visible = visibleSlideIndexes();
    const position = Math.max(0, visible.indexOf(index));
    const nextPos = (position + (deltaX < 0 ? 1 : -1) + visible.length) % visible.length;
    show(visible[nextPos]);
   },
   { passive: true }
  );

  bindLightboxLinks(gallery);
  show(index);

  if (!$) return;
  const form = document.querySelector('form.variations_form');
  if (!form || !primary) return;

  function showParentCarouselGallery() {
   clearSuppressedItems(slides);
   clearSuppressedItems(thumbs);
   restorePrimaryImage(primary);
   gallery.classList.remove('is-variation-gallery');
   show(0);
  }

  function showVariationCarouselGallery(variation) {
   const images = variationGalleryImages(variation);
   if (!images.length) return;

   const matched = findSlideIndexesForImages(slides, images);
   if (matched.length) {
    restorePrimaryImage(primary);
    setSuppressedItems(slides, matched);
    setSuppressedItems(thumbs, matched);
    gallery.classList.add('is-variation-gallery');
    show(matched[0]);
    return;
   }

   applyImageToPrimary(primary, images[0]);
   setSuppressedItems(slides, [0]);
   setSuppressedItems(thumbs, [0]);
   gallery.classList.add('is-variation-gallery');
   show(0);
  }

  $(form).on('found_variation', (_, variation) => showVariationCarouselGallery(variation));
  $(form).on('reset_data hide_variation', showParentCarouselGallery);
 }

 document.querySelectorAll('.jofme-stacked-gallery').forEach(bindStackedGallery);
 document.querySelectorAll('.jofme-carousel-gallery').forEach(bindCarouselGallery);
})();
