/** node .tests/swatch-title-size.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const swatches = fs.readFileSync(path.join(root, 'assets/css/swatches.css'), 'utf8');
const cards = fs.readFileSync(path.join(root, 'assets/css/product-cards.css'), 'utf8');
const premium = fs.readFileSync(path.join(root, 'inc/premium-shop.php'), 'utf8');

assert.match(swatches, /\.jofme-swatch\.is-color\{[^}]*width:\s*30px[^}]*height:\s*30px/s);
assert.match(swatches, /\.jofme-swatch\.is-image\{[^}]*width:\s*30px[^}]*height:\s*30px/s);
assert.match(swatches, /\.jofme-swatch__image img\{[^}]*object-fit:\s*cover/s);
assert.match(swatches, /\.jofme-filter-swatches \.jofme-swatch\.is-color\{width:\s*30px;height:\s*30px\}/);
assert.match(
	cards,
	/body\.jofme-card-bestsellers\.woocommerce ul\.products li\.product \.woocommerce-loop-product__title\{[^}]*font-size:\s*var\(--jofme-card-title-size,\s*30px\)/s
);
assert.match(premium, /get_theme_mod\(\s*'jofme_card_title_size',\s*30\s*\)/);

console.log('PASS: swatch 30px and bestsellers title 30px');
