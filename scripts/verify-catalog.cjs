const vm = require('node:vm');
const fs = require('node:fs');
const assert = require('node:assert/strict');
function run(saved, storageBlocked = false, archive = true, mobile = false) {
 const catalog = { dataset: {}, contains() { return true; } };
 const controls = (archive ? ['1', '2', '3', '4', 'list'] : []).map((view) => ({
  dataset: { view },
  attrs: {},
  setAttribute(k, v) { this.attrs[k] = v; },
  closest(sel) {
   return String(sel).includes('jofme-view') ? this : null;
  },
 }));
 const storage = {
  value: saved,
  getItem() { if (storageBlocked) throw Error(); return this.value; },
  setItem(k, v) { if (storageBlocked) throw Error(); this.value = v; },
 };
 const listeners = { click: [], submit: [], change: [] };
 const context = {
  document: {
   querySelector: (sel) => {
    if (sel === '.jofme-shop') return catalog;
    if (sel === '.jofme-shop-sidebar-panel' || sel === '.jofme-shop-sidebar-toggle') return null;
    if (String(sel).includes('jofme-catalog-results') || String(sel).includes('jofme-ajax-filters')) return null;
    return catalog;
   },
   querySelectorAll: (sel) => {
    if (String(sel).includes('jofme-view') || sel === '.jofme-view button') return controls;
    if (String(sel).includes('jofme-term-description')) return [];
    return [];
   },
   getElementById: () => null,
   body: { classList: { contains: (c) => c === 'jofme-default-view-3' } },
   addEventListener: (type, fn) => {
    if (!listeners[type]) listeners[type] = [];
    listeners[type].push(fn);
   },
  },
  localStorage: storage,
  window: {
   matchMedia: (query) => ({ matches: mobile && String(query).includes('767') }),
   addEventListener() {},
   history: { pushState() {} },
   jofmeShop: { ajaxFilters: true },
  },
  DOMParser: function DOMParser() {
   this.parseFromString = () => ({ querySelector: () => null });
  },
  AbortController: function AbortController() {
   this.signal = {};
   this.abort = () => {};
  },
  fetch: async () => ({ ok: true, text: async () => '' }),
 };
 controls.forEach((button) => {
  button.click = () => {
   const event = {
    target: button,
    preventDefault() {},
    stopPropagation() {},
   };
   (listeners.click || []).forEach((fn) => fn(event));
  };
 });
 vm.runInNewContext(fs.readFileSync(require('node:path').join(__dirname, '../assets/js/premium-shop.js'), 'utf8'), context);
 return { catalog, controls, storage, listeners };
}
let test = run('list');
assert.equal(test.catalog.dataset.view, 'list');
assert.equal(test.controls[4].attrs['aria-pressed'], 'true');
test.controls[1].click();
assert.equal(test.catalog.dataset.view, '2');
assert.equal(test.storage.value, '2');
assert.equal(test.controls.filter((b) => b.attrs['aria-pressed'] === 'true').length, 1);
assert.equal(run('invalid').catalog.dataset.view, '3');
test = run(null, true); test.controls[0].click(); assert.equal(test.catalog.dataset.view, '1');
test = run('4'); assert.equal(test.catalog.dataset.view, '4'); assert.equal(test.controls[3].attrs['aria-pressed'], 'true');
test.controls[2].click(); assert.equal(test.storage.value, '3');
assert.equal(run('list', false, false).catalog.dataset.view, undefined, 'Single-product related items must not inherit archive view');
test = run('4', false, true, true); assert.equal(test.catalog.dataset.view, '2', 'Mobile clamps 3/4/list down to 2 columns');
assert.ok(test.listeners.click.length > 0, 'delegated click listeners registered');
console.log('PASS: saved view, selected state, view changes, invalid preference, blocked storage');
