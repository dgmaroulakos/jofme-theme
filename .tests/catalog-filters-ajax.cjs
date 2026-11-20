/** node .tests/catalog-filters-ajax.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');

const php = fs.readFileSync(path.join(root, 'inc/premium-shop.php'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/premium-shop.js'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/premium-shop.css'), 'utf8');
const wishlist = fs.readFileSync(path.join(root, 'assets/js/wishlist.js'), 'utf8');
const workflow = fs.readFileSync(path.join(root, '.github/workflows/release.yml'), 'utf8');

assert.match(php, /function jofme_catalog_results_open/);
assert.match(php, /function jofme_catalog_results_close/);
assert.match(php, /data-jofme-ajax-filters/);
assert.match(php, /data-jofme-catalog-results/);
assert.match(php, /ajaxFilters/);
assert.match(js, /jofme:catalogupdated/);
assert.match(js, /buildFilterUrl/);
assert.match(js, /loadCatalog/);
assert.match(js, /data-jofme-ajax-filters-form/);
assert.match(js, /history\.pushState/);
assert.match(js, /popstate/);
assert.match(css, /\.jofme-catalog-results\b/);
assert.match(css, /is-ajax-loading/);
assert.match(wishlist, /jofme:catalogupdated/);
assert.match(workflow, /catalog-filters-ajax\.cjs/);

console.log('PASS: catalog filters AJAX wiring present');
