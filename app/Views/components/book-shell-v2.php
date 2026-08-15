<link rel="stylesheet" href="/assets/css/book-preview.css?v=20260815">

<?php require __DIR__ . '/book-welcome.php'; ?>

<main class="preview" data-preview data-state="0">
    <div class="preview-stage" data-stage aria-label="Your personalized eBook journey">
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

    <div class="preview-controls" aria-label="Book controls">
        <div class="preview-controls__navigation">
            <button type="button" data-prev>
                <span aria-hidden="true">&larr;</span>
                <span>Previous</span>
            </button>
            <output data-progress aria-live="polite"></output>
            <button type="button" data-next>
                <span>Next</span>
                <span aria-hidden="true">&rarr;</span>
            </button>
        </div>
        <div class="preview-controls__tools">
            <button type="button" data-reset>Front cover</button>
            <button type="button" data-change>Change name</button>
            <button type="button" data-remove>Remove name</button>
        </div>
    </div>

    <?php require __DIR__ . '/book-pages.php'; ?>
</main>

<script src="/assets/js/book-preview.js?v=20260815" defer></script>
