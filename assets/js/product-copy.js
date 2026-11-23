(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	function setBusy(root, busy) {
		var submit = root.querySelector('.jofme-restock-notify__submit');
		var label = root.querySelector('.jofme-restock-notify__submit-label');
		var busyLabel = root.querySelector('.jofme-restock-notify__submit-busy');
		var email = root.querySelector('.jofme-restock-notify__email');
		if (submit) {
			submit.disabled = !!busy;
			submit.setAttribute('aria-busy', busy ? 'true' : 'false');
		}
		if (label) {
			label.hidden = !!busy;
		}
		if (busyLabel) {
			busyLabel.hidden = !busy;
		}
		if (email) {
			email.disabled = !!busy;
		}
	}

	function showError(root, message) {
		var status = root.querySelector('.jofme-restock-notify__status');
		var email = root.querySelector('.jofme-restock-notify__email');
		if (status) {
			status.hidden = false;
			status.textContent = message;
			status.classList.add('is-error');
			status.classList.remove('is-success');
		}
		if (email) {
			email.classList.add('is-invalid');
			email.focus();
		}
	}

	function showSuccess(root, message) {
		var form = root.querySelector('.jofme-restock-notify__form');
		var done = root.querySelector('.jofme-restock-notify__done');
		var doneText = root.querySelector('.jofme-restock-notify__done-text');
		var title = root.querySelector('.jofme-restock-notify__title');
		root.classList.add('is-success');
		if (form) {
			form.hidden = true;
		}
		if (title) {
			title.hidden = true;
		}
		if (done) {
			done.hidden = false;
		}
		if (doneText) {
			doneText.textContent = message;
		}
	}

	ready(function () {
		var cfg = window.jofmeProductCopy || {};
		document.querySelectorAll('.jofme-restock-notify').forEach(function (root) {
			var form = root.querySelector('.jofme-restock-notify__form');
			var email = root.querySelector('.jofme-restock-notify__email');
			var productId = root.getAttribute('data-product-id');
			if (!form || !email || !productId || root.dataset.jofmeRestockBound) {
				return;
			}
			root.dataset.jofmeRestockBound = '1';

			email.addEventListener('input', function () {
				email.classList.remove('is-invalid');
				var status = root.querySelector('.jofme-restock-notify__status');
				if (status && status.classList.contains('is-error')) {
					status.hidden = true;
					status.textContent = '';
					status.classList.remove('is-error');
				}
			});

			form.addEventListener('submit', function (event) {
				event.preventDefault();
				if (root.classList.contains('is-success') || root.classList.contains('is-loading')) {
					return;
				}

				var value = (email.value || '').trim();
				if (!value || value.indexOf('@') < 1) {
					showError(root, cfg.invalid || 'Βάλε ένα έγκυρο email.');
					return;
				}

				root.classList.add('is-loading');
				setBusy(root, true);

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
						root.classList.remove('is-loading');
						setBusy(root, false);
						if (result.ok) {
							showSuccess(
								root,
								(result.data && result.data.data && result.data.data.message) ||
									cfg.success ||
									'Τέλεια — θα σε ενημερώσουμε μόλις ξανάρθει.'
							);
							return;
						}
						showError(
							root,
							(result.data && result.data.data && result.data.data.message) ||
								cfg.error ||
								'Κάτι πήγε στραβά. Δοκίμασε ξανά.'
						);
					})
					.catch(function () {
						root.classList.remove('is-loading');
						setBusy(root, false);
						showError(root, cfg.error || 'Κάτι πήγε στραβά. Δοκίμασε ξανά.');
					});
			});
		});
	});
})();
