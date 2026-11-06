const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');

const source = fs.readFileSync(path.join(__dirname, '../assets/js/gallery-design.js'), 'utf8');

const items = [
  { id: 'jofme-gallery-image-1', dataset: {}, scrollIntoView() { this.scrolled = true; } },
  { id: 'jofme-gallery-image-2', dataset: {}, scrollIntoView() { this.scrolled = true; } },
];
const dots = items.map((item, index) => ({
  dataset: { target: item.id },
  attrs: index === 0 ? { 'aria-current': 'true' } : {},
  setAttribute(k, v) { this.attrs[k] = v; },
  removeAttribute(k) { delete this.attrs[k]; },
  addEventListener(type, fn) { this.click = fn; },
}));
const primary = {
  attrs: { src: '/a.jpg', srcset: 'a 1x', sizes: '100vw' },
  dataset: {},
  getAttribute(k) { return this.attrs[k] || ''; },
  setAttribute(k, v) { this.attrs[k] = v; },
  removeAttribute(k) { delete this.attrs[k]; },
  closest() { return link; },
};
const link = {
  attrs: { href: '/a-full.jpg' },
  getAttribute(k) { return this.attrs[k] || ''; },
  setAttribute(k, v) { this.attrs[k] = v; },
};
const gallery = {
  dataset: {},
  querySelector(sel) {
    if (sel.includes('[data-primary] img')) return primary;
    return null;
  },
  querySelectorAll(sel) {
    if (sel.includes('__item')) return items;
    if (sel.includes('__dot')) return dots;
    return [];
  },
};
const handlers = {};
const form = { querySelector() { return null; } };
const context = {
  window: {
    jQuery(target) {
      return {
        on(events, fn) {
          String(events).split(/\s+/).forEach((event) => { handlers[event] = fn; });
        },
      };
    },
    matchMedia: () => ({ matches: true }),
    IntersectionObserver: class {
      constructor() {}
      observe() {}
    },
  },
  document: {
    addEventListener() {},
    querySelector(sel) {
      if (sel === 'form.variations_form') return form;
      return null;
    },
    querySelectorAll(sel) {
      if (sel === '.jofme-stacked-gallery') return [gallery];
      return [];
    },
    getElementById(id) {
      return items.find((item) => item.id === id) || null;
    },
  },
};
context.window.jQuery.fn = {};

vm.runInNewContext(source, context);

assert.equal(gallery.dataset.jofmeStacked, '1');
assert.equal(primary.dataset.originalSrc, '/a.jpg');
dots[1].click();
assert.equal(items[1].scrolled, true);
assert.equal(dots[1].attrs['aria-current'], 'true');
assert.equal(dots[0].attrs['aria-current'], undefined);

handlers.found_variation(null, { image: { src: '/b.jpg', srcset: 'b 1x', sizes: '50vw', full_src: '/b-full.jpg' } });
assert.equal(primary.attrs.src, '/b.jpg');
assert.equal(link.attrs.href, '/b-full.jpg');

handlers.reset_data();
assert.equal(primary.attrs.src, '/a.jpg');
assert.equal(link.attrs.href, '/a-full.jpg');

console.log('PASS: stacked gallery dots, variation image swap and reset');
