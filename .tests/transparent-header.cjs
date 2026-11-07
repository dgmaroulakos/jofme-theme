/** node .tests/transparent-header.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const editorial = fs.readFileSync(path.join(root, 'inc/editorial.php'), 'utf8');
const tags = fs.readFileSync(path.join(root, 'inc/template-tags.php'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/editorial-header.css'), 'utf8');
const typo = fs.readFileSync(path.join(root, 'inc/typography.php'), 'utf8');

assert.match(editorial, /jofme_transparent_logo/);
assert.match(editorial, /jofme_transparent_header_color/);
assert.match(editorial, /jofme-has-transparent-logo/);
assert.match(tags, /custom-logo--transparent/);
assert.match(css, /--jofme-transparent-header-color/);
assert.match(css, /custom-logo--transparent/);
assert.match(typo, /jofme_nav_font/);
assert.match(typo, /jofme_nav_font_size/);
assert.match(typo, /--jofme-nav-size/);
console.log('PASS: transparent logo/color and menu typography wiring');
