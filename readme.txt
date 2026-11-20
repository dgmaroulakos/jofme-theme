=== Jofme ===
Contributors: jofme
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.7.49
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Elegant jewelry & luxury WooCommerce theme with Elementor and WPML support.

== Description ==

Jofme is a refined WordPress theme for jewelry stores and luxury accessory brands.
It includes WooCommerce styling, Elementor Theme Builder locations, and WPML-ready
strings / wpml-config.xml.

== Installation ==

1. Upload the `jofme` folder to `/wp-content/themes/`
2. Activate via Appearance → Themes
3. Install WooCommerce (and optionally Elementor + WPML)
4. Assign menus: Primary, Footer, Mobile
5. Customize under Appearance → Customize → Jofme Options

== Recommended plugins ==

* WooCommerce
* Elementor (and Elementor Pro for Theme Builder header/footer)
* WPML or Polylang

== Changelog ==

= 1.7.49 =
* Shop filters: choose which attributes appear in the Filters panel (Customizer → Shop); hide duplicate WooCommerce filter widgets.

= 1.7.48 =
* Wishlist: cleaner heart icon on product cards / header, reliable card positioning.

= 1.7.47 =
* Product tabs: active tab buttons use white text on black background (pill default).
* Wishlist page: auto-created page with [jofme_wishlist], header heart link + count, AJAX product grid from localStorage (YITH/TI still take over when installed).

= 1.7.46 =
* Product gallery carousel: more reliable nav/swipe; variation images jump to matching slides.
* Carousel thumbnails: no white border, left-aligned, larger (88px).
* Product tabs: black background / white text by default, plus Customizer style options.
* Product cards: wishlist heart (localStorage, with YITH/TI bridge when installed).
* Bestsellers Tabs: carousel layout, up to 48 products, richer tab appearance controls.

= 1.7.45 =
* Bestsellers cards: larger color swatches (30px) and 30px product titles.
* Shop Filters: auto-list every global product attribute (new attributes appear without a widget).

= 1.7.44 =
* Bestsellers cards: remove clamp padding on product images (padding: 0).

= 1.7.43 =
* Bestsellers Tabs: base query by category and/or attribute for all tabs.
* Theme updater: fall back to github.com releases.atom when api.github.com fails, and show the real error.

= 1.7.42 =
* Bestsellers Tabs: per-tab product query by global attribute term.

= 1.7.41 =
* Bestsellers Tabs: option to show archive-style attribute swatches on product cards.

= 1.7.40 =
* Prebuilt footer: logo/contact on the left, three columns on the right.

= 1.7.39 =
* Prebuilt footer: 3 columns + logo/contact on the right (Customizer).

= 1.7.38 =
* Product cards: remove white / surface background behind images.

= 1.7.37 =
* Header width: content / boxed / wide / narrow / full (Customizer).

= 1.7.36 =
* Header layout: Stacked (logo above menu); all layouts listed in Customizer.

= 1.7.35 =
* Size guide modal: render PDF pages only (no browser PDF editor chrome).

= 1.7.34 =
* Size guides: assign to individual products as well as categories.

= 1.7.33 =
* Size guides: multiple PDF guides under Products → Size guides, assigned to selected categories.

= 1.7.32 =
* Gallery product photos: force original full file (no cropped srcset); variations use full_src.

= 1.7.31 =
* Gallery product photos: no image background by default (optional color still in Customizer).

= 1.7.30 =
* Gallery summary max-width 730px; image background color control; original aspect (no crop).
* Header: Customizer logo max height (desktop) and max width (mobile).

= 1.7.29 =
* Gallery product design: Customizer choice between stacked scroll and carousel images.

= 1.7.28 =
* Customizer: stuck header height (desktop + mobile), applied via fixed bar + spacer.

= 1.7.27 =
* Sticky header no longer collapses height on stick (fixes scroll jump/flicker).

