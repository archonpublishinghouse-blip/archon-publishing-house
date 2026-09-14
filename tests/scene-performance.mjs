// Run: node --experimental-vm-modules tests/scene-performance.mjs
// Deterministic lifecycle checks with a mock WebGL renderer; this is not browser visual QA.
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import vm from 'node:vm';

const source = await readFile(new URL('../public/assets/js/archon-3d-scene.js', import.meta.url), 'utf8');
async function setup({ reduced = false, saveData = false, compact = false, deferImport = false } = {}) {
    const observers = [], frames = new Map(), idle = [], renderers = [];
    let imports = 0, nextFrame = 0;
    let releaseImport;
    const importGate = new Promise(resolve => { releaseImport = resolve; });
    class Target {
        constructor() {
            this.listeners = new Map(); this.classes = new Set(); this.children = [];
            this.classList = { contains: name => this.classes.has(name), add: name => this.classes.add(name), toggle: (name, value) => value ? this.classes.add(name) : this.classes.delete(name) };
            this.style = { setProperty() { throw new Error('Background must not update inherited CSS variables each frame'); } };
            this.clientWidth = 1920; this.clientHeight = 18000; this.dataset = {};
        }
        addEventListener(name, fn) { const all = this.listeners.get(name) || []; all.push(fn); this.listeners.set(name, all); }
        dispatch(name) { (this.listeners.get(name) || []).forEach(fn => fn({ preventDefault() {} })); }
        setAttribute() {}
        prepend(child) { this.children.unshift(child); }
        append(child) { this.children.push(child); }
        remove() {}
    }
    const body = new Target(), preview = new Target(), bookSection = new Target();
    const doc = new Target();
    Object.assign(doc, { readyState: 'complete', hidden: false, body, createElement: () => new Target(), querySelector: () => preview, querySelectorAll: () => [] });
    preview.querySelector = () => bookSection;
    const motion = new Target(); motion.matches = reduced;
    class Observer {
        constructor(callback, options = {}) { this.callback = callback; this.options = options; observers.push(this); }
        observe(target) { this.target = target; }
        emit(value) { this.callback([{ isIntersecting: value, target: this.target }]); }
        unobserve() {}
    }
    const window = new Target();
    Object.assign(window, {
        innerWidth: 1920, innerHeight: 1080, devicePixelRatio: 3,
        matchMedia: query => query.includes('reduced') ? motion : { matches: compact },
        IntersectionObserver: Observer, requestIdleCallback: fn => idle.push(fn),
    });
    class Vector { constructor() { this.x = this.y = this.z = 0; } set(x, y, z) { Object.assign(this, { x, y, z }); } setScalar() {} }
    class Object3D {
        constructor() { this.position = new Vector(); this.rotation = new Vector(); this.scale = new Vector(); this.userData = {}; }
        add() {}
        updateProjectionMatrix() {}
        setAttribute() {}
    }
    class Renderer {
        constructor(options) { this.options = options; this.domElement = new Target(); this.renders = 0; renderers.push(this); }
        setClearColor() {}
        setPixelRatio(value) { this.dpr = value; }
        setSize(width, height) { this.width = width; this.height = height; }
        render() { this.renders++; }
    }
    const three = { WebGLRenderer: Renderer };
    for (const name of ['Scene', 'FogExp2', 'PerspectiveCamera', 'Group', 'AmbientLight', 'DirectionalLight', 'PointLight', 'MeshStandardMaterial', 'BoxGeometry', 'Mesh', 'BufferGeometry', 'BufferAttribute', 'Points', 'PointsMaterial']) three[name] = Object3D;
    const context = vm.createContext({
        window, document: doc, navigator: { connection: { saveData }, deviceMemory: compact ? 4 : 8 },
        IntersectionObserver: Observer, performance: { now: () => 0 }, console,
        requestAnimationFrame: fn => { const id = ++nextFrame; frames.set(id, fn); return id; },
        cancelAnimationFrame: id => frames.delete(id), setTimeout: fn => idle.push(fn),
    });
    const threeModule = new vm.SyntheticModule(Object.keys(three), function () { for (const [name, value] of Object.entries(three)) this.setExport(name, value); }, { context });
    await threeModule.link(() => {}); await threeModule.evaluate();
    const module = new vm.SourceTextModule(source, { context, importModuleDynamically: async () => {
        imports++;
        if (deferImport) await importGate;
        return threeModule;
    } });
    await module.link(() => { throw new Error('Decorative scene must not have a blocking static import'); });
    await module.evaluate();
    const near = observers.find(item => item.target === bookSection && item.options.rootMargin);
    const visible = observers.find(item => item.target === bookSection && !item.options.rootMargin);
    const pump = time => { const callbacks = [...frames.values()]; frames.clear(); callbacks.forEach(fn => fn(time)); };
    const load = async () => { for (const fn of idle.splice(0)) await fn(); };
    return { doc, preview, bookSection, motion, window, frames, renderers, near, visible, pump, load, releaseImport, imports: () => imports };
}

