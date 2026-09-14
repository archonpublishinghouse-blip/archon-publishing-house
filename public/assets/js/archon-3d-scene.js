const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

const prefersCompactDevice = () => window.matchMedia('(pointer: coarse)').matches
    || (navigator.deviceMemory && navigator.deviceMemory <= 4);

const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

const ready = (callback) => {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', callback, { once: true });
    else callback();
};

const createScene = (THREE, host, preview, onRestore) => {
    const compact = prefersCompactDevice();
    const layer = document.createElement('div');
    layer.className = 'archon-webgl-scene';
    layer.setAttribute('aria-hidden', 'true');

    host.prepend(layer);

    let renderer;
    try {
        renderer = new THREE.WebGLRenderer({
            alpha: true,
            antialias: !compact,
            powerPreference: 'low-power',
        });
    } catch {
        layer.remove();
        return null;
    }

    renderer.setClearColor(0x000000, 0);
    layer.append(renderer.domElement);

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(preview ? 0x062326 : 0xf2ebd0, preview ? 0.038 : 0.022);

    const camera = new THREE.PerspectiveCamera(36, 1, 0.1, 100);
    camera.position.set(0, preview ? 0.25 : 0.9, preview ? 9.5 : 11);

    const stage = new THREE.Group();
    scene.add(stage);

    const ambient = new THREE.AmbientLight(0xf2ebd0, preview ? 0.9 : 1.15);
    scene.add(ambient);

    const keyLight = new THREE.DirectionalLight(0xf8e7b8, 2.15);
    keyLight.position.set(-3, 5, 6);
    scene.add(keyLight);

    const tealLight = new THREE.PointLight(0x1d5356, 1.8, 18);
    tealLight.position.set(4, -1, 4);
    scene.add(tealLight);

    const brass = new THREE.MeshStandardMaterial({ color: 0xb89654, roughness: 0.44, metalness: 0.5 });
    const paper = new THREE.MeshStandardMaterial({ color: 0xf2ebd0, roughness: 0.86, metalness: 0.02 });
    const teal = new THREE.MeshStandardMaterial({ color: 0x0d3e42, roughness: 0.65, metalness: 0.08 });
    const muted = new THREE.MeshStandardMaterial({ color: 0x708f8c, roughness: 0.72, metalness: 0.05 });
    const ink = new THREE.MeshStandardMaterial({ color: 0x0a2223, roughness: 0.78, metalness: 0.05 });

    const bookGeometry = new THREE.BoxGeometry(0.62, 0.92, 0.12);
    const pageGeometry = new THREE.BoxGeometry(0.52, 0.82, 0.04);
    const lineGeometry = new THREE.BoxGeometry(0.46, 0.018, 0.018);
    const spineGeometry = new THREE.BoxGeometry(0.06, 0.92, 0.16);
    const books = [];

    const makeBook = ({ x, y, z, rx, ry, rz, s, material }) => {
        const book = new THREE.Group();
        const cover = new THREE.Mesh(bookGeometry, material);
        const pages = new THREE.Mesh(pageGeometry, paper);
        const spine = new THREE.Mesh(spineGeometry, brass);
        const band = new THREE.Mesh(lineGeometry, brass);

        pages.position.z = -0.065;
        pages.position.x = 0.03;
        spine.position.x = -0.34;
        band.position.set(0, 0.22, 0.075);

        book.add(pages, cover, spine, band);
        book.position.set(x, y, z);
        book.rotation.set(rx, ry, rz);
        book.scale.setScalar(s);
        book.userData.baseY = y;
        book.userData.baseRotationY = ry;
        stage.add(book);
        books.push(book);
        return book;
    };

    const bookLayout = preview ? [
        { x: -4.9, y: -1.1, z: -2.3, rx: -0.12, ry: 0.42, rz: -0.32, s: 1.2, material: muted },
        { x: -3.75, y: 1.25, z: -3.8, rx: 0.2, ry: 0.15, rz: -0.14, s: 1.42, material: teal },
        { x: -2.72, y: -1.45, z: -2.6, rx: -0.18, ry: 0.22, rz: 0.24, s: 0.96, material: ink },
        { x: 3.45, y: -1.22, z: -3.1, rx: -0.1, ry: -0.34, rz: 0.31, s: 1.1, material: ink },
        { x: 4.62, y: 1.05, z: -4, rx: 0.15, ry: -0.18, rz: 0.13, s: 1.34, material: teal },
        { x: 5.4, y: -0.45, z: -2.8, rx: -0.16, ry: -0.36, rz: 0.2, s: 1.02, material: muted },
    ] : [
        { x: -5.8, y: 1.5, z: -4, rx: 0.2, ry: 0.35, rz: -0.5, s: 1.1, material: teal },
        { x: -3.8, y: -1.7, z: -5, rx: -0.15, ry: 0.24, rz: 0.38, s: 0.9, material: muted },
        { x: 3.7, y: 1.2, z: -4.7, rx: 0.25, ry: -0.4, rz: 0.44, s: 1.15, material: ink },
        { x: 5.7, y: -1.3, z: -5.8, rx: -0.18, ry: -0.2, rz: -0.35, s: 0.95, material: teal },
    ];
    bookLayout.forEach(makeBook);

    const particleCount = compact ? 32 : 64;
    const positions = new Float32Array(particleCount * 3);
    for (let i = 0; i < particleCount; i += 1) {
        positions[i * 3] = (Math.random() - 0.5) * (preview ? 13 : 16);
        positions[i * 3 + 1] = (Math.random() - 0.5) * (preview ? 7 : 8);
        positions[i * 3 + 2] = -Math.random() * 8 - 1;
    }
    const particleGeometry = new THREE.BufferGeometry();
    particleGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    const particles = new THREE.Points(
        particleGeometry,
        new THREE.PointsMaterial({
            color: preview ? 0xf2ebd0 : 0x0d3e42,
            size: preview ? 0.024 : 0.018,
            transparent: true,
            opacity: preview ? 0.42 : 0.18,
            depthWrite: false,
        }),
    );
    stage.add(particles);

    const pointer = { x: 0, y: 0 };
    const target = { x: 0, y: 0 };
    let width = 1;
    let height = 1;
    let frame = 0;
    let running = false;
    let previousTime = 0;
    let contextLost = false;

    const resize = () => {
        width = Math.max(1, Math.min(layer.clientWidth || window.innerWidth, window.innerWidth));
        height = Math.max(1, Math.min(layer.clientHeight || window.innerHeight, window.innerHeight));
        // Bound the drawing buffer to 1.5M pixels, independent of document length.
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, compact ? 1 : 1.25, Math.sqrt(1500000 / (width * height))));
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.position.z = width < 760 ? (preview ? 10.4 : 12.5) : (preview ? 9.5 : 11);
        camera.updateProjectionMatrix();
        if (!contextLost) renderer.render(scene, camera);
    };

    const animate = (time = 0) => {
        if (!running) return;
        frame = requestAnimationFrame(animate);
        // Decorative motion has its own 30fps budget; page turns remain CSS-driven.
        const frameDuration = 1000 / 30;
        const elapsed = time - previousTime;
        if (elapsed + 0.01 < frameDuration) return;
        // Keep the frame deadline on its grid instead of discarding the remainder.
        previousTime += Math.floor((elapsed + 0.01) / frameDuration) * frameDuration;
        pointer.x += (target.x - pointer.x) * 0.055;
        pointer.y += (target.y - pointer.y) * 0.055;

        stage.rotation.y = pointer.x * 0.09;
        stage.rotation.x = -pointer.y * 0.045;
        tealLight.position.x = 4 + pointer.x * 2;
        tealLight.position.y = -1 - pointer.y;
        particles.rotation.z = time * 0.000025;

        books.forEach((book, index) => {
            book.position.y = book.userData.baseY + Math.sin(time * 0.0007 + index * 1.9) * 0.055;
            book.rotation.y = book.userData.baseRotationY + Math.sin(time * 0.0005 + index) * 0.035;
        });

        renderer.render(scene, camera);
    };

    const onPointer = (event) => {
        if (!running || compact) return;
        target.x = clamp((event.clientX / Math.max(1, window.innerWidth) - 0.5) * 2, -1, 1);
        target.y = clamp((event.clientY / Math.max(1, window.innerHeight) - 0.5) * 2, -1, 1);
    };

    const stop = () => {
        running = false;
        if (frame) cancelAnimationFrame(frame);
        frame = 0;
    };

    const start = () => {
        if (reduceMotion.matches || contextLost || running) return;
        running = true;
        previousTime = performance.now();
        frame = requestAnimationFrame(animate);
    };

    let resizeFrame = 0;
    const scheduleResize = () => {
        if (resizeFrame) return;
        resizeFrame = requestAnimationFrame(() => { resizeFrame = 0; resize(); });
    };
    window.addEventListener('resize', scheduleResize, { passive: true });
    window.addEventListener('pointermove', onPointer, { passive: true });
    renderer.domElement.addEventListener('webglcontextlost', event => {
        event.preventDefault();
        contextLost = true;
        stop();
    });
    renderer.domElement.addEventListener('webglcontextrestored', () => {
        contextLost = false;
        resize();
        onRestore();
    });
    resize();
    return { start, stop };
};

