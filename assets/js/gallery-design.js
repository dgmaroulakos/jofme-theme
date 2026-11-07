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
  return Array.from(gallery.querySelectorAll('.jofme-stacked-gallery__link')).map((link) => {
   const img = link.querySelector('img');
   return {
    src: link.getAttribute('href') || (img && img.currentSrc) || (img && img.src) || '',
    alt: (img && img.getAttribute('alt')) || '',
   };
  }).filter((item) => item.src);
 }

 function bindStackedGallery(gallery) {
  if (!gallery || gallery.dataset.jofmeStacked === '1') return;
  gallery.dataset.jofmeStacked = '1';

  const items = Array.from(gallery.querySelectorAll('.jofme-stacked-gallery__item'));
  const dots = Array.from(gallery.querySelectorAll('.jofme-stacked-gallery__dot'));
  if (!items.length) return;

  const primary = gallery.querySelector('.jofme-stacked-gallery__item[data-primary] img');
  if (primary) {
   primary.dataset.originalSrc = primary.getAttribute('src') || '';
   primary.dataset.originalSrcset = primary.getAttribute('srcset') || '';
   primary.dataset.originalSizes = primary.getAttribute('sizes') || '';
   const link = primary.closest('a');
   if (link) {
    primary.dataset.originalHref = link.getAttribute('href') || '';
   }
  }

  dots.forEach((dot) => {
   dot.addEventListener('click', () => {
    const target = document.getElementById(dot.dataset.target);
    if (!target) return;
    scrollToElement(target);
    setCurrentDot(gallery, target.id);
   });
  });

  gallery.querySelectorAll('.jofme-stacked-gallery__link').forEach((link) => {
   link.addEventListener('click', (event) => {
    event.preventDefault();
    const sources = collectLightboxSources(gallery);
    const index = Number(link.dataset.lightboxIndex);
    const start = Number.isFinite(index) ? index : Math.max(0, sources.findIndex((item) => item.src === link.href));
    openLightbox(sources, start < 0 ? 0 : start);
   });
  });

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

  function applyVariationImage(variation) {
   if (!variation || !variation.image || !variation.image.src) return;
   primary.setAttribute('src', variation.image.src);
   if (variation.image.srcset) {
    primary.setAttribute('srcset', variation.image.srcset);
   } else {
    primary.removeAttribute('srcset');
   }
   if (variation.image.sizes) {
    primary.setAttribute('sizes', variation.image.sizes);
   }
   const link = primary.closest('a');
   if (link && variation.image.full_src) {
    link.setAttribute('href', variation.image.full_src);
   }
   scrollToElement(items[0]);
   setCurrentDot(gallery, items[0].id);
  }

  function resetPrimaryImage() {
   if (!primary.dataset.originalSrc) return;
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

  $(form).on('found_variation', (_, variation) => applyVariationImage(variation));
  $(form).on('reset_data hide_variation', resetPrimaryImage);
 }

 document.querySelectorAll('.jofme-stacked-gallery').forEach(bindStackedGallery);
})();
