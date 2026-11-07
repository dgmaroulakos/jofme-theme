(function () {
	'use strict';

	const doc = document;
	const body = doc.body;
	const data = window.jofmeData || {};
	const scrollConfig = data.scroll || {};

	function qs(sel, ctx) {
		return (ctx || doc).querySelector(sel);
	}

	function qsa(sel, ctx) {
		return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel));
	}

	function prefersReducedMotion() {
		return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	}

	const SCROLL_PROFILES = {
		slow: { ease: 0.045, duration: 1200 },
		medium: { ease: 0.08, duration: 800 },
		fast: { ease: 0.14, duration: 480 },
	};

	function scrollProfile() {
		const speed = scrollConfig.speed === 'slow' || scrollConfig.speed === 'fast' ? scrollConfig.speed : 'medium';
		return SCROLL_PROFILES[speed];
	}

	function maxScrollY() {
		return Math.max(0, doc.documentElement.scrollHeight - window.innerHeight);
	}

	function clampScroll(y) {
		return Math.min(maxScrollY(), Math.max(0, y));
	}

	function nestedScrollable(start) {
		let el = start;
		while (el && el !== body && el !== doc.documentElement) {
			const style = window.getComputedStyle(el);
			const overflowY = style.overflowY;
			if ((overflowY === 'auto' || overflowY === 'scroll' || overflowY === 'overlay') && el.scrollHeight > el.clientHeight + 1) {
				return el;
			}
			el = el.parentElement;
		}
		return null;
	}

	/**
	 * Animate window scroll with a fixed duration (anchors, back-to-top, gallery).
	 */
	function jofmeScrollTo(top, options) {
		const opts = options || {};
		const reduced = prefersReducedMotion() || opts.instant;
		const target = clampScroll(typeof top === 'number' ? top : 0);

		if (reduced || !scrollConfig.enabled) {
			window.scrollTo(0, target);
			if (smoothScroll) {
				smoothScroll.sync(target);
			}
			return;
		}

		const start = window.scrollY || window.pageYOffset || 0;
		const distance = target - start;
		if (Math.abs(distance) < 1) {
			return;
		}

		const duration = Math.max(200, opts.duration || scrollProfile().duration);
		const startTime = performance.now();

		if (smoothScroll) {
			smoothScroll.pause();
		}

		function easeInOutCubic(t) {
			return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
		}

		function frame(now) {
			const progress = Math.min(1, (now - startTime) / duration);
			const next = start + distance * easeInOutCubic(progress);
			window.scrollTo(0, next);
			if (progress < 1) {
				requestAnimationFrame(frame);
				return;
			}
			window.scrollTo(0, target);
			if (smoothScroll) {
				smoothScroll.sync(target);
				smoothScroll.resume();
			}
		}

		requestAnimationFrame(frame);
	}

	window.jofmeScrollTo = jofmeScrollTo;

	/**
	 * Continuous wheel smoothing that keeps real window scroll (sticky-safe).
	 */
	const smoothScroll = (function createSmoothScroll() {
		if (!scrollConfig.enabled || prefersReducedMotion()) {
			return null;
		}

		let targetY = window.scrollY || 0;
		let currentY = targetY;
		let rafId = 0;
		let paused = false;
		let dragging = false;
		const ease = scrollProfile().ease;

		function stopRaf() {
			if (rafId) {
				cancelAnimationFrame(rafId);
				rafId = 0;
			}
		}

		function tick() {
			rafId = 0;
			if (paused) {
				return;
			}
			const delta = targetY - currentY;
			if (Math.abs(delta) < 0.35) {
				currentY = targetY;
				window.scrollTo(0, currentY);
				return;
			}
			currentY += delta * ease;
			window.scrollTo(0, currentY);
			rafId = requestAnimationFrame(tick);
		}

		function start() {
			if (!rafId && !paused) {
				rafId = requestAnimationFrame(tick);
			}
		}

		function onWheel(event) {
			if (event.defaultPrevented || event.ctrlKey) {
				return;
			}
			if (nestedScrollable(event.target)) {
				return;
			}
			event.preventDefault();
			targetY = clampScroll(targetY + event.deltaY);
			start();
		}

		function onScroll() {
			if (paused || dragging) {
				return;
			}
			if (rafId) {
				return;
			}
			const y = window.scrollY || 0;
			targetY = y;
			currentY = y;
		}

		window.addEventListener('wheel', onWheel, { passive: false });
		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('mousedown', (event) => {
			if (event.target === doc.documentElement || event.target === body) {
				dragging = true;
			}
		});
		window.addEventListener('mouseup', () => {
			dragging = false;
			targetY = window.scrollY || 0;
			currentY = targetY;
		});
		window.addEventListener('keydown', (event) => {
			const keys = ['ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End', 'Space'];
			if (!keys.includes(event.key) && event.code !== 'Space') {
				return;
			}
			if (nestedScrollable(event.target) || /^(INPUT|TEXTAREA|SELECT)$/.test(event.target.tagName) || event.target.isContentEditable) {
				return;
			}
			// Let the browser apply the key scroll, then sync on the next frame.
			requestAnimationFrame(() => {
				targetY = window.scrollY || 0;
				currentY = targetY;
			});
		});

		return {
			sync(y) {
				targetY = clampScroll(typeof y === 'number' ? y : window.scrollY || 0);
				currentY = targetY;
			},
			pause() {
				paused = true;
				stopRaf();
			},
			resume() {
				paused = false;
				this.sync(window.scrollY || 0);
			},
		};
	})();

	/* Same-page anchors use the shared duration. */
	doc.addEventListener('click', (event) => {
		const link = event.target.closest('a[href*="#"]');
		if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
			return;
		}
		const href = link.getAttribute('href');
		if (!href || href === '#' || href.charAt(0) === '#') {
			const id = href === '#' ? '' : href.slice(1);
			if (!id) {
				return;
			}
			const target = doc.getElementById(decodeURIComponent(id));
			if (!target) {
				return;
			}
			event.preventDefault();
			const headerOffset = parseFloat(getComputedStyle(doc.documentElement).getPropertyValue('--jofme-header-h')) || 0;
			const top = target.getBoundingClientRect().top + (window.scrollY || 0) - headerOffset - 12;
			jofmeScrollTo(top);
			if (history.pushState) {
				history.pushState(null, '', '#' + id);
			}
			return;
		}
		try {
			const url = new URL(link.href, window.location.href);
			if (url.pathname !== window.location.pathname || url.search !== window.location.search || !url.hash || url.hash === '#') {
				return;
			}
			const target = doc.getElementById(decodeURIComponent(url.hash.slice(1)));
			if (!target) {
				return;
			}
			event.preventDefault();
			const headerOffset = parseFloat(getComputedStyle(doc.documentElement).getPropertyValue('--jofme-header-h')) || 0;
			const top = target.getBoundingClientRect().top + (window.scrollY || 0) - headerOffset - 12;
			jofmeScrollTo(top);
			if (history.pushState) {
				history.pushState(null, '', url.hash);
			}
		} catch (_) {
			/* Ignore invalid URLs. */
		}
	});

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
		drawer.addEventListener('click', (e) => {
			if (e.target === drawer) closeMenu();
		});
		drawer.addEventListener('keydown', (e) => {
			if (e.key !== 'Tab') return;
			const focusable = qsa('button, a[href], input, select, textarea, [tabindex="0"]', drawer).filter((el) => el.getClientRects().length);
			const first = focusable[0];
			const last = focusable[focusable.length - 1];
			if (!first) return;
			if (e.shiftKey && doc.activeElement === first) {
				e.preventDefault();
				last.focus();
			} else if (!e.shiftKey && doc.activeElement === last) {
				e.preventDefault();
				first.focus();
			}
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
			jofmeScrollTo(0);
		});
	}

	/* Shop / category archives: smooth-scroll to the product grid on entry. */
	function isProductArchive() {
		return (
			body.classList.contains('woocommerce') &&
			(body.classList.contains('post-type-archive-product') ||
				body.classList.contains('tax-product_cat') ||
				body.classList.contains('tax-product_tag') ||
				body.classList.contains('woocommerce-shop'))
		);
	}
	function scrollArchiveToProducts() {
		if (!scrollConfig.archiveProducts || !isProductArchive()) {
			return;
		}
		if (window.location.hash) {
			return;
		}
		if ((window.scrollY || window.pageYOffset || 0) > 16) {
			return;
		}
		const target = qs('#jofme-catalog-products') || qs('.jofme-shop ul.products') || qs('ul.products');
		if (!target) {
			return;
		}
		const headerOffset = parseFloat(getComputedStyle(doc.documentElement).getPropertyValue('--jofme-header-h')) || 0;
		const top = target.getBoundingClientRect().top + (window.scrollY || 0) - headerOffset - 12;
		if (top <= 24) {
			return;
		}
		window.setTimeout(function () {
			jofmeScrollTo(top);
		}, 150);
	}
	if (doc.readyState === 'complete') {
		scrollArchiveToProducts();
	} else {
		window.addEventListener('load', scrollArchiveToProducts, { once: true });
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
