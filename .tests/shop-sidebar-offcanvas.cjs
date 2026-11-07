/** node .tests/shop-sidebar-offcanvas.cjs — requires playwright + pngjs (optional visual). */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const css = fs.readFileSync(path.join(root, 'assets/css/layout.css'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/premium-shop.js'), 'utf8');
const layout = fs.readFileSync(path.join(root, 'inc/layout.php'), 'utf8');

for (const effect of ['slide-left', 'slide-right', 'slide-bottom', 'fade', 'scale']) {
 assert.match(css, new RegExp(`jofme-sidebar-effect-${effect}`), `CSS missing ${effect}`);
 assert.match(layout, new RegExp(`'${effect}'`), `PHP missing ${effect}`);
}
assert.match(css, /jofme-shop-sidebar-toggle/);
assert.match(css, /max-width:\s*959px/);
assert.match(css, /wc-block-product-filters__open-overlay/);
assert.match(css, /wc-block-product-filters__overlay-dialog/);
assert.match(js, /jofme-shop-sidebar-panel/);
assert.match(js, /jofme-sidebar-open/);
assert.match(js, /detach/);
assert.match(js, /expandWidgetFilters/);
assert.match(js, /is-filter-drawer-disabled/);
assert.match(js, /lockPageScroll/);
assert.match(js, /unlockPageScroll/);
assert.match(css, /touch-action:\s*none/);
assert.match(layout, /jofme_catalog_sidebar_effect/);
console.log('PASS: shop sidebar off-canvas effect wiring present in CSS/JS/PHP');
