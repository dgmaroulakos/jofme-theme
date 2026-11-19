/** node .tests/wishlist-heart.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'inc/wishlist.php'), 'utf8');
const premium = fs.readFileSync(path.join(root, 'inc/premium-shop.php'), 'utf8');
const functions = fs.readFileSync(path.join(root, 'functions.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/wishlist.css'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/wishlist.js'), 'utf8');
const widget = fs.readFileSync(path.join(root, 'inc/widgets/class-bestsellers-tabs.php'), 'utf8');

assert.match(functions, /\/inc\/wishlist\.php/);
assert.match(php, /function jofme_wishlist_button_html/);
assert.match(php, /data-jofme-wishlist/);
assert.match(php, /yith_wcwl_add_to_wishlist/);
assert.match(premium, /jofme_wishlist_button_html/);
assert.match(widget, /jofme_wishlist_button_html/);
assert.match(css, /\.jofme-wishlist/);
assert.match(js, /jofme_wishlist_ids/);
assert.match(js, /localStorage/);
console.log('PASS: product card wishlist heart + YITH/TI bridge');
