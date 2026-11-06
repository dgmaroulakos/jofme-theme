(() => {
 'use strict';
 function clear(field) {
  field.querySelector('input[name="jofme_category_cover_id"]').value = '0';
  field.querySelector('img').removeAttribute('src');
  field.querySelector('.jofme-category-cover-preview').hidden = true;
 }
 document.addEventListener('click', event => {
  const button = event.target.closest('.jofme-cover-select, .jofme-cover-remove');
  if (!button) return;
  const field = button.closest('.jofme-category-cover-field');
  if (button.classList.contains('jofme-cover-remove')) { clear(field); return; }
  const frame = wp.media({title: jofmeCover.title, button: {text: jofmeCover.button}, library: {type:'image'}, multiple:false});
  frame.on('select', () => {
   const image = frame.state().get('selection').first().toJSON();
   field.querySelector('input[name="jofme_category_cover_id"]').value = image.id;
   field.querySelector('img').src = image.sizes && image.sizes.medium ? image.sizes.medium.url : image.url;
   field.querySelector('.jofme-category-cover-preview').hidden = false;
  });
  frame.open();
 });
 document.addEventListener('reset', event => {
  const field = event.target.querySelector('.jofme-category-cover-field');
  if (field) setTimeout(() => clear(field), 0);
 });
})();
