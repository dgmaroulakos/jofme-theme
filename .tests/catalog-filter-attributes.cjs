/** node .tests/catalog-filter-attributes.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');

const php = fs.readFileSync(path.join(root, 'inc/premium-shop.php'), 'utf8');
const layout = fs.readFileSync(path.join(root, 'inc/layout.php'), 'utf8');
const workflow = fs.readFileSync(path.join(root, '.github/workflows/release.yml'), 'utf8');

assert.match(php, /function jofme_catalog_filter_attr_mod_key/);
assert.match(php, /function jofme_catalog_filter_attribute_enabled/);
assert.match(php, /function jofme_catalog_filter_attributes/);
assert.match(php, /jofme_filter_show_collection/);
assert.match(php, /jofme_filter_show_price/);
assert.match(php, /jofme_filter_attr_/);
assert.match(php, /function jofme_suppress_duplicate_shop_filter_widgets/);
assert.match(php, /woocommerce_layered_nav/);
assert.match(php, /widget_display_callback/);
assert.match(php, /jofme_catalog_filter_attributes\s*\(/);
assert.match(layout, /Jofme Options → Shop|enable under Jofme Options/);
assert.match(workflow, /catalog-filter-attributes\.cjs/);

console.log('PASS: catalog filter attribute toggles + duplicate widget suppression');
