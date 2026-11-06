const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');

const source = fs.readFileSync(path.join(__dirname, '../assets/js/main.js'), 'utf8');

function run(options = {}) {
  const scrollY = { value: 0 };
  const listeners = {};
  const body = {
    classList: {
      contains: () => false,
    },
    appendChild() {},
  };
  const context = {
    performance: { now: () => 0 },
    requestAnimationFrame(fn) { return setTimeout(() => fn(16), 0); },
    cancelAnimationFrame(id) { clearTimeout(id); },
    window: {
      jofmeData: {
        scroll: {
          enabled: options.enabled !== false,
          speed: options.speed || 'medium',
        },
      },
      scrollY: 0,
      pageYOffset: 0,
      innerHeight: 800,
      scrollTo(x, y) {
        if (typeof x === 'object') {
          scrollY.value = x.top || 0;
          this.scrollY = scrollY.value;
          return;
        }
        scrollY.value = y;
        this.scrollY = y;
      },
      addEventListener(type, fn) {
        listeners[type] = listeners[type] || [];
        listeners[type].push(fn);
      },
      matchMedia: () => ({ matches: !!options.reduced }),
      getComputedStyle: () => ({
        overflowY: 'visible',
        getPropertyValue: () => '72px',
      }),
    },
    document: {
      body,
      documentElement: { scrollHeight: 4000 },
      querySelector: () => null,
      querySelectorAll: () => [],
      addEventListener() {},
      getElementById: () => null,
    },
    getComputedStyle: () => ({
      overflowY: 'visible',
      getPropertyValue: () => '72px',
    }),
  };
  context.window.document = context.document;
  context.window.performance = context.performance;
  context.window.requestAnimationFrame = context.requestAnimationFrame;
  context.window.cancelAnimationFrame = context.cancelAnimationFrame;
  vm.runInNewContext(source, context);
  return { context, listeners, scrollY };
}

const medium = run({ speed: 'medium' });
assert.equal(typeof medium.context.window.jofmeScrollTo, 'function');
assert.ok(medium.listeners.wheel && medium.listeners.wheel.length === 1, 'wheel smoothing attached');

const reduced = run({ reduced: true });
assert.equal(reduced.listeners.wheel, undefined, 'reduced motion skips wheel smoothing');

const disabled = run({ enabled: false });
assert.equal(disabled.listeners.wheel, undefined, 'disabled scroll skips wheel smoothing');

const slow = run({ speed: 'slow' });
assert.ok(slow.listeners.wheel, 'slow profile still enables smoothing');

console.log('PASS: scroll profiles, reduced motion and disabled state');
