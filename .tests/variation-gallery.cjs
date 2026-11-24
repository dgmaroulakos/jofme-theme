/** node .tests/variation-gallery.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'inc/gallery-design.php'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/gallery-design.js'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/css/gallery-design.css'), 'utf8');
const workflow = fs.readFileSync(path.join(root, '.github/workflows/release.yml'), 'utf8');

assert.match(php, /function jofme_available_variation_gallery/);
assert.match(php, /woocommerce_available_variation/);
assert.match(php, /_wc_additional_variation_images/);
assert.match(php, /\$data\['jofme_gallery'\]/);
assert.match(js, /showParentStackedGallery/);
assert.match(js, /showVariationStackedGallery/);
assert.match(js, /showParentCarouselGallery/);
assert.match(js, /showVariationCarouselGallery/);
assert.match(js, /found_variation/);
assert.match(js, /reset_data hide_variation/);
assert.match(js, /is-variation-gallery/);
assert.match(css, /\.jofme-gallery-item--suppressed/);
assert.match(workflow, /variation-gallery\.cjs/);
console.log('PASS: variation gallery featured/select/clear wiring');
