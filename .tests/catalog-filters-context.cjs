/** node .tests/catalog-filters-context.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');

const php = fs.readFileSync(path.join(root, 'inc/premium-shop.php'), 'utf8');
const swatches = fs.readFileSync(path.join(root, 'inc/swatches.php'), 'utf8');
const workflow = fs.readFileSync(path.join(root, '.github/workflows/release.yml'), 'utf8');

assert.match(php, /function jofme_catalog_filter_object_ids/);
assert.match(php, /function jofme_catalog_filter_terms/);
assert.match(php, /object_ids/);
assert.match(php, /wc_get_products/);
assert.match(php, /is_product_taxonomy/);
assert.match(php, /jofme_catalog_filter_terms\(\s*\$taxonomy/);
assert.match(swatches, /jofme_catalog_filter_terms/);
assert.match(workflow, /catalog-filters-context\.cjs/);

console.log('PASS: catalog filters scoped to current category/collection products');
