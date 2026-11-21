/** node .tests/wishlist-page.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');

const php = fs.readFileSync(path.join(root, 'inc/wishlist.php'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/wishlist.js'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/wishlist.css'), 'utf8');
const header = fs.readFileSync(path.join(root, 'template-parts/header/site-header.php'), 'utf8');
const editorial = fs.readFileSync(path.join(root, 'template-parts/header/editorial-header.php'), 'utf8');
const heart = fs.readFileSync(path.join(root, '.tests/wishlist-heart.cjs'), 'utf8');
const workflow = fs.readFileSync(path.join(root, '.github/workflows/release.yml'), 'utf8');

assert.match(php, /add_shortcode\(\s*'jofme_wishlist'/);
assert.match(php, /function jofme_wishlist_page_url/);
assert.match(php, /function jofme_wishlist_ensure_page/);
assert.match(php, /function jofme_wishlist_link/);
assert.match(php, /function jofme_wishlist_ajax_products/);
assert.match(php, /wp_ajax_nopriv_jofme_wishlist_products/);
assert.match(php, /jofme_wishlist_page_id/);
assert.match(php, /dropdown-pages/);
assert.match(php, /jofme_wishlist_card_layout/);
assert.match(php, /function jofme_wishlist_card_layout/);
assert.match(php, /data-jofme-wishlist-page/);
assert.match(js, /jofme_wishlist_products/);
assert.match(js, /data-jofme-wishlist-page/);
assert.match(js, /data-jofme-wishlist-count/);
assert.match(css, /\.jofme-wishlist-page/);
assert.match(css, /\.jofme-wishlist-link/);
assert.match(header, /jofme_wishlist_link/);
assert.match(editorial, /jofme_wishlist_link/);
assert.match(heart, /jofme_wishlist_ids/);
assert.match(workflow, /wishlist-heart\.cjs/);
assert.match(workflow, /wishlist-page\.cjs/);

console.log('PASS: wishlist page shortcode, header link, AJAX grid, CI coverage');
