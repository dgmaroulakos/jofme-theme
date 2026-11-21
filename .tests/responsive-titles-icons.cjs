/** node .tests/responsive-titles-icons.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');

const premium = fs.readFileSync(path.join(root, 'inc/premium-shop.php'), 'utf8');
const wishlist = fs.readFileSync(path.join(root, 'inc/wishlist.php'), 'utf8');
const cards = fs.readFileSync(path.join(root, 'assets/css/product-cards.css'), 'utf8');
const shop = fs.readFileSync(path.join(root, 'assets/css/premium-shop.css'), 'utf8');
const wishCss = fs.readFileSync(path.join(root, 'assets/css/wishlist.css'), 'utf8');
const best = fs.readFileSync(path.join(root, 'assets/css/bestsellers-tabs.css'), 'utf8');

assert.match(premium, /jofme_card_title_size/);
assert.match(premium, /jofme_card_icon_size/);
assert.match(premium, /--jofme-card-title-size/);
assert.match(premium, /--jofme-card-icon-size/);
assert.match(wishlist, /jofme_wishlist_card_layout/);
assert.match(wishlist, /function jofme_wishlist_card_layout/);
assert.match(wishlist, /function jofme_product_card_layout_choices/);
assert.match(wishlist, /Same as shop product cards/);
assert.match(cards, /--jofme-card-title-size/);
assert.match(cards, /--jofme-card-title-size-mobile/);
assert.match(cards, /\.jofme-bestsellers__name\{[^}]*--jofme-card-title-size/);
assert.match(shop, /--jofme-card-icon-size/);
assert.match(shop, /--jofme-card-icon-svg/);
assert.match(wishCss, /--jofme-card-icon-size/);
assert.match(best, /\.jofme-bestsellers__title\{font-size:clamp/);

console.log('PASS: responsive titles/icons + wishlist card layout Customizer');
