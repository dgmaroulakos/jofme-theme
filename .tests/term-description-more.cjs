/** node .tests/term-description-more.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'inc/premium-shop.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/premium-shop.css'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/premium-shop.js'), 'utf8');

assert.match(php, /function jofme_render_term_description/);
assert.match(php, /function jofme_taxonomy_archive_description/);
assert.match(php, /\$limit = 20/);
assert.match(php, /jofme-term-description__more/);
assert.match(php, /remove_action\(\s*'woocommerce_archive_description',\s*'woocommerce_taxonomy_archive_description'/);
assert.match(php, /'more'\s*=>/);
assert.match(php, /'less'\s*=>/);
assert.match(css, /\.jofme-term-description__preview/);
assert.match(css, /max-width:767px/);
assert.match(css, /\.jofme-term-description:not\(\.is-expanded\) \.jofme-term-description__full/);
assert.match(js, /\.jofme-term-description/);
assert.match(js, /is-expanded/);
assert.match(js, /jofmeShop\.less/);
console.log('PASS: mobile term-description More toggle wiring');
