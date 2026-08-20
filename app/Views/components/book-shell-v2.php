<link rel="stylesheet" href="/assets/css/book-preview.css?v=20260815-mobile-nav-logo2">

<?php require __DIR__ . '/book-welcome.php'; ?>

<main class="preview" data-preview data-state="0" aria-label="Archon Publishing House interactive book">
    <div class="preview-library" aria-hidden="true">
        <span class="preview-ambient-book preview-ambient-book--1"></span>
        <span class="preview-ambient-book preview-ambient-book--2"></span>
        <span class="preview-ambient-book preview-ambient-book--3"></span>
        <span class="preview-ambient-book preview-ambient-book--4"></span>
        <span class="preview-ambient-book preview-ambient-book--5"></span>
        <span class="preview-ambient-book preview-ambient-book--6"></span>
    </div>

    <div class="preview-reader">
        <div id="book-stage" class="preview-stage" data-stage role="region" aria-label="Your personalized eBook journey">
            <?php for ($i = 0; $i < 10; $i++): ?>
                <section class="preview-sheet" data-sheet="<?= $i ?>">
                    <article
                        class="preview-face front"
                        data-face="<?= $i === 0 ? 'cover' : ($i === 9 ? 'inside-back' : 'page-' . ($i * 2 - 1)) ?>"
                    ></article>
                    <article
                        class="preview-face back"
                        data-face="<?= $i === 0 ? 'inside-front' : ($i === 9 ? 'back-cover' : 'page-' . ($i * 2)) ?>"
                    ></article>
                </section>
            <?php endfor; ?>
            <i class="preview-spine" aria-hidden="true"></i>
        </div>

        <nav class="preview-side-navigation" aria-label="Book page navigation">
            <button type="button" class="preview-side-navigation__previous" data-prev aria-label="Previous page" aria-controls="book-stage" disabled>
                <span aria-hidden="true">&larr;</span>
            </button>
            <button type="button" class="preview-side-navigation__next" data-next aria-label="Next page" aria-controls="book-stage" disabled>
                <span aria-hidden="true">&rarr;</span>
            </button>
        </nav>
    </div>

    <div class="preview-controls" role="group" aria-label="Book controls">
        <div class="preview-controls__progress">
            <output data-progress aria-live="polite" aria-atomic="true"></output>
        </div>
        <div class="preview-controls__tools">
            <button type="button" data-reset>Front cover</button>
            <button type="button" data-change>Change name</button>
            <button type="button" data-remove>Remove name</button>
        </div>
    </div>

    <?php require __DIR__ . '/book-pages.php'; ?>

    <noscript>
        <section class="preview-noscript">
            <p class="book-kicker">ARCHON PUBLISHING HOUSE</p>
            <h1>Your eBook journey</h1>
            <p>Explore our professional eBook-writing services using the accessible pages below.</p>
            <nav aria-label="Website sections">
                <a href="/services">Writing services</a>
                <a href="/authors">Authors and selected work</a>
                <a href="/quote">Request a quote</a>
                <a href="/contact">Contact us</a>
            </nav>
        </section>
    </noscript>
</main>

<script src="/assets/js/book-preview.js?v=20260815-mobile-nav-logo2" defer></script>
