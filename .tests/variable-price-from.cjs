/**
 * Variable products: price range → "From {min}" only (no second/max price).
 */
const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.join(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'inc/woocommerce.php'), 'utf8');

assert.match(php, /function jofme_format_price_range_from_only/);
assert.match(php, /woocommerce_format_price_range/);
assert.match(php, /From %s/);
assert.match(php, /woocommerce/);
assert.doesNotMatch(
  php,
  /jofme_format_price_range_from_only[\s\S]{0,400}%1\$s.*%2\$s/,
  'must not keep from–to range format'
);

console.log('PASS: variable price shows From min only');
