/** node .tests/tabs-bg.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const woo = fs.readFileSync(path.join(root, 'assets/css/woocommerce.css'), 'utf8');
const main = fs.readFileSync(path.join(root, 'assets/css/main.css'), 'utf8');
const customizer = fs.readFileSync(path.join(root, 'inc/customizer.php'), 'utf8');

assert.match(woo, /\.woocommerce-tabs\.wc-tabs-wrapper/);
assert.match(woo, /--jofme-tabs-bg,\s*#111111/);
assert.match(woo, /--jofme-tabs-text,\s*#ffffff/);
assert.match(woo, /--jofme-tabs-active-bg,\s*#111111/);
assert.match(woo, /--jofme-tabs-active-text,\s*#ffffff/);
assert.match(woo, /li\.active a[\s\S]*?background:\s*var\(--jofme-tabs-active-bg,\s*#111111\)/);
assert.match(woo, /--jofme-tabs-padding-x,\s*15%/);
assert.match(woo, /body\.jofme-tabs-pill/);
assert.match(woo, /body\.jofme-tabs-boxed/);
assert.match(main, /--jofme-color-bg:\s*#e8caaf/);
assert.match(customizer, /jofme_color_background.=>\s*array\(\s*'#e8caaf'/);
assert.match(customizer, /get_theme_mod\(\s*'jofme_color_background',\s*'#e8caaf'\s*\)/);
assert.match(customizer, /jofme_product_tabs/);
assert.match(customizer, /jofme_tabs_style/);
assert.match(customizer, /'default'\s*=>\s*'pill'/);
assert.match(customizer, /jofme_tabs_bg/);
assert.match(customizer, /jofme_tabs_active_bg'\s*=>\s*array\(\s*'#111111'/);
assert.match(customizer, /--jofme-tabs-bg:/);
assert.match(customizer, /--jofme-tabs-active-bg:/);
console.log('PASS: product tabs black/white defaults + active black bg / white text');