ready(() => {
    if (document.body.classList.contains('admin-body')) return;
    const preview = document.querySelector('[data-preview]');
    const bookSection = preview?.querySelector('[data-book-experience]');
    const host = bookSection || document.body;
    let visible = !bookSection;
    let near = !bookSection;
    let loading = false;
    let scene = null;
    let dependency = null;
    const canRun = () => visible && !document.hidden && !reduceMotion.matches && !preview?.classList.contains('is-reader-busy') && !document.body.classList.contains('is-splash-lock');
    const sync = () => {
        if (scene) { if (canRun()) scene.start(); else scene.stop(); }
        else if (near) scheduleLoad();
    };
    const load = async () => {
        if (document.hidden || reduceMotion.matches || prefersCompactDevice() || navigator.connection?.saveData || !near) {
            loading = false;
            return;
        }
        try {
            // A decorative dependency must not delay DOMContentLoaded or book controls.
            dependency ||= await import('../vendor/three.module.min.js');
            // Download nearby, but defer GPU setup if the reader became busy or left.
            if (!near || !canRun() || navigator.connection?.saveData) { loading = false; return; }
            scene = createScene(dependency, host, Boolean(preview), sync);
            if (scene) sync();
        } catch { /* The existing CSS book decorations remain available. */ }
    };
    const scheduleLoad = () => {
        if (loading || scene || reduceMotion.matches || prefersCompactDevice() || navigator.connection?.saveData) return;
        loading = true;
        if ('requestIdleCallback' in window) window.requestIdleCallback(load, { timeout: 1600 });
        else setTimeout(load, 200);
    };
    if (bookSection && 'IntersectionObserver' in window) {
        new IntersectionObserver(entries => { near = entries[0].isIntersecting; if (near) scheduleLoad(); }, { rootMargin: '240px' }).observe(bookSection);
        new IntersectionObserver(entries => { visible = entries[0].isIntersecting; sync(); }).observe(bookSection);
    } else {
        visible = near = true;
        scheduleLoad();
    }
    document.addEventListener('visibilitychange', sync);
    reduceMotion.addEventListener?.('change', sync);
    preview?.addEventListener('archon:reader-busy', sync);
    document.addEventListener('archon:splash-end', sync);
});

