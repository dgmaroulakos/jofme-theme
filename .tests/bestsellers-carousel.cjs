/** node .tests/bestsellers-carousel.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const php = fs.readFileSync(
	path.join(__dirname, '..', 'inc/widgets/class-bestsellers-tabs.php'),
	'utf8'
);
const css = fs.readFileSync(
	path.join(__dirname, '..', 'assets/css/bestsellers-tabs.css'),
	'utf8'
);
const js = fs.readFileSync(
	path.join(__dirname, '..', 'assets/js/bestsellers-tabs.js'),
	'utf8'
);

assert.match(php, /'layout'/);
assert.match(php, /'carousel'\s*=>/);
assert.match(php, /carousel_slides/);
assert.match(php, /max'\s*=>\s*48/);
assert.match(php, /min\(\s*48,/);
assert.match(php, /data-jofme-bestsellers-carousel/);
assert.match(php, /tabs_style/);
assert.match(php, /jofme-bestsellers--tabs-/);
assert.match(css, /\.jofme-bestsellers__carousel/);
assert.match(css, /\.jofme-bestsellers__track/);
assert.match(css, /\.jofme-bestsellers--tabs-pill/);
assert.match(js, /bindCarousel/);
assert.match(js, /data-jofme-bestsellers-carousel/);

console.log('PASS: bestsellers tabs carousel + higher product limit + tab styles');
