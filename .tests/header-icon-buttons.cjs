/**
 * Header search/menu toggles must not use UA button chrome (white bg / border).
 */
const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.join(__dirname, '..');
const css = fs.readFileSync(path.join(root, 'assets/css/main.css'), 'utf8');

assert.match(
  css,
  /\.jofme-menu-toggle,\s*\n\.jofme-search-toggle\s*\{[^}]*appearance:\s*none/s,
  'menu/search toggles need appearance:none'
);
assert.match(
  css,
  /\.jofme-menu-toggle,\s*\n\.jofme-search-toggle\s*\{[^}]*background:\s*transparent/s,
  'menu/search toggles need transparent background'
);
assert.match(
  css,
  /\.jofme-menu-toggle,\s*\n\.jofme-search-toggle\s*\{[^}]*border:\s*0/s,
  'menu/search toggles need border:0'
);

console.log('PASS: header icon buttons reset UA chrome');
