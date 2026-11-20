/** node .tests/catalog-price-slider.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');

const php = fs.readFileSync(path.join(root, 'inc/premium-shop.php'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/premium-shop.js'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/premium-shop.css'), 'utf8');
const workflow = fs.readFileSync(path.join(root, '.github/workflows/release.yml'), 'utf8');

assert.match(php, /function jofme_catalog_price_bounds/);
assert.match(php, /function jofme_filter_price_slider/);
assert.match(php, /data-jofme-price-slider/);
assert.match(php, /jofme-price-slider__input--min/);
assert.match(php, /jofme-price-slider__input--max/);
assert.match(php, /wc_product_meta_lookup/);
assert.match(js, /initPriceSliders/);
assert.match(js, /syncPriceSlider/);
assert.match(js, /commitPriceSlider/);
assert.match(js, /data-jofme-price-slider/);
assert.match(css, /\.jofme-price-slider__track\b/);
assert.match(css, /\.jofme-price-slider__input\b/);
assert.match(css, /\.jofme-price-slider__range\b/);
assert.match(workflow, /catalog-price-slider\.cjs/);
assert.doesNotMatch(php, /jofme-filter-panel__price-field/);

console.log('PASS: catalog price dual-range slider');
