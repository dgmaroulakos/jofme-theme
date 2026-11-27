/** node .tests/size-variation-gallery.cjs */
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');

const source = fs.readFileSync(path.join(__dirname, '../assets/js/gallery-design.js'), 'utf8');

const primary = {
  attrs: { src: '/parent.jpg', srcset: 'parent 1x', sizes: '100vw' },
  dataset: {},
  getAttribute(k) { return this.attrs[k] || ''; },
  setAttribute(k, v) { this.attrs[k] = v; },
  removeAttribute(k) { delete this.attrs[k]; },
  closest() { return link; },
};
const link = {
  attrs: { href: '/parent-full.jpg' },
  getAttribute(k) { return this.attrs[k] || ''; },
  setAttribute(k, v) { this.attrs[k] = v; },
};
const items = [
  {
    id: 'jofme-gallery-image-1',
    dataset: {},
    classList: { toggle() {}, remove() {}, add() {}, contains() { return false; } },
    scrollIntoView() {},
  },
];
const gallery = {
  dataset: {},
  classList: { add() {}, remove() {}, toggle() {}, contains() { return false; } },
  querySelector(sel) {
    if (String(sel).includes('[data-primary] img')) return primary;
    return null;
  },
  querySelectorAll(sel) {
    if (String(sel).includes('__item')) return items;
    return [];
  },
};
const colorWrap = {
  getAttribute(name) {
    return name === 'data-jofme-gallery-driver' ? '1' : null;
  },
};
const colorSelect = {
  name: 'attribute_pa_color',
  value: '',
  closest(sel) {
    return sel === '.jofme-variation-swatches' ? colorWrap : null;
  },
};
const sizeSelect = {
  name: 'attribute_pa_size',
  value: '',
  closest() { return null; },
};
const sku = { textContent: 'PARENT', dataset: {}, nodeType: 1 };
const formStore = {};
const form = {
  classList: { contains(name) { return name === 'variations_form'; } },
  querySelector() { return null; },
  querySelectorAll(sel) {
    if (sel === '.jofme-variation-swatches select[name^="attribute_"]') return [colorSelect];
    if (sel === 'select[name^="attribute_"]') return [colorSelect, sizeSelect];
    return [];
  },
};
Object.defineProperty(form, 'dataset', {
  configurable: false,
  enumerable: true,
  get() { return formStore; },
});

const variations = [
  {
    attributes: { attribute_pa_color: 'gold', attribute_pa_size: 's' },
    image_id: 11,
    sku: 'GOLD-S',
    image: { src: '/gold-s.jpg', full_src: '/gold-s-full.jpg' },
    jofme_gallery: [{ id: 11, src: '/gold-s-full.jpg', full_src: '/gold-s-full.jpg' }],
  },
  {
    attributes: { attribute_pa_color: 'gold', attribute_pa_size: 'm' },
    image_id: 0,
    sku: 'GOLD-M',
    image: { src: '/parent.jpg', full_src: '/parent-full.jpg' },
    jofme_gallery: [],
  },
  {
    attributes: { attribute_pa_color: 'gold', attribute_pa_size: 'l' },
    image_id: 13,
    sku: 'GOLD-L',
    image: { src: '/gold-l.jpg', full_src: '/gold-l-full.jpg' },
    jofme_gallery: [{ id: 13, src: '/gold-l-full.jpg', full_src: '/gold-l-full.jpg' }],
  },
  {
    attributes: { attribute_pa_color: 'silver', attribute_pa_size: 's' },
    image_id: 22,
    sku: 'SILVER-S',
    image: { src: '/silver.jpg', full_src: '/silver-full.jpg' },
    jofme_gallery: [{ id: 22, src: '/silver-full.jpg', full_src: '/silver-full.jpg' }],
  },
];

const handlers = {};
const bodyStore = {};
const body = {};
Object.defineProperty(body, 'dataset', {
  configurable: false,
  enumerable: true,
  get() { return bodyStore; },
});

const context = {
  window: {
    jQuery() {
      return {
        on(events, selectorOrFn, maybeFn) {
          const fn = typeof selectorOrFn === 'function' ? selectorOrFn : maybeFn;
          if (typeof fn !== 'function') return;
          String(events).split(/\s+/).forEach((event) => {
            handlers[event.split('.')[0]] = fn;
          });
        },
        data(key) {
          return key === 'product_variations' ? variations : null;
        },
      };
    },
    matchMedia: () => ({ matches: true }),
    IntersectionObserver: class { observe() {} },
  },
  document: {
    body,
    addEventListener() {},
    querySelector(sel) {
      if (sel === 'form.variations_form') return form;
      return null;
    },
    querySelectorAll(sel) {
      if (sel === '.jofme-stacked-gallery') return [gallery];
      if (sel === 'form.variations_form') return [form];
      if (sel === '.jofme-product-sku .sku, .product_meta .sku') return [sku];
      return [];
    },
    getElementById() { return null; },
  },
};
context.window.jQuery.fn = {};
vm.runInNewContext(source, context);

function check() {
  handlers.check_variations.call(form);
}
function found(variation) {
  handlers.found_variation.call(form, null, variation);
}

assert.equal(primary.attrs.src, '/parent.jpg');
assert.equal(sku.textContent, 'PARENT');

colorSelect.value = 'gold';
sizeSelect.value = '';
check();
assert.equal(primary.attrs.src, '/gold-s-full.jpg', 'color alone previews that color');
assert.equal(sku.textContent, 'PARENT', 'color preview does not invent a size SKU');

colorSelect.value = '';
sizeSelect.value = 's';
check();
assert.equal(primary.attrs.src, '/parent.jpg', 'size alone keeps parent photos');

colorSelect.value = 'gold';
sizeSelect.value = 'l';
found(variations[2]);
assert.equal(primary.attrs.src, '/gold-l-full.jpg', 'full match uses that size photo');
assert.equal(sku.textContent, 'GOLD-L');

colorSelect.value = 'gold';
sizeSelect.value = 'm';
found(variations[1]);
assert.equal(primary.attrs.src, '/gold-s-full.jpg', 'size without a photo keeps the color gallery');
assert.equal(sku.textContent, 'GOLD-M');
assert.notEqual(primary.attrs.src, '/parent.jpg');

colorSelect.value = 'silver';
sizeSelect.value = 's';
found(variations[3]);
assert.equal(primary.attrs.src, '/silver-full.jpg');
assert.equal(sku.textContent, 'SILVER-S');

colorSelect.value = '';
sizeSelect.value = 'm';
found({
  attributes: { attribute_pa_size: 'm' },
  image_id: 0,
  sku: 'SIZE-M',
  jofme_gallery: [],
  image: { src: '/fallback.jpg', full_src: '/fallback-full.jpg' },
});
assert.equal(primary.attrs.src, '/parent.jpg', 'size variation parent-image fallback does not collapse the gallery');
assert.equal(sku.textContent, 'SIZE-M');

colorSelect.value = '';
sizeSelect.value = '';
handlers.reset_data.call(form);
assert.equal(primary.attrs.src, '/parent.jpg');
assert.equal(sku.textContent, 'PARENT');

console.log('PASS: size variations keep color gallery and do not use the parent image fallback');
