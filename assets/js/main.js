(function () {
	'use strict';

	const doc = document;
	const body = doc.body;

	function qs(sel, ctx) {
		return (ctx || doc).querySelector(sel);
	}

	function qsa(sel, ctx) {
		return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel));
	}

	/* Mobile menu */
	const menuToggle = qs('.jofme-menu-toggle');
	const drawer = qs('#jofme-mobile-drawer');

	if (menuToggle && drawer) {
		menuToggle.addEventListener('click', function () {
			const open = drawer.hasAttribute('hidden');
			if (open) {
				drawer.removeAttribute('hidden');
				requestAnimationFrame(function () {
					drawer.classList.add('is-open');
				});
				body.classList.add('jofme-nav-open');
				menuToggle.setAttribute('aria-expanded', 'true');
			} else {
				drawer.classList.remove('is-open');
				body.classList.remove('jofme-nav-open');
				menuToggle.setAttribute('aria-expanded', 'false');
				setTimeout(function () {
					drawer.setAttribute('hidden', '');
				}, 250);
			}
		});

		drawer.addEventListener('click', function (e) {
			if (e.target === drawer) {
				menuToggle.click();
			}
		});
	}

	/* Header search */
	const searchToggle = qs('.jofme-search-toggle');
	const searchPanel = qs('#jofme-header-search');

	if (searchToggle && searchPanel) {
		searchToggle.addEventListener('click', function () {
			const isHidden = searchPanel.hasAttribute('hidden');
			if (isHidden) {
				searchPanel.removeAttribute('hidden');
				searchToggle.setAttribute('aria-expanded', 'true');
				const input = qs('input[type="search"]', searchPanel);
				if (input) {
					input.focus();
				}
			} else {
				searchPanel.setAttribute('hidden', '');
				searchToggle.setAttribute('aria-expanded', 'false');
			}
		});
	}

	/* Sticky header shadow */
	const header = qs('.site-header');
	if (header && body.classList.contains('has-sticky-header')) {
		const onScroll = function () {
			if (window.scrollY > 8) {
				header.classList.add('is-stuck');
			} else {
				header.classList.remove('is-stuck');
			}
		};
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/* Back to top */
	const backTop = qs('.jofme-back-to-top');
	if (backTop) {
		backTop.removeAttribute('hidden');
		window.addEventListener(
			'scroll',
			function () {
				if (window.scrollY > 500) {
					backTop.classList.add('is-visible');
				} else {
					backTop.classList.remove('is-visible');
				}
			},
			{ passive: true }
		);
		backTop.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}

	/* Escape closes overlays */
	doc.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') {
			return;
		}
		if (drawer && !drawer.hasAttribute('hidden') && menuToggle) {
			menuToggle.click();
		}
		if (searchPanel && !searchPanel.hasAttribute('hidden') && searchToggle) {
			searchToggle.click();
		}
	});
})();
