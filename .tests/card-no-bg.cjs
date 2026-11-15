/** node .tests/card-no-bg.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const cards = fs.readFileSync(path.join(root, 'assets/css/product-cards.css'), 'utf8');
const gallery = fs.readFileSync(path.join(root, 'assets/css/gallery-design.css'), 'utf8');
const woo = fs.readFileSync(path.join(root, 'assets/css/woocommerce.css'), 'utf8');
const hover = fs.readFileSync(path.join(root, 'assets/css/product-hover.css'), 'utf8');
const premium = fs.readFileSync(path.join(root, 'assets/css/premium-shop.css'), 'utf8');

assert.match(cards, /\.jofme-card-media\{[^}]*background:\s*transparent/s);
assert.match(cards, /\.jofme-bestsellers__media\{[^}]*background:\s*transparent/s);
assert.match(gallery, /jofme-card-gallery[\s\S]*?li\.product\{[^}]*background:\s*transparent/s);
assert.match(woo, /ul\.products li\.product img\s*\{[^}]*background:\s*transparent/s);
assert.match(hover, /\.jofme-card-media\{[^}]*background:\s*transparent/s);
assert.match(premium, /\.jofme-card-framed[\s\S]*?background:\s*transparent/);
assert.doesNotMatch(woo, /ul\.products li\.product img\s*\{[^}]*background:\s*#f0ebe4/s);
console.log('PASS: product cards use transparent backgrounds');
