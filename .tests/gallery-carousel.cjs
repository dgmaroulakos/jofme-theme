/** node .tests/gallery-carousel.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'inc/gallery-design.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/gallery-design.css'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/gallery-design.js'), 'utf8');

assert.match(php, /jofme_gallery_display/);
assert.match(php, /'carousel'\s*=>/);
assert.match(php, /function jofme_gallery_display_mode/);
assert.match(php, /jofme-gallery-' \. jofme_gallery_display_mode\(\)/);
assert.match(php, /jofme-carousel-gallery__nav/);
assert.match(php, /jofme-carousel-gallery__thumb/);
assert.match(css, /\.jofme-carousel-gallery__viewport/);
assert.match(css, /\.jofme-carousel-gallery__nav--prev/);
assert.match(css, /justify-content:flex-start/);
assert.match(css, /\.jofme-carousel-gallery__thumb\{[^}]*border:0/);
assert.match(css, /width:88px/);
assert.match(js, /bindCarouselGallery/);
assert.match(js, /touchstart/);
assert.match(js, /findSlideIndexForVariation/);
assert.match(js, /\.jofme-carousel-gallery/);
assert.match(js, /closest\('\.jofme-carousel-gallery__nav'\)/);
console.log('PASS: product gallery carousel Customizer + markup wiring');
