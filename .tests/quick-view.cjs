/** node .tests/quick-view.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'inc/premium-shop.php'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/premium-shop.js'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/premium-shop.css'), 'utf8');
const swatches = fs.readFileSync(path.join(root, 'inc/swatches.php'), 'utf8');

assert.match(php, /function jofme_preview_gallery/);
assert.match(php, /woocommerce_template_single_add_to_cart/);
assert.match(php, /woocommerce_template_single_price/);
assert.match(php, /woocommerce_template_single_excerpt/);
assert.match(php, /jofme-preview-product/);
assert.match(php, /wc-add-to-cart-variation/);
assert.match(js, /wc_variation_form/);
assert.match(js, /initPreviewProduct/);
assert.match(js, /add_to_cart/);
assert.match(js, /found_variation/);
assert.match(css, /\.jofme-preview-product form\.cart/);
assert.match(css, /\.jofme-preview-gallery__main/);
assert.match(swatches, /is_shop\(\) \|\| is_product_taxonomy\(\)/);
console.log('PASS: quick view mirrors product page (gallery, variations, cart)');
