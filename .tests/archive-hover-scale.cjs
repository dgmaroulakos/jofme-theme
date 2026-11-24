/** node .tests/archive-hover-scale.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const hover = fs.readFileSync(path.join(root, 'inc/product-hover.php'), 'utf8');
const enqueue = fs.readFileSync(path.join(root, 'inc/enqueue.php'), 'utf8');
const mainJs = fs.readFileSync(path.join(root, 'assets/js/main.js'), 'utf8');
const cards = fs.readFileSync(path.join(root, 'assets/css/product-cards.css'), 'utf8');
const premium = fs.readFileSync(path.join(root, 'inc/premium-shop.php'), 'utf8');

assert.match(hover, /jofme_product_hover_scale/);
assert.match(hover, /jofme_archive_scroll_products/);
assert.match(hover, /jofme_sanitize_hover_scale/);
assert.match(hover, /--jofme-card-hover-scale/);
assert.match(hover, /jofme-card-media/);
assert.match(hover, /return '<span class="jofme-card-media">'/);
assert.match(enqueue, /archiveProducts/);
assert.match(mainJs, /scrollEntryToBreadcrumbs/);
assert.match(mainJs, /woocommerce-breadcrumb/);
assert.match(mainJs, /single-product/);
assert.match(mainJs, /jofme-catalog-products/);
assert.match(hover, /Smooth scroll to breadcrumbs on page load/);
assert.match(premium, /id="jofme-catalog-products"/);
assert.match(cards, /--jofme-card-hover-scale/);
assert.match(cards, /jofme-bestsellers__card:hover[\s\S]*jofme-card-media img\{transform:scale/);
console.log('PASS: scroll-to-breadcrumbs and card hover scale wiring');
