/** node .tests/languages-el.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');

const setup = fs.readFileSync(path.join(root, 'inc/setup.php'), 'utf8');
const style = fs.readFileSync(path.join(root, 'style.css'), 'utf8');
const pack = fs.readFileSync(path.join(root, 'scripts/package.py'), 'utf8');
const build = fs.readFileSync(path.join(root, 'scripts/build-el-po.py'), 'utf8');
const workflow = fs.readFileSync(path.join(root, '.github/workflows/release.yml'), 'utf8');

assert.match(setup, /load_theme_textdomain\(\s*'jofme'\s*,\s*JOFME_DIR\s*\.\s*'\/languages'\s*\)/);
assert.match(style, /Domain Path:\s*\/languages/);
assert.match(pack, /\.mo/);
assert.match(pack, /\.po/);
assert.match(build, /jofme-el\.po/);
assert.match(workflow, /languages-el\.cjs/);

for (const name of ['jofme.pot', 'jofme-el.po', 'jofme-el.mo', 'el.po', 'el.mo']) {
	const file = path.join(root, 'languages', name);
	assert.ok(fs.existsSync(file), `missing ${name}`);
	assert.ok(fs.statSync(file).size > 100, `${name} too small`);
}

const po = fs.readFileSync(path.join(root, 'languages/jofme-el.po'), 'utf8');
assert.match(po, /Language: el/);
assert.match(po, /Η σελίδα δεν βρέθηκε/);
assert.match(po, /Συνέχεια αγορών/);
assert.match(po, /Μπήκε στο καλάθι/);
assert.match(po, /Wishlist/);
assert.doesNotMatch(po, /^#, fuzzy$/m);

console.log('PASS: Greek el po/mo language packs');
