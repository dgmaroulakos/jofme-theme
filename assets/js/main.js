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
        // Keep the fixed drawer outside sticky/backdrop-filter ancestors.
        body.appendChild(drawer);
        let closeTimer;
        function closeMenu() {
            clearTimeout(closeTimer);
            drawer.classList.remove('is-open');
            body.classList.remove('jofme-nav-open');
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.focus();
            closeTimer = setTimeout(() => drawer.setAttribute('hidden', ''), 250);
        }
        menuToggle.addEventListener('click', function () {
            if (menuToggle.getAttribute('aria-expanded') === 'true') {
                closeMenu();
                return;
            }
            clearTimeout(closeTimer);
            drawer.removeAttribute('hidden');
            requestAnimationFrame(() => drawer.classList.add('is-open'));
            body.classList.add('jofme-nav-open');
            menuToggle.setAttribute('aria-expanded', 'true');
            const first = qs('button, a[href]', drawer);
            if (first) first.focus();
        });
        const closeButton = qs('.jofme-mobile-close', drawer);
        if (closeButton) closeButton.addEventListener('click', closeMenu);
        drawer.addEventListener('click', e => { if (e.target === drawer) closeMenu(); });
        drawer.addEventListener('keydown', e => {
            if (e.key !== 'Tab') return;
            const focusable = qsa('button, a[href], input, select, textarea, [tabindex="0"]', drawer).filter(el => el.getClientRects().length);
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (!first) return;
            if (e.shiftKey && doc.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && doc.activeElement === last) { e.preventDefault(); first.focus(); }
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 900 && menuToggle.getAttribute('aria-expanded') === 'true') {
                clearTimeout(closeTimer);
                drawer.setAttribute('hidden', '');
                drawer.classList.remove('is-open');
                body.classList.remove('jofme-nav-open');
                menuToggle.setAttribute('aria-expanded', 'false');
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
                searchToggle.focus();
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
		if (drawer && menuToggle && menuToggle.getAttribute('aria-expanded') === 'true') {
			menuToggle.click();
		}
		if (searchPanel && !searchPanel.hasAttribute('hidden') && searchToggle) {
			searchToggle.click();
		}
	});
})();
