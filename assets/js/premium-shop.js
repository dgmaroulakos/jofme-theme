(() => {
 'use strict';
 const catalog = document.querySelector('.jofme-shop');
 const controls = document.querySelectorAll('.jofme-view button');
 const allowed = ['1', '2', '3', 'list'];
 function setView(view) {
  if (!allowed.includes(view) || !catalog) return;
  catalog.dataset.view = view;
  controls.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.view === view)));
 }
 const defaultView = allowed.find(view => document.body.classList.contains(`jofme-default-view-${view}`)) || '3';
 let savedView;
 try { savedView = localStorage.getItem('jofme-catalog-view'); } catch (_) {}
 setView(allowed.includes(savedView) ? savedView : defaultView);
 controls.forEach(button => button.addEventListener('click', () => {
  setView(button.dataset.view);
  try { localStorage.setItem('jofme-catalog-view', button.dataset.view); } catch (_) {}
 }));
 const dialog = document.getElementById('jofme-preview-dialog');
 if (!dialog) return;
 const content = dialog.querySelector('.jofme-preview-content');
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
 document.addEventListener('click', async event => {
  const button = event.target.closest('.jofme-preview');
  if (!button) return;
  if (typeof dialog.showModal !== 'function') { window.location.href = button.closest('li.product').querySelector('a').href; return; }
  trigger = button;
  if (request) request.abort();
  request = new AbortController();
  content.textContent = jofmeShop.loading;
  content.setAttribute('aria-busy', 'true');
  dialog.removeAttribute('aria-labelledby');
  dialog.showModal();
  document.body.classList.add('jofme-preview-open');
  try {
   const body = new URLSearchParams({action: 'jofme_quick_view', nonce: jofmeShop.nonce, product: button.dataset.product});
   const response = await fetch(jofmeShop.url, {method: 'POST', body, signal: request.signal, credentials: 'same-origin'});
   const result = await response.json();
   if (!response.ok || !result.success) throw new Error('preview');
   content.innerHTML = result.data.html;
   dialog.setAttribute('aria-labelledby', 'jofme-preview-title');
  } catch (error) { if (error.name !== 'AbortError') content.textContent = jofmeShop.error; }
  finally { content.removeAttribute('aria-busy'); }
 });
})();
