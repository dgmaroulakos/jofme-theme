(() => {
 'use strict';
 const $ = window.jQuery;
 if (!$ || !window.wp || !wp.media) return;

 function setPreview(field, id, url, name) {
  field.find('#jofme_size_guide_pdf_id').val(id || 0);
  const preview = field.find('.jofme-size-guide-pdf-preview');
  const link = field.find('.jofme-size-guide-pdf-link');
  if (id && url) {
   link.attr('href', url).text(name || url.split('/').pop());
   preview.prop('hidden', false);
  } else {
   link.attr('href', '#').text('');
   preview.prop('hidden', true);
  }
 }

 $(document).on('click', '.jofme-size-guide-pdf-select', (event) => {
  event.preventDefault();
  const field = $(event.currentTarget).closest('.jofme-size-guide-pdf-field');
  const frame = wp.media({
   title: (window.jofmeSizeGuideAdmin && jofmeSizeGuideAdmin.title) || 'PDF',
   button: { text: (window.jofmeSizeGuideAdmin && jofmeSizeGuideAdmin.button) || 'Use' },
   library: { type: 'application/pdf' },
   multiple: false,
  });
  frame.on('select', () => {
   const file = frame.state().get('selection').first().toJSON();
   setPreview(field, file.id, file.url, file.filename || file.title);
  });
  frame.open();
 });

 $(document).on('click', '.jofme-size-guide-pdf-remove', (event) => {
  event.preventDefault();
  setPreview($(event.currentTarget).closest('.jofme-size-guide-pdf-field'), 0, '', '');
 });
})();
