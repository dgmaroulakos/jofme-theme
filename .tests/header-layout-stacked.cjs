/** node .tests/header-layout-stacked.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const customizer = fs.readFileSync(path.join(root, 'inc/customizer.php'), 'utf8');
const header = fs.readFileSync(path.join(root, 'template-parts/header/site-header.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/main.css'), 'utf8');

assert.match(customizer, /'stacked'\s*=>/);
assert.match(customizer, /'editorial'\s*=>/);
assert.match(customizer, /'classic'\s*=>/);
assert.match(customizer, /'centered'\s*=>/);
assert.match(header, /stacked/);
assert.match(header, /main-navigation--stacked/);
assert.match(css, /\.site-header--stacked/);
assert.match(css, /\.main-navigation--stacked/);
console.log('PASS: stacked header layout Customizer + markup wiring');
