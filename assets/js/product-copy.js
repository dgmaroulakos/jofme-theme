(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	ready(function () {
		var cfg = window.jofmeProductCopy || {};
		document.querySelectorAll('.jofme-restock-notify').forEach(function (root) {
			var toggle = root.querySelector('.jofme-restock-notify__toggle');
			var form = root.querySelector('.jofme-restock-notify__form');
			var email = root.querySelector('.jofme-restock-notify__email');
			var status = root.querySelector('.jofme-restock-notify__status');
			var productId = root.getAttribute('data-product-id');
			if (!toggle || !form || !email || !productId) {
				return;
			}

			toggle.addEventListener('click', function () {
				var open = form.hasAttribute('hidden');
				if (open) {
					form.removeAttribute('hidden');
					toggle.setAttribute('aria-expanded', 'true');
					email.focus();
				} else {
					form.setAttribute('hidden', '');
					toggle.setAttribute('aria-expanded', 'false');
				}
			});

			form.addEventListener('submit', function (event) {
				event.preventDefault();
				var value = (email.value || '').trim();
				if (!value || value.indexOf('@') < 1) {
					if (status) {
						status.hidden = false;
						status.textContent = cfg.invalid || 'Please enter a valid email.';
					}
					return;
				}

				var body = new FormData();
				body.append('action', 'jofme_restock_notify');
				body.append('nonce', cfg.nonce || '');
				body.append('email', value);
				body.append('product_id', productId);

				fetch(cfg.url || '/wp-admin/admin-ajax.php', {
					method: 'POST',
					credentials: 'same-origin',
					body: body,
				})
					.then(function (res) {
						return res.json().then(function (data) {
							return { ok: res.ok && data && data.success, data: data };
						});
					})
					.then(function (result) {
						if (!status) {
							return;
						}
						status.hidden = false;
						if (result.ok) {
							status.textContent =
								(result.data && result.data.data && result.data.data.message) ||
								cfg.success ||
								'Thanks';
							var submitBtn = form.querySelector('.jofme-restock-notify__submit');
							if (submitBtn) {
								submitBtn.setAttribute('disabled', '');
							}
							email.setAttribute('disabled', '');
						} else {
							status.textContent =
								(result.data && result.data.data && result.data.data.message) ||
								cfg.error ||
								'Error';
						}
					})
					.catch(function () {
						if (status) {
							status.hidden = false;
							status.textContent = cfg.error || 'Error';
						}
					});
			});
		});
	});
})();
