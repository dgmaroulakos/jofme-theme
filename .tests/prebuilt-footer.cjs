/** node .tests/prebuilt-footer.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'inc/footer.php'), 'utf8');
const tpl = fs.readFileSync(path.join(root, 'template-parts/footer/site-footer.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/main.css'), 'utf8');
const boot = fs.readFileSync(path.join(root, 'functions.php'), 'utf8');

assert.match(boot, /\/inc\/footer\.php/);
assert.match(php, /jofme_footer_layout/);
assert.match(php, /brand_right/);
assert.match(php, /footer_col_1/);
assert.match(php, /jofme_footer_brand_column/);
assert.match(php, /jofme_footer_contact/);
assert.match(tpl, /site-footer__grid--brand-right/);
assert.match(tpl, /jofme_footer_brand_column/);
assert.match(css, /\.site-footer__grid--brand-right/);
assert.match(css, /\.site-footer__brand/);
assert.match(css, /\.site-footer__contact/);
console.log('PASS: prebuilt footer 3 cols + brand/contact right');
