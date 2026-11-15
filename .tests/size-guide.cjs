/** node .tests/size-guide.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'inc/size-guide.php'), 'utf8');
const boot = fs.readFileSync(path.join(root, 'functions.php'), 'utf8');
const adminJs = fs.readFileSync(path.join(root, 'assets/js/size-guide-admin.js'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/size-guide.js'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/size-guide.css'), 'utf8');

assert.match(boot, /\/inc\/size-guide\.php/);
assert.match(php, /register_post_type\(\s*'jofme_size_guide'/);
assert.match(php, /_jofme_size_guide_pdf_id/);
assert.match(php, /_jofme_size_guide_cats/);
assert.match(php, /_jofme_size_guide_products/);
assert.match(php, /jofme_size_guide_products_box/);
assert.match(php, /wc-product-search/);
assert.match(php, /jofme_get_size_guides_for_product/);
assert.match(php, /woocommerce_before_add_to_cart_button/);
assert.match(php, /application\/pdf/);
assert.match(adminJs, /application\/pdf/);
assert.match(js, /showModal/);
assert.match(css, /\.jofme-size-guide-dialog/);
console.log('PASS: size guide CPT, PDF import, category assignment wiring');
