/** npm install --no-save playwright pngjs; npx playwright install chromium; node .tests/hover-browser.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {chromium} = require('playwright');
const {PNG} = require('pngjs');
const root = path.resolve(__dirname, '..');
(async () => {
 const browser = await chromium.launch({headless:true, ...(process.env.JOFME_CHROMIUM_PATH ? {executablePath:process.env.JOFME_CHROMIUM_PATH} : {}), args:['--no-sandbox']});
 try {
  const page = await browser.newPage({viewport:{width:1000,height:900}});
  const css = ['main.css','woocommerce.css','premium-shop.css','product-hover.css'].map(file => fs.readFileSync(path.join(root,'assets/css',file),'utf8')).join('\n');
  const image = color => 'data:image/svg+xml,' + encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600"><path fill="${color}" d="M0 0h600v600H0z"/></svg>`);
  await page.setContent(`<style>${css} .woocommerce .jofme-shop ul.products{grid-template-columns:500px!important}</style><div class="woocommerce"><main class="jofme-shop" data-view="3"><ul class="products"><li class="product"><a href="#product" class="woocommerce-loop-product__link"><span class="jofme-card-media"><img src="${image('red')}"><img src="${image('blue')}" class="jofme-hover-image is-ready"></span><h2>Product</h2></a><div class="jofme-card-actions"><button>Quick view</button></div></li></ul></main></div>`);
  await page.waitForFunction(() => [...document.images].every(img => img.complete && img.naturalWidth));
  const media = page.locator('.jofme-card-media');
  const pixel = async () => { const png = PNG.sync.read(await media.screenshot()); const offset = (Math.floor(png.height/2)*png.width+Math.floor(png.width/2))*4; return [...png.data.subarray(offset,offset+3)]; };
  for (const view of ['3','list']) {
   await page.locator('main').evaluate((el,view)=>el.dataset.view=view,view);
   await page.mouse.move(900,850); await page.waitForTimeout(450);
   assert.deepEqual(await pixel(),[255,0,0],`Primary before hover (${view})`);
   await media.hover(); await page.waitForTimeout(500);
   assert.deepEqual(await pixel(),[0,0,255],`Secondary must stay painted after fade ends (${view})`);
   await page.waitForTimeout(1000);
   assert.deepEqual(await pixel(),[0,0,255],`Secondary must stay visible during hover (${view})`);
   await page.locator('.jofme-card-actions button').hover(); await page.waitForTimeout(350);
   assert.deepEqual(await pixel(),[0,0,255],`Hovering actions must preserve secondary (${view})`);
   await page.mouse.move(900,850); await page.waitForTimeout(450);
   assert.deepEqual(await pixel(),[255,0,0],`Primary returns on pointer leave (${view})`);
  }
  console.log('PASS: painted image remains secondary after transition, across grid/list and action hover; returns on leave');
 } finally { await browser.close(); }
})().catch(error => {console.error(error);process.exit(1)});
