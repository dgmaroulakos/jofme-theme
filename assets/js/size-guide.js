(() => {
 'use strict';

 document.addEventListener('click', (event) => {
  const trigger = event.target.closest('.jofme-size-guide-trigger');
  if (trigger) {
   const id = trigger.getAttribute('data-guide');
   const dialog = id && document.getElementById(id);
   if (dialog && typeof dialog.showModal === 'function') {
    dialog.showModal();
   }
   return;
  }

  const closer = event.target.closest('[data-close]');
  if (closer) {
   const dialog = closer.closest('dialog');
   if (dialog && typeof dialog.close === 'function') {
    dialog.close();
   }
  }
 });

 document.addEventListener('click', (event) => {
  const dialog = event.target;
  if (!(dialog instanceof HTMLDialogElement)) return;
  if (!dialog.classList.contains('jofme-size-guide-dialog')) return;
  const rect = dialog.querySelector('.jofme-size-guide-dialog__inner');
  if (!rect) return;
  const box = rect.getBoundingClientRect();
  const inside =
   event.clientX >= box.left &&
   event.clientX <= box.right &&
   event.clientY >= box.top &&
   event.clientY <= box.bottom;
  if (!inside && typeof dialog.close === 'function') {
   dialog.close();
  }
 });
})();
