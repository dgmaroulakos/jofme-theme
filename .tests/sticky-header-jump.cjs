/** node .tests/sticky-header-jump.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const css = fs.readFileSync(path.join(root, 'assets/css/main.css'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/main.js'), 'utf8');

assert.match(css, /\.site-header\.is-stuck\s*\{[^}]*position:\s*fixed/s);
assert.match(css, /min-height:\s*var\(--jofme-header-stuck-h/);
assert.doesNotMatch(css, /\.site-header\.is-stuck\s+\.site-header__inner\s*\{[^}]*min-height:\s*0/s);
assert.match(js, /site-header-spacer/);
assert.match(js, /y > 24/);
assert.match(js, /y < 8/);
console.log('PASS: sticky header uses fixed+spacer and scroll hysteresis');
