(() => {
 'use strict';

 function activateTab(root, nextTab) {
  if (!root || !nextTab) return;
  const tabs = Array.from(root.querySelectorAll('[role="tab"]'));
  const panels = Array.from(root.querySelectorAll('[role="tabpanel"]'));
  const nextId = nextTab.getAttribute('aria-controls');

  tabs.forEach((tab) => {
   const selected = tab === nextTab;
   tab.setAttribute('aria-selected', selected ? 'true' : 'false');
   tab.tabIndex = selected ? 0 : -1;
  });

  panels.forEach((panel) => {
   if (panel.id === nextId) {
    panel.removeAttribute('hidden');
   } else {
    panel.setAttribute('hidden', '');
   }
  });

  const activePanel = root.querySelector('[role="tabpanel"]:not([hidden])');
  if (activePanel) {
   activePanel.querySelectorAll('[data-jofme-bestsellers-carousel]').forEach(updateCarouselNav);
  }
 }

 function updateCarouselNav(carousel) {
  if (!carousel) return;
  const track = carousel.querySelector('.jofme-bestsellers__track');
  const prev = carousel.querySelector('.jofme-bestsellers__nav--prev');
  const next = carousel.querySelector('.jofme-bestsellers__nav--next');
  if (!track) return;
  const max = Math.max(0, track.scrollWidth - track.clientWidth - 2);
  const left = track.scrollLeft;
  if (prev) prev.disabled = left <= 2;
  if (next) next.disabled = left >= max;
 }

 function scrollCarousel(carousel, dir) {
  const track = carousel.querySelector('.jofme-bestsellers__track');
  if (!track) return;
  const slide = track.querySelector('.jofme-bestsellers__slide');
  const amount = slide ? slide.getBoundingClientRect().width + 16 : Math.max(200, track.clientWidth * 0.8);
  track.scrollBy({
   left: dir * amount,
   behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
  });
 }

 function bindCarousel(carousel) {
  if (!carousel || carousel.dataset.jofmeCarouselBound === '1') return;
  carousel.dataset.jofmeCarouselBound = '1';
  const track = carousel.querySelector('.jofme-bestsellers__track');
  if (!track) return;

  carousel.addEventListener('click', (event) => {
   const nav = event.target.closest('.jofme-bestsellers__nav');
   if (!nav || !carousel.contains(nav) || nav.disabled) return;
   event.preventDefault();
   scrollCarousel(carousel, Number(nav.dataset.dir || 0));
  });

  track.addEventListener('scroll', () => updateCarouselNav(carousel), { passive: true });
  window.addEventListener('resize', () => updateCarouselNav(carousel));
  updateCarouselNav(carousel);
 }

 function bindRoot(root) {
  if (!root || root.dataset.jofmeBestsellersBound === '1') return;
  root.dataset.jofmeBestsellersBound = '1';

  const tabs = Array.from(root.querySelectorAll('[role="tab"]'));
  if (tabs.length) {
   root.addEventListener('click', (event) => {
    const tab = event.target.closest('[role="tab"]');
    if (!tab || !root.contains(tab)) return;
    activateTab(root, tab);
   });

   root.addEventListener('keydown', (event) => {
    const tab = event.target.closest('[role="tab"]');
    if (!tab || !root.contains(tab)) return;
    const current = tabs.indexOf(tab);
    if (current < 0) return;

    let next = -1;
    if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
     next = (current + 1) % tabs.length;
    } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
     next = (current - 1 + tabs.length) % tabs.length;
    } else if (event.key === 'Home') {
     next = 0;
    } else if (event.key === 'End') {
     next = tabs.length - 1;
    }
    if (next < 0) return;
    event.preventDefault();
    tabs[next].focus();
    activateTab(root, tabs[next]);
   });
  }

  root.querySelectorAll('[data-jofme-bestsellers-carousel]').forEach(bindCarousel);
 }

 function init(scope) {
  (scope || document).querySelectorAll('[data-jofme-bestsellers]').forEach(bindRoot);
 }

 init();

 if (window.elementorFrontend && window.elementorFrontend.hooks) {
  window.elementorFrontend.hooks.addAction('frontend/element_ready/jofme-bestsellers-tabs.default', ($el) => {
   init($el[0] || $el);
  });
 }

 document.addEventListener('elementor/popup/show', () => init());
})();
