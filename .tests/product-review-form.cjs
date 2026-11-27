/** node .tests/product-review-form.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const css = fs.readFileSync(path.join(root, 'assets/css/woocommerce.css'), 'utf8');
const php = fs.readFileSync(path.join(root, 'inc/woocommerce.php'), 'utf8');
const workflow = fs.readFileSync(path.join(root, '.github/workflows/release.yml'), 'utf8');

assert.match(css, /\.woocommerce #review_form #respond/);
assert.match(css, /background:\s*var\(--jofme-color-surface,\s*#fffdf9\)/);
assert.match(css, /\.woocommerce #review_form \.comment-form/);
assert.match(css, /grid-template-columns:\s*1fr 1fr/);
assert.match(css, /\.woocommerce #review_form p\.stars a::before/);
assert.match(css, /content:\s*"\\e020"/);
assert.match(css, /\.woocommerce #review_form #respond \.form-submit \.jofme-review-submit/);
assert.match(css, /\.woocommerce #reviews \.comment_container/);
assert.match(css, /@media \(max-width: 767px\)[\s\S]*?\.woocommerce #review_form \.comment-form \{\s*grid-template-columns:\s*1fr/);

assert.match(php, /function jofme_product_review_comment_form_args/);
assert.match(php, /woocommerce_product_review_comment_form_args/);
assert.match(php, /Γράψε την αξιολόγησή σου/);
assert.match(php, /Υποβολή αξιολόγησης/);
assert.match(php, /jofme-review-submit/);
assert.match(php, /submit_button/);

assert.match(workflow, /product-review-form\.cjs/);

console.log('PASS: product review form UX/UI styles + comment form args');
