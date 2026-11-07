/** node .tests/category-cover.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'inc/category-cover.php'), 'utf8');
const archive = fs.readFileSync(path.join(root, 'woocommerce/archive-product.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/premium-shop.css'), 'utf8');

assert.match(php, /function jofme_catalog_has_cover/);
assert.match(php, /function jofme_category_cover_id/);
assert.match(archive, /jofme_catalog_has_cover/);
assert.match(archive, /\$has_cover/);
assert.match(css, /\.jofme-catalog-heading--cover/);
assert.match(css, /var\(--jofme-layout-width,\s*var\(--jofme-container\)\)/);
assert.doesNotMatch(css, /\.jofme-catalog-heading--cover[^{]*\{[^}]*padding:\s*clamp/s);
console.log('PASS: full-bleed category cover with layout-aligned copy');
