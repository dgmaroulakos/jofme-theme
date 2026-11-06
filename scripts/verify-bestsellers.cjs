const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');

const source = fs.readFileSync(path.join(__dirname, '../assets/js/bestsellers-tabs.js'), 'utf8');

function makeRoot() {
  const tabs = ['all', 'rings'].map((id, index) => ({
    id: `tab-${id}`,
    role: 'tab',
    dataset: {},
    attrs: {
      'aria-selected': index === 0 ? 'true' : 'false',
      'aria-controls': `panel-${id}`,
      tabindex: index === 0 ? '0' : '-1',
    },
    tabIndex: index === 0 ? 0 : -1,
    getAttribute(name) { return this.attrs[name]; },
    setAttribute(name, value) { this.attrs[name] = String(value); if (name === 'tabindex') this.tabIndex = Number(value); },
    focus() { this.focused = true; },
    closest(sel) { return sel === '[role="tab"]' ? this : null; },
  }));
  const panels = ['all', 'rings'].map((id, index) => ({
    id: `panel-${id}`,
    role: 'tabpanel',
    hidden: index !== 0,
    removeAttribute(name) { if (name === 'hidden') this.hidden = false; },
    setAttribute(name) { if (name === 'hidden') this.hidden = true; },
  }));
  const root = {
    dataset: {},
    contains(node) { return tabs.includes(node) || panels.includes(node); },
    querySelectorAll(sel) {
      if (sel === '[role="tab"]') return tabs;
      if (sel === '[role="tabpanel"]') return panels;
      return [];
    },
    addEventListener(type, fn) { this['on' + type] = fn; },
  };
  return { root, tabs, panels };
}

const { root, tabs, panels } = makeRoot();
const context = {
  window: {},
  document: {
    querySelectorAll(sel) {
      return sel === '[data-jofme-bestsellers]' ? [root] : [];
    },
    addEventListener() {},
  },
};
vm.runInNewContext(source, context);

assert.equal(root.dataset.jofmeBestsellersBound, '1');
root.onclick({ target: tabs[1], closest(sel) { return tabs[1].closest(sel); } });
assert.equal(tabs[1].attrs['aria-selected'], 'true');
assert.equal(tabs[0].attrs['aria-selected'], 'false');
assert.equal(panels[1].hidden, false);
assert.equal(panels[0].hidden, true);

root.onkeydown({
  key: 'ArrowRight',
  target: tabs[1],
  preventDefault() { this.prevented = true; },
  closest(sel) { return tabs[1].closest(sel); },
});
assert.equal(tabs[0].attrs['aria-selected'], 'true');
assert.equal(tabs[0].focused, true);

console.log('PASS: bestsellers tab activation and keyboard navigation');
