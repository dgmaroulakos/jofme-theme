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
 }

 function bindRoot(root) {
  if (!root || root.dataset.jofmeBestsellersBound === '1') return;
  root.dataset.jofmeBestsellersBound = '1';

  const tabs = Array.from(root.querySelectorAll('[role="tab"]'));
  if (!tabs.length) return;

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
