/** node .tests/header-width.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const customizer = fs.readFileSync(path.join(root, 'inc/customizer.php'), 'utf8');
const setup = fs.readFileSync(path.join(root, 'inc/setup.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/layout.css'), 'utf8');

assert.match(customizer, /jofme_header_width/);
assert.match(customizer, /'boxed'\s*=>/);
assert.match(customizer, /'wide'\s*=>/);
assert.match(customizer, /'narrow'\s*=>/);
assert.match(customizer, /'full'\s*=>/);
assert.match(customizer, /'content'\s*=>/);
assert.match(setup, /jofme-header-width-/);
assert.match(css, /--jofme-header-width/);
assert.match(css, /\.jofme-header-width-wide/);
assert.match(css, /\.jofme-header-width-boxed/);
console.log('PASS: header width Customizer (boxed/wide/narrow/full/content)');
