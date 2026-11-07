/** node .tests/product-page-gallery.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'inc/gallery-design.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/gallery-design.css'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/gallery-design.js'), 'utf8');

assert.match(php, /remove_action\(\s*'woocommerce_single_product_summary',\s*'woocommerce_template_single_excerpt'/);
assert.match(php, /details open class="jofme-product-details__description"/);
assert.match(php, /function jofme_gallery_mobile_title/);
assert.match(php, /jofme-product-title--mobile/);
assert.match(php, /jofme-stacked-gallery__link/);
assert.match(php, /data-lightbox-index/);
assert.match(css, /\.jofme-product-title--mobile/);
assert.match(css, /grid-template-areas:\s*"title"\s*"images"\s*"summary"/);
assert.match(css, /#jofme-gallery-lightbox/);
assert.match(js, /openLightbox/);
assert.match(js, /ArrowLeft/);
assert.match(js, /ArrowRight/);
assert.match(js, /jofme-stacked-gallery__link/);
console.log('PASS: product page description, mobile title, lightbox wiring');
