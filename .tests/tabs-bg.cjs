/** node .tests/tabs-bg.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const woo = fs.readFileSync(path.join(root, 'assets/css/woocommerce.css'), 'utf8');
const main = fs.readFileSync(path.join(root, 'assets/css/main.css'), 'utf8');
const customizer = fs.readFileSync(path.join(root, 'inc/customizer.php'), 'utf8');

assert.match(woo, /\.woocommerce-tabs\.wc-tabs-wrapper/);
assert.match(woo, /padding:\s*0%\s*15%/);
assert.match(main, /--jofme-color-bg:\s*#e8caaf/);
assert.match(customizer, /jofme_color_background.=>\s*array\(\s*'#e8caaf'/);
assert.match(customizer, /get_theme_mod\(\s*'jofme_color_background',\s*'#e8caaf'\s*\)/);
console.log('PASS: product tabs padding and theme background #e8caaf');
