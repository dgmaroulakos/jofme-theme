const vm = require('node:vm');
const fs = require('node:fs');
const assert = require('node:assert/strict');
function run(saved, storageBlocked = false, archive = true) {
 const catalog = {dataset:{}};
 const controls = (archive ? ['1','2','3','4','list'] : []).map(view => ({dataset:{view}, attrs:{}, setAttribute(k,v){this.attrs[k]=v}, addEventListener(k,fn){this.click=fn}}));
 const storage = {value:saved,getItem(){if(storageBlocked) throw Error();return this.value},setItem(k,v){if(storageBlocked)throw Error();this.value=v}};
 const context = {document:{querySelector:()=>catalog,querySelectorAll:()=>controls,getElementById:()=>null,body:{classList:{contains:c=>c==='jofme-default-view-3'}}},localStorage:storage};
 vm.runInNewContext(fs.readFileSync(require('node:path').join(__dirname, '../assets/js/premium-shop.js'),'utf8'),context);
 return {catalog,controls,storage};
}
let test = run('list');
assert.equal(test.catalog.dataset.view,'list');
assert.equal(test.controls[4].attrs['aria-pressed'],'true');
test.controls[1].click();
assert.equal(test.catalog.dataset.view,'2');
assert.equal(test.storage.value,'2');
assert.equal(test.controls.filter(b=>b.attrs['aria-pressed']==='true').length,1);
assert.equal(run('invalid').catalog.dataset.view,'3');
test=run(null,true);test.controls[0].click();assert.equal(test.catalog.dataset.view,'1');
test=run('4');assert.equal(test.catalog.dataset.view,'4');assert.equal(test.controls[3].attrs['aria-pressed'],'true');
test.controls[2].click();assert.equal(test.storage.value,'3');
assert.equal(run('list',false,false).catalog.dataset.view,undefined,'Single-product related items must not inherit archive view');
console.log('PASS: saved view, selected state, view changes, invalid preference, blocked storage');
