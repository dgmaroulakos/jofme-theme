/** npm install --no-save playwright pngjs; npx playwright install chromium; node .tests/bestsellers-card-hover.cjs */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {chromium} = require('playwright');
const {PNG} = require('pngjs');
const root = path.resolve(__dirname, '..');
(async () => {
 const browser = await chromium.launch({
  headless: true,
  ...(process.env.JOFME_CHROMIUM_PATH ? {executablePath: process.env.JOFME_CHROMIUM_PATH} : {}),
  args: ['--no-sandbox'],
 });
 try {
  const page = await browser.newPage({viewport: {width: 900, height: 800}});
  const css = ['main.css', 'woocommerce.css', 'premium-shop.css', 'product-hover.css', 'product-cards.css']
   .map((file) => fs.readFileSync(path.join(root, 'assets/css', file), 'utf8'))
   .join('\n');
  const image = (color, label) =>
   'data:image/svg+xml,' +
   encodeURIComponent(
    `<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600"><rect width="600" height="600" fill="${color}"/><text x="300" y="310" text-anchor="middle" font-size="48" fill="#111">${label}</text></svg>`
   );
  await page.setContent(`<!DOCTYPE html><html><head><style>
:root{--jofme-color-text:#1a1a1a;--jofme-color-text-muted:#777;--jofme-color-surface:#f0ebe4;--jofme-color-accent:#8a6a4a;--jofme-font-body:system-ui;--jofme-transition:.3s ease}
${css}
body{margin:0;width:1100px}
.woocommerce .jofme-shop[data-view] ul.products{display:grid!important;grid-template-columns:320px!important;width:320px!important;gap:0!important;margin:24px!important;padding:0!important;list-style:none}
.woocommerce ul.products li.product{width:100%!important;float:none!important;margin:0!important}
</style></head>
<body class="jofme-card-bestsellers woocommerce">
<main class="jofme-shop" data-view="1">
<ul class="products">
<li class="product">
<a href="#product" class="woocommerce-loop-product__link">
<span class="onsale">-20%</span>
<span class="jofme-card-media">
<img src="${image('#e8dfd4', 'A')}" alt="Primary" width="600" height="600">
<img src="${image('#6b8f71', 'B')}" class="jofme-hover-image is-ready" alt="" width="600" height="600">
</span>
<span class="jofme-card-category">Rings</span>
<h2 class="woocommerce-loop-product__title">Sample Ring</h2>
<span class="price"><span>96€</span></span>
</a>
</li>
</ul>
</main>
</body></html>`);
  await page.waitForFunction(() => [...document.images].every((img) => img.complete && img.naturalWidth));

  const measure = await page.evaluate(() => {
   const media = document.querySelector('.jofme-card-media');
   const imgs = [...media.querySelectorAll('img')];
   const title = document.querySelector('.woocommerce-loop-product__title');
   const mediaBox = media.getBoundingClientRect();
   const titleBox = title.getBoundingClientRect();
   const img0 = imgs[0].getBoundingClientRect();
   return {
    display: getComputedStyle(media).display,
    mediaHeight: mediaBox.height,
    mediaWidth: mediaBox.width,
    img0Top: img0.top,
    img1Top: imgs[1].getBoundingClientRect().top,
    img0Height: img0.height,
    img0Width: img0.width,
    titleGap: titleBox.top - mediaBox.bottom,
    imgPosition: getComputedStyle(imgs[0]).position,
   };
  });

  assert.equal(measure.display, 'block', 'Card media uses a positioned block frame');
  assert.equal(measure.imgPosition, 'absolute', 'Images must be absolutely overlaid in the frame');
  assert.ok(Math.abs(measure.img0Top - measure.img1Top) < 1, 'Primary and hover images must share the same top edge');
  assert.ok(measure.mediaHeight > 250 && measure.mediaHeight < 420, `Media height should be one square cell, got ${measure.mediaHeight}`);
  assert.ok(Math.abs(measure.img0Height - measure.mediaHeight) < 1, `Image height must match media frame, got ${measure.img0Height} vs ${measure.mediaHeight}`);
  assert.ok(Math.abs(measure.img0Width - measure.mediaWidth) < 1, `Image width must match media frame, got ${measure.img0Width} vs ${measure.mediaWidth}`);
  assert.ok(measure.titleGap >= 0 && measure.titleGap < 80, `Title must sit just under media, gap=${measure.titleGap}`);

  const clip = await page.evaluate(() => {
   const r = document.querySelector('.jofme-card-media').getBoundingClientRect();
   return {x: r.x, y: r.y, width: r.width, height: r.height};
  });
  const sample = async () => {
   const png = PNG.sync.read(await page.screenshot({clip}));
   const offset = (Math.floor(png.height / 2) * png.width + Math.floor(png.width / 2)) * 4;
   return [...png.data.subarray(offset, offset + 3)];
  };

  await page.mouse.move(800, 700);
  await page.waitForTimeout(350);
  const before = await sample();
  // Primary beige #e8dfd4 — sample away from SVG letter by using center of contain box.
  assert.ok(before[0] > 200 && before[1] > 190 && before[2] > 180, `Primary visible before hover: ${before}`);

  await page.locator('.jofme-card-media').hover();
  await page.waitForTimeout(450);
  const after = await sample();
  // Hover green #6b8f71
  assert.ok(after[1] > after[0] && after[1] > 100 && after[0] < 150, `Secondary green visible on hover: ${after}`);

  const outDir = process.env.JOFME_ARTIFACT_DIR || '/opt/cursor/artifacts';
  fs.mkdirSync(outDir, {recursive: true});
  await page.mouse.move(800, 700);
  await page.waitForTimeout(300);
  await page.screenshot({path: path.join(outDir, 'bestsellers-card-rest.png'), fullPage: true});
  await page.locator('.jofme-card-media').hover();
  await page.waitForTimeout(300);
  await page.screenshot({path: path.join(outDir, 'bestsellers-card-hover.png'), fullPage: true});

  console.log('PASS: bestsellers card media overlays correctly; hover swap works; no blank gap');
  console.log(JSON.stringify(measure));
 } finally {
  await browser.close();
 }
})().catch((error) => {
 console.error(error);
 process.exit(1);
});
