(() => {
	'use strict';

	document.querySelectorAll('.jofme-swatch-picker').forEach((wrap) => {
		const text = wrap.querySelector('[data-swatch-hex]');
		const picker = wrap.querySelector('[data-swatch-picker]');
		if (!text || !picker) {
			return;
		}
		picker.addEventListener('input', () => {
			text.value = picker.value;
		});
		text.addEventListener('input', () => {
			if (/^#[0-9A-Fa-f]{6}$/.test(text.value)) {
				picker.value = text.value;
			}
		});
	});

	function clearImage(field) {
		const input = field.querySelector('input[name="jofme_swatch_image"]');
		const preview = field.querySelector('.jofme-swatch-image-preview');
		const img = preview ? preview.querySelector('img') : null;
		if (input) {
			input.value = '0';
		}
		if (img) {
			img.removeAttribute('src');
		}
		if (preview) {
			preview.hidden = true;
		}
	}

	document.addEventListener('click', (event) => {
		const button = event.target.closest('.jofme-swatch-image-select, .jofme-swatch-image-remove');
		if (!button) {
			return;
		}
		const field = button.closest('.jofme-swatch-image-field');
		if (!field) {
			return;
		}
		if (button.classList.contains('jofme-swatch-image-remove')) {
			clearImage(field);
			return;
		}
		if (typeof wp === 'undefined' || !wp.media) {
			return;
		}
		const title = (window.jofmeSwatch && jofmeSwatch.title) || 'Choose swatch image';
		const buttonText = (window.jofmeSwatch && jofmeSwatch.button) || 'Use as swatch';
		const frame = wp.media({
			title: title,
			button: { text: buttonText },
			library: { type: 'image' },
			multiple: false,
		});
		frame.on('select', () => {
			const image = frame.state().get('selection').first().toJSON();
			const input = field.querySelector('input[name="jofme_swatch_image"]');
			const preview = field.querySelector('.jofme-swatch-image-preview');
			const img = preview ? preview.querySelector('img') : null;
			if (input) {
				input.value = String(image.id);
			}
			if (img) {
				img.src = image.sizes && image.sizes.thumbnail ? image.sizes.thumbnail.url : image.url;
			}
			if (preview) {
				preview.hidden = false;
			}
		});
		frame.open();
	});
})();