= 1.7.26 =
* Product tabs wrapper padding 0 15% (tighter on mobile).
* Default theme background color set to #e8caaf.

= 1.7.25 =
* Mobile category term description truncates to 20 words with a More arrow toggle.

= 1.7.24 =
* Product page: remove short description above add to cart; open Description panel below.
* Product page mobile: title above gallery images.
* Product gallery lightbox with prev/next navigation.

= 1.7.23 =
* Quick view grid: images and summary columns force width 100%.

= 1.7.22 =
* Quick view mirrors the product page: gallery, variations/swatches, quantity and AJAX add to cart.

= 1.7.21 =
* Category cover description uses full content width (max-width: 100%).

= 1.7.20 =
* Archive pages smooth-scroll to the product grid on load.
* Product card image hover scale (default 1.05) with Customizer control.

= 1.7.19 =
* Customizer: mobile header min height (default 80px).

= 1.7.18 =
* Customizer: header min height and sticky header padding.

= 1.7.17 =
* Transparent header logo and text color; menu typography controls (font, size, weight, tracking, transform).

= 1.7.16 =
* Remove the editorial header utility bar.

= 1.7.15 =
* Fix list-view product description (render outside the card link; fall back to full description).

= 1.7.14 =
* Mobile logo max-width 90px.

= 1.7.13 =
* Header, utility bar and announcement follow the Layout Settings width (boxed / wide / narrow / full).

= 1.7.12 =
* Category cover is full-width; title and description align to the site layout width.

= 1.7.11 =
* Header: align logo and icons on one axis, logo max-height 100px, extra vertical padding away from border lines.

= 1.7.10 =
* Theme updater: “Check again” clears the GitHub cache; Jofme Updates shows installed/latest and a force-check button.

= 1.7.9 =
* List view product descriptions show up to 20 words, then a “View more” link to the product.

= 1.7.8 =
* Remove the inline “Filter collection” accordion; catalog filters come from the shop sidebar / off-canvas only.

= 1.7.7 =
* Mobile catalog toolbar: icon-only Filters (Customizer SVG), sorting, 1–2 column controls; result count on its own row.

= 1.7.6 =
* Lock page scroll while the shop off-canvas is open.
* Color catalog filters preselect matching variations on cards and product links.

= 1.7.5 =
* Off-canvas shop sidebar shows filter widgets immediately (no nested Woo overlay click).

= 1.7.4 =
* Shop sidebar becomes off-canvas on mobile/tablet, with Customizer open effect.

= 1.7.3 =
* Fix bestsellers card images being clipped out of view (absolute overlay sizing).

= 1.7.2 =
* Fix bestsellers card media overlay so hover images no longer create blank gaps.


= 1.7.1 =
* Bestsellers product card layout used theme-wide, with shared hover image support.

= 1.7.0 =
* Elementor Bestsellers Tabs widget with category filters and product grid.

= 1.6.1 =
* Global smooth page scroll with Customizer speed control (slow / medium / fast).

= 1.6.0 =
* Attribute-level “Display as color swatches” option.
* Term color picker for swatch hex values.
* Color swatches on product cards, catalog filters and variation forms.
* Gallery product design: stacked scroll images with sticky purchase panel.

= 1.5.0 =
* Per-category covers and full-width catalog heading.
* Icon view controls with 4-column support.
* Gallery intro boundary and native zoom fixes.
* Consistent loop columns, centered header and swatch link structure.

= 1.4.0 =
* Fix hover image paint order after the opacity transition ends.
* Gallery product cards and Gallery single-product design.
* Aeonik Pro Light CDN and configurable text/heading fonts.
* Native variation, gallery, quantity and product care controls.

= 1.3.0 =
* Editorial header with utility menus and optional transparent homepage navigation.
* Elementor Editorial Story and Weekly Sales widgets.
* Mobile menu focus management and shared header panels.

= 1.0.0 =
* Initial release — theme foundation
