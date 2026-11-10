/** node .tests/sticky-header-height.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const customizer = fs.readFileSync(path.join(root, 'inc/customizer.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/main.css'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/main.js'), 'utf8');

assert.match(customizer, /jofme_sticky_header_height/);
assert.match(customizer, /jofme_sticky_header_height_mobile/);
assert.match(customizer, /jofme_sanitize_sticky_header_height/);
assert.match(customizer, /--jofme-header-stuck-h/);
assert.match(customizer, /--jofme-header-stuck-h-mobile/);
assert.match(css, /--jofme-header-stuck-h:\s*100px/);
assert.match(css, /--jofme-header-stuck-h-mobile:\s*64px/);
assert.match(css, /min-height:\s*var\(--jofme-header-stuck-h/);
assert.match(css, /\.site-header\.is-stuck\s*\{[^}]*position:\s*fixed/s);
assert.match(css, /\.site-header-spacer/);
assert.match(js, /site-header-spacer/);
assert.match(js, /setStuck/);
assert.match(js, /transparent \? 0/);
console.log('PASS: stuck header height Customizer + spacer wiring');