const s = await setup();
assert.equal(s.imports(), 0, 'No Three dependency during initial page startup');
s.near.emit(true);
await s.load();
assert.equal(s.imports(), 1, 'Three loads only once when the book approaches the viewport');
assert.equal(s.renderers.length, 0, 'GPU setup waits for the book to actually become visible');
assert.equal(s.frames.size, 0, 'Offscreen scene has no active rendering loop');
s.visible.emit(true); await s.load();
assert.equal(s.imports(), 1, 'A postponed setup reuses its downloaded dependency');
assert.equal(s.bookSection.children.length, 1, 'Canvas belongs to the book section');
assert.equal(s.preview.children.length, 0, 'Canvas does not cover the long homepage');
const renderer = s.renderers[0];
assert.ok(renderer.width * renderer.height * renderer.dpr ** 2 <= 1500001, 'Drawing buffer stays within 1.5M pixels at DPR 3');
assert.equal(renderer.height, 1080, 'Tall content cannot expand the canvas beyond the viewport');
assert.equal(s.frames.size, 1);
const baseline = renderer.renders;
s.pump(17); s.pump(34);
assert.equal(renderer.renders - baseline, 1, 'Background renders at most once per 33ms');
s.preview.classList.add('is-reader-busy'); s.preview.dispatch('archon:reader-busy');
assert.equal(s.frames.size, 0, 'Scene stops during book interaction');
s.preview.classList.toggle('is-reader-busy', false); s.preview.dispatch('archon:reader-busy');
assert.equal(s.frames.size, 1, 'Scene resumes with one loop after settling');
s.doc.hidden = true; s.doc.dispatch('visibilitychange');
assert.equal(s.frames.size, 0, 'Hidden tabs stop rendering');
s.doc.hidden = false; s.doc.dispatch('visibilitychange');
assert.equal(s.frames.size, 1, 'Visible tabs resume without duplicate loops');
s.visible.emit(false);
assert.equal(s.frames.size, 0, 'Scrolling past the book stops rendering');
s.visible.emit(true); s.motion.matches = true; s.motion.dispatch('change');
assert.equal(s.frames.size, 0, 'Reduced motion stops an existing scene');
s.motion.matches = false; s.motion.dispatch('change');
for (let i = 0; i < 20; i++) s.preview.dispatch('archon:reader-busy');
assert.equal(s.frames.size, 1, 'Repeated lifecycle notifications do not duplicate animation loops');
renderer.domElement.dispatch('webglcontextlost');
assert.equal(s.frames.size, 0, 'A lost GPU context stops rendering');
renderer.domElement.dispatch('webglcontextrestored');
assert.equal(s.frames.size, 1, 'A restored GPU context resumes the visible scene');
renderer.domElement.dispatch('webglcontextlost');
s.doc.hidden = true;
renderer.domElement.dispatch('webglcontextrestored');
assert.equal(s.frames.size, 0, 'GPU recovery does not restart a hidden scene');
s.doc.hidden = false; s.doc.dispatch('visibilitychange');
assert.equal(s.frames.size, 1);

const pacing = await setup();
pacing.near.emit(true); pacing.visible.emit(true); await pacing.load();
const initialRenders = pacing.renderers[0].renders;
for (let frame = 1; frame <= 600; frame++) pacing.pump(frame * 1000 / 60);
assert.equal(pacing.renderers[0].renders - initialRenders, 300, '30fps budget keeps even deadlines across floating-point 60Hz timestamps');

for (const reason of ['hidden', 'busy', 'offscreen', 'reduced']) {
    const pending = await setup({ deferImport: true });
    pending.near.emit(true); pending.visible.emit(true);
    const download = pending.load();
    if (reason === 'hidden') pending.doc.hidden = true;
    if (reason === 'busy') pending.preview.classList.add('is-reader-busy');
    if (reason === 'offscreen') { pending.near.emit(false); pending.visible.emit(false); }
    if (reason === 'reduced') pending.motion.matches = true;
    pending.releaseImport(); await download;
    assert.equal(pending.renderers.length, 0, `${reason} during download postpones GPU allocation`);
    pending.doc.hidden = false; pending.motion.matches = false;
    pending.preview.classList.toggle('is-reader-busy', false);
    pending.near.emit(true); pending.visible.emit(true); await pending.load();
    assert.equal(pending.renderers.length, 1, 'Eligible scene resumes setup exactly once');
    assert.equal(pending.imports(), 1, 'Retry reuses the already loaded module');
}

for (const options of [{ reduced: true }, { saveData: true }]) {
    const quiet = await setup(options); quiet.near.emit(true); quiet.visible.emit(true); await quiet.load();
    assert.equal(quiet.imports(), 0, 'Reduced-motion and save-data visitors skip WebGL downloads');
}
const mobile = await setup({ compact: true });
mobile.near.emit(true); mobile.visible.emit(true); await mobile.load();
assert.equal(mobile.imports(), 0, 'Coarse-pointer mobile devices skip the WebGL download');
assert.equal(mobile.renderers.length, 0, 'Mobile keeps decorative rendering in CSS');
console.log('PASS: lazy loading, post-download eligibility, canvas budget, even frame pacing, GPU recovery, offscreen/hidden/busy pauses, reduced motion, save-data and single-loop lifecycle.');
