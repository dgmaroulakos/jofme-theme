/** node .tests/catalog-filters-accordion.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');

const php = fs.readFileSync(path.join(root, 'inc/premium-shop.php'), 'utf8');
const swatches = fs.readFileSync(path.join(root, 'inc/swatches.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/premium-shop.css'), 'utf8');
const workflow = fs.readFileSync(path.join(root, '.github/workflows/release.yml'), 'utf8');

assert.match(php, /function jofme_filter_checklist/);
assert.match(php, /function jofme_filter_selected_slugs/);
assert.match(php, /function jofme_catalog_filter_term_count/);
assert.match(php, /function jofme_filter_remove_url/);
assert.match(php, /function jofme_normalize_catalog_filter_query/);
assert.match(php, /jofme-filter-panel/);
assert.match(php, /jofme-filter-panel__checkbox/);
assert.match(php, /jofme-filter-panel__count/);
assert.match(php, /query_type_/);
assert.match(php, /name="' \. esc_attr\( \$key \) \. '\[]"/);
assert.match(swatches, /jofme_filter_checklist/);
assert.match(css, /\.jofme-filter-panel__summary\b/);
assert.match(css, /\.jofme-filter-panel__checkbox\b/);
assert.match(css, /\.jofme-filter-panel__count\b/);
assert.match(workflow, /catalog-filters-accordion\.cjs/);

console.log('PASS: catalog filters accordion checklist layout');
