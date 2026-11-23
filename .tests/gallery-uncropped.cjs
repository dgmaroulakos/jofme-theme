/** node .tests/gallery-uncropped.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'inc/gallery-design.php'), 'utf8');
const woo = fs.readFileSync(path.join(root, 'inc/woocommerce.php'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/gallery-design.js'), 'utf8');

assert.match(php, /function jofme_gallery_uncropped_image/);
assert.match(php, /unset\(\s*\$attr\['srcset'\],\s*\$attr\['sizes'\]\s*\)/);
assert.match(woo, /woocommerce_get_image_size_single/);
assert.match(woo, /\$size\['crop'\]\s*=\s*0/);
assert.match(js, /image\.full_src \|\| image\.src/);
assert.match(js, /primary\.removeAttribute\('srcset'\)/);
assert.match(js, /variationGalleryImages/);
console.log('PASS: gallery serves uncropped full images only');
