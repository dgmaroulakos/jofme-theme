(() => {
 'use strict';
 const $ = window.jQuery;

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
    target.scrollIntoView({
     behavior: prefersReducedMotion() ? 'auto' : 'smooth',
     block: 'start',
    });
    setCurrentDot(gallery, target.id);
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
   items[0].scrollIntoView({
    behavior: prefersReducedMotion() ? 'auto' : 'smooth',
    block: 'start',
   });
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
