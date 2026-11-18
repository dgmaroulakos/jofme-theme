/** node .tests/bestsellers-card-media-padding.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const cards = fs.readFileSync(path.join(__dirname, '..', 'assets/css/product-cards.css'), 'utf8');

assert.match(
	cards,
	/body\.jofme-card-bestsellers\.woocommerce ul\.products li\.product \.jofme-card-media img\{[^}]*padding:\s*0[^}]*\}/s
);
assert.match(
	cards,
	/body\.jofme-card-bestsellers\.woocommerce ul\.products li\.product \.woocommerce-loop-product__link>img\{[^}]*padding:\s*0[^}]*\}/s
);
assert.match(cards, /\.jofme-bestsellers__media>img\{[^}]*padding:\s*0[^}]*\}/s);
assert.match(cards, /\.jofme-bestsellers__media \.jofme-card-media img\{[^}]*padding:\s*0[^}]*\}/s);
assert.doesNotMatch(cards, /jofme-bestsellers[\s\S]{0,200}padding:\s*clamp/s);
assert.doesNotMatch(
	cards,
	/body\.jofme-card-bestsellers\.woocommerce ul\.products li\.product \.jofme-card-media img\{[^}]*padding:\s*clamp/s
);

const mobile = cards.match(/@media\(max-width:767px\)\{([\s\S]*?)\n\}/);
assert.ok(mobile, 'mobile block exists');
assert.doesNotMatch(mobile[1], /jofme-card-media img\{[^}]*padding:\s*12px/);
assert.doesNotMatch(mobile[1], /\.jofme-bestsellers__media>img\{?[^}]*padding:\s*12px/);
assert.doesNotMatch(
	mobile[1],
	/woocommerce-loop-product__link>img,\s*\n?\s*\.jofme-bestsellers__media>img/
);

console.log('PASS: bestsellers card image padding is 0 (no clamp)');
