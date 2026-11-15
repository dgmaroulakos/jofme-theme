(() => {
 'use strict';

 const rendered = new WeakSet();

 function workerSrc() {
  if (window.jofmeSizeGuide && jofmeSizeGuide.worker) {
   return jofmeSizeGuide.worker;
  }
  return 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
 }

 async function renderPdf(dialog) {
  if (!dialog || rendered.has(dialog)) return;
  const host = dialog.querySelector('.jofme-size-guide-dialog__pages');
  const url = host && host.getAttribute('data-pdf-url');
  if (!host || !url || !window.pdfjsLib) return;

  rendered.add(dialog);
  host.innerHTML = '';
  host.setAttribute('aria-busy', 'true');

  try {
   pdfjsLib.GlobalWorkerOptions.workerSrc = workerSrc();
   const pdf = await pdfjsLib.getDocument({ url, withCredentials: false }).promise;
   const maxWidth = Math.min(host.clientWidth || 860, 900);

   for (let pageNum = 1; pageNum <= pdf.numPages; pageNum += 1) {
    const page = await pdf.getPage(pageNum);
    const unscaled = page.getViewport({ scale: 1 });
    const scale = maxWidth / unscaled.width;
    const viewport = page.getViewport({ scale: Math.max(scale, 0.75) });
    const canvas = document.createElement('canvas');
    canvas.className = 'jofme-size-guide-dialog__page';
    canvas.width = Math.floor(viewport.width);
    canvas.height = Math.floor(viewport.height);
    canvas.setAttribute('role', 'img');
    canvas.setAttribute('aria-label', 'Page ' + pageNum);
    const context = canvas.getContext('2d', { alpha: false });
    await page.render({ canvasContext: context, viewport }).promise;
    host.appendChild(canvas);
   }
  } catch (_) {
   host.innerHTML = '';
   const link = document.createElement('a');
   link.className = 'jofme-size-guide-dialog__fallback';
   link.href = url;
   link.target = '_blank';
   link.rel = 'noopener noreferrer';
   link.textContent =
    (window.jofmeSizeGuide && jofmeSizeGuide.openPdf) || 'Open PDF';
   host.appendChild(link);
  } finally {
   host.removeAttribute('aria-busy');
  }
 }

 document.addEventListener('click', (event) => {
  const trigger = event.target.closest('.jofme-size-guide-trigger');
  if (trigger) {
   const id = trigger.getAttribute('data-guide');
   const dialog = id && document.getElementById(id);
   if (dialog && typeof dialog.showModal === 'function') {
    dialog.showModal();
    renderPdf(dialog);
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
