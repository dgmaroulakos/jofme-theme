/** node .tests/ajax-add-to-cart.cjs — prevent double qty on wc-ajax add_to_cart. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');

const php = fs.readFileSync(path.join(root, 'inc/woocommerce.php'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/premium-shop.js'), 'utf8');
const workflow = fs.readFileSync(path.join(root, '.github/workflows/release.yml'), 'utf8');

assert.match(php, /function jofme_prevent_duplicate_ajax_add_to_cart/);
assert.match(php, /wp_loaded',\s*'jofme_prevent_duplicate_ajax_add_to_cart',\s*5/);
assert.match(php, /unset\(\s*\$_REQUEST\['add-to-cart'\],\s*\$_POST\['add-to-cart'\]\s*\)/);
assert.match(php, /wc-ajax/);
assert.match(js, /data\.delete\(\s*['"]add-to-cart['"]\s*\)/);
assert.match(workflow, /ajax-add-to-cart\.cjs/);

console.log('PASS: ajax add-to-cart duplicate prevention wiring');
