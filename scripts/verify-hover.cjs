const vm = require('node:vm');
const fs = require('node:fs');
const assert = require('node:assert/strict');
class Image {
 constructor(complete, width, secondary = true) { this.complete = complete; this.naturalWidth = width; this.secondary = secondary; this.ready = false; this.classList = {toggle: (_, ready) => {this.ready = ready}}; }
 matches() { return this.secondary; }
}
const cached = new Image(true, 600), pending = new Image(false, 0), failed = new Image(true, 0), unrelated = new Image(true, 200, false);
const handlers = {};
const document = {querySelectorAll: () => [cached,pending,failed],addEventListener: (event, callback, options) => {handlers[event] = {callback, options}}};
const window = {addEventListener: (event, callback) => {handlers['window:' + event] = {callback}}};
vm.runInNewContext(fs.readFileSync(require('node:path').join(__dirname, '../assets/js/product-hover.js'), 'utf8'), {document, window, HTMLImageElement: Image});
assert.equal(cached.ready, true); assert.equal(pending.ready, false); assert.equal(failed.ready, false);
pending.complete = true; pending.naturalWidth = 300; handlers.load.callback({target: pending}); assert.equal(pending.ready, true);
pending.naturalWidth = 0; handlers.error.callback({target: pending}); assert.equal(pending.ready, false);
handlers.load.callback({target: unrelated}); assert.equal(unrelated.ready, false);
assert.ok(handlers['window:load']); assert.ok(handlers.scroll);
console.log('PASS: cached, pending, failed and loaded secondary images; unrelated images unchanged');
