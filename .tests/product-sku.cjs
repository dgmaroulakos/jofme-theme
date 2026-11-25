/** node .tests/product-sku.cjs — gallery product page shows SKU. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');

const php = fs.readFileSync(path.join(root, 'inc/gallery-design.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/gallery-design.css'), 'utf8');
const workflow = fs.readFileSync(path.join(root, '.github/workflows/release.yml'), 'utf8');

const js = fs.readFileSync(path.join(root, 'assets/js/gallery-design.js'), 'utf8');

assert.match(php, /function jofme_gallery_product_sku/);
assert.match(php, /jofme_gallery_product_sku/);
assert.match(php, /sku_wrapper/);
assert.match(php, /product_meta jofme-product-sku/);
assert.match(php, /data-jofme-parent-sku/);
assert.match(php, /class="sku"/);
assert.match(php, /woocommerce_template_single_meta/);
assert.match(css, /\.jofme-product-sku/);
assert.match(js, /syncVariationSku/);
assert.match(js, /bindVariationSku/);
assert.match(js, /found_variation\.jofmeSku/);
assert.match(js, /ensureDataset\(form\)/);
assert.doesNotMatch(js, /form\.dataset\s*=\s*form\.dataset\s*\|\|/);
assert.match(workflow, /product-sku\.cjs/);

console.log('PASS: product page SKU on gallery summary + variation sync');
