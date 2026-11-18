/** node .tests/bestsellers-attribute-query.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const php = fs.readFileSync(
	path.join(__dirname, '..', 'inc/widgets/class-bestsellers-tabs.php'),
	'utf8'
);

assert.match(php, /attribute_term_id/);
assert.match(php, /get_attribute_term_options\s*\(/);
assert.match(php, /tax_query/);
assert.match(php, /0 === strpos\(\s*\(string\) \$attr_term->taxonomy,\s*'pa_'/);
assert.match(php, /query_products\(\s*\$tab\['category'\],\s*\$limit,\s*\$orderby,\s*\$tab\['attribute'\]/);

console.log('PASS: bestsellers tabs support query by attribute');
