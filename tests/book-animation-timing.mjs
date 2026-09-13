// Run: node tests/book-animation-timing.mjs
// Exercise the production completion helper without browser control or visual claims.
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import vm from 'node:vm';

const source = await readFile(new URL('../public/assets/js/book-preview.js', import.meta.url), 'utf8');
const start = source.indexOf('        const animateOverlay=');
const end = source.indexOf('        const switchSettledSpread=', start);
assert.ok(start >= 0 && end > start, 'Locate the real production completion helper');

function setup(style = { animationDuration: '.34s', animationDelay: '0s' }) {
    const frames = [], timers = new Map(), listeners = new Map(), classes = new Set();
    let nextTimer = 0;
    const overlay = {
        classList: { add: name => classes.add(name) },
        addEventListener: (name, fn) => listeners.set(name, fn),
        removeEventListener: name => listeners.delete(name),
    };
    const context = {
        requestAnimationFrame: fn => frames.push(fn),
        getComputedStyle: () => style,
        setTimeout: (fn, duration) => { const id = ++nextTimer; timers.set(id, { fn, duration }); return id; },
        clearTimeout: id => timers.delete(id),
    };
    const helper = vm.runInNewContext(`(() => {
        let transitionToken=1, timer=0, activeAnimationCleanup=null;
        ${source.slice(start, end)}
        return { animateOverlay,
            invalidate() { transitionToken++; clearTimeout(timer); activeAnimationCleanup?.(); },
        };
    })()`, context);
    return { helper, overlay, frames, timers, listeners, classes };
}

for (const className of ['next-out', 'next-in', 'previous-out', 'previous-in']) {
    const test = setup();
    const completed = [];
    test.helper.animateOverlay(test.overlay, className, 1, source => completed.push(source));
    assert.equal(test.timers.size, 0, 'A delayed first frame must not spend the completion budget');
    test.frames.shift()();
    assert.ok(test.classes.has(className));
    assert.equal([...test.timers.values()][0].duration, 490, 'Fail-safe uses CSS duration plus 150ms');
    const finish = test.listeners.get('animationend');
    finish({ target: {} });
    assert.equal(completed.length, 0, 'Bubbling child animations cannot finish a page turn');
    finish({ target: test.overlay }); finish({ target: test.overlay });
    assert.deepEqual(completed, ['animationend'], 'Completion is idempotent in both directions');
    assert.equal(test.timers.size, 0);
    assert.equal(test.listeners.size, 0);
}

for (const reason of ['animationcancel', 'fail-safe']) {
    const test = setup({ animationDuration: '200ms, .4s', animationDelay: '.05s' });
    const completed = [];
    test.helper.animateOverlay(test.overlay, 'next-out', 1, source => completed.push(source));
    test.frames.shift()();
    assert.equal([...test.timers.values()][0].duration, 600, 'Multiple CSS timings and units are supported');
    if (reason === 'animationcancel') test.listeners.get(reason)({ target: test.overlay });
    else [...test.timers.values()][0].fn();
    assert.deepEqual(completed, [reason]);
    assert.equal(test.listeners.size, 0);
    assert.equal(test.timers.size, 0);
}

for (const beforeFrame of [true, false]) {
    const test = setup();
    let completed = 0;
    test.helper.animateOverlay(test.overlay, 'previous-in', 1, () => completed++);
    if (!beforeFrame) test.frames.shift()();
    const staleCompletion = test.listeners.get('animationend');
    test.helper.invalidate();
    if (beforeFrame) test.frames.shift()();
    staleCompletion({ target: test.overlay });
    assert.equal(completed, 0, 'A stale/cancelled token cannot commit state');
    assert.equal(test.timers.size, 0);
    assert.equal(test.listeners.size, 0);
}
console.log('PASS: forward/reverse overlay timing, CSS-derived fail-safe, child-event filtering, cancellation, stale tokens and idempotent cleanup.');
