// Exercise the production form placement against phone and keyboard viewports.
// Geometry checks only: these do not replace a real-device visual review.
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import vm from 'node:vm';

const source = await readFile(new URL('../public/assets/js/book-preview.js', import.meta.url), 'utf8');
const start = source.indexOf('        const placeBookmarkOnPage=');
const end = source.indexOf('        let bookmarkResizeFrame=', start);
assert.ok(start >= 0 && end > start);
let checked = 0;
for (const width of [320, 360, 390, 430, 768]) {
    for (const [height, offsetTop] of [[640, 0], [844, 0], [300, 0], [280, 160]]) {
        for (const pageTop of [-400, 180, 950]) {
            const properties = new Map();
            const pageWidth = Math.min(width - 48, 390);
            const rect = { left: (width - pageWidth) / 2, top: pageTop, width: pageWidth, height: 440 };
            rect.right = rect.left + rect.width;
            const page = { getBoundingClientRect: () => rect, classList: { contains: () => true } };
            const context = {
                bookmarkDialog: { style: { setProperty: (key, value) => properties.set(key, parseFloat(value)) } },
                bookmarkOpener: null, settledRight: page, settledLeft: page, stage: page,
                mode: 'mobile', currentState: 1,
                document: { documentElement: { clientWidth: width } },
                window: { innerWidth: width, innerHeight: 844, visualViewport: { height, offsetTop } },
                clampNumber: (value, min, max) => Math.min(Math.max(value, min), max),
            };
            vm.runInNewContext(`${source.slice(start, end)};placeBookmarkOnPage(null);`, context);
            const get = name => properties.get(`--bookmark-${name}`);
            assert.ok(get('width') >= 270, 'Form must have enough width for readable inputs');
            assert.ok(get('left') - get('width') / 2 >= 8, 'Left edge stays on screen');
            assert.ok(get('left') + get('width') / 2 <= width - 8, 'Right edge stays on screen');
            assert.ok(get('top') >= offsetTop + 24, 'Close button has room above the form');
            assert.ok(get('top') + get('max-height') <= offsetTop + height - 24, 'Form ends above the keyboard');
            checked++;
        }
    }
}
console.log(`PASS: ${checked} form placements at 320/360/390/430/768px, including open keyboards and offscreen books.`);