ready(() => {
    if (document.body.classList.contains('admin-body')) return;

    // Stop looping decoration once its section leaves the viewport.
    if ('IntersectionObserver' in window) {
        const motionObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => entry.target.classList.toggle('is-motion-paused', !entry.isIntersecting));
        });
        document.querySelectorAll('.book-intro, .book-experience-section, .book-below-visual, .book-below-studio').forEach(section => motionObserver.observe(section));
    }

    const revealItems = document.querySelectorAll('.page-hero, .section, .panel, .service-list article, .author-card, .article-grid > a, .related-services > a');
    revealItems.forEach((item, index) => {
        item.dataset.archonReveal = '';
        item.style.setProperty('--reveal-delay', `${Math.min(index * 35, 260)}ms`);
    });

    if ('IntersectionObserver' in window && !reduceMotion.matches && !prefersCompactDevice()) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    const tiltItems = document.querySelectorAll('.author-card, .article-grid > a, .related-services > a, .service-list article, .book-card');
    if (prefersCompactDevice()) return;
    tiltItems.forEach((item) => {
        item.dataset.archonTilt = '';
        item.addEventListener('pointermove', (event) => {
            if (reduceMotion.matches) return;
            const rect = item.getBoundingClientRect();
            const x = ((event.clientX - rect.left) / Math.max(1, rect.width) - 0.5) * 2;
            const y = ((event.clientY - rect.top) / Math.max(1, rect.height) - 0.5) * 2;
            item.style.setProperty('--tilt-x', `${(-y * 4).toFixed(2)}deg`);
            item.style.setProperty('--tilt-y', `${(x * 5).toFixed(2)}deg`);
        }, { passive: true });
        item.addEventListener('pointerleave', () => {
            item.style.removeProperty('--tilt-x');
            item.style.removeProperty('--tilt-y');
        });
    });
});
