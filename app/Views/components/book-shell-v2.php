<link rel="stylesheet" href="/assets/css/book-preview.css?v=20260822-voices-form1">

<?php require __DIR__ . '/book-welcome.php'; ?>

<main class="preview" data-preview data-state="0" aria-label="Archon Publishing House interactive book">
    <section class="book-intro" data-book-intro aria-labelledby="book-intro-title">
        <div class="book-intro__book" aria-hidden="true">
            <div class="book-intro__page book-intro__page--left"></div>
            <div class="book-intro__page book-intro__page--right"></div>
            <i class="book-intro__spine"></i>
        </div>
        <div class="book-intro__copy">
            <h1 id="book-intro-title">Archon Publishing House</h1>
            <p>From your first idea to a professionally written eBook.</p>
            <button type="button" class="book-intro__button" data-book-intro-start>Get Started</button>
        </div>
    </section>

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

    <dialog class="book-bookmark" data-bookmark-dialog aria-labelledby="book-bookmark-title">
        <form method="dialog" class="book-bookmark__close">
            <button type="submit" aria-label="Close form">&times;</button>
        </form>
        <div class="book-bookmark__panel" data-bookmark-panel="quote">
            <p class="book-kicker">START YOUR EBOOK</p>
            <h2 id="book-bookmark-title">Tell us about your book.</h2>
            <form method="post" action="/quote" enctype="multipart/form-data" class="book-bookmark__form" data-bookmark-ajax>
                <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                <p class="book-bookmark__status book-bookmark__wide" data-bookmark-status role="status" aria-live="polite"></p>
                <label>Full name<input name="name" required></label>
                <label>Email address<input name="email" type="email" required></label>
                <label>Phone / WhatsApp<input name="phone"></label>
                <label>Service
                    <select name="service_id">
                        <option value="">Select a service</option>
                        <?php foreach (($services ?? []) as $service): ?>
                            <option value="<?=$service['id']?>"><?=Security::e($service['title'])?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Book title<input name="book_title"></label>
                <label>Genre<input name="genre"></label>
                <label>Estimated word count<input name="word_count" inputmode="numeric"></label>
                <label>Project stage
                    <select name="project_stage">
                        <option>Idea</option>
                        <option>Draft complete</option>
                        <option>Editing</option>
                        <option>Ready to publish</option>
                    </select>
                </label>
                <label>Completion date<input type="date" name="completion_date"></label>
                <label>Budget range
                    <select name="budget_range">
                        <option>Not sure yet</option>
                        <option>Under $1,000</option>
                        <option>$1,000-$3,000</option>
                        <option>$3,000+</option>
                    </select>
                </label>
                <label class="book-bookmark__wide">Project description<textarea name="description" rows="5" minlength="20" required></textarea></label>
                <label class="book-bookmark__wide">Optional extract<input name="attachment" type="file" accept=".pdf,.doc,.docx"></label>
                <label class="consent book-bookmark__wide"><input name="consent" type="checkbox" required> I consent to Archon using these details to discuss my request.</label>
                <button class="book-action book-bookmark__wide" type="submit">Request my consultation</button>
            </form>
        </div>
        <div class="book-bookmark__panel" data-bookmark-panel="contact" hidden>
            <p class="book-kicker">CONTACT ARCHON</p>
            <h2 id="book-bookmark-contact-title">Start a conversation.</h2>
            <form method="post" action="/contact" class="book-bookmark__form" data-bookmark-ajax>
                <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                <p class="book-bookmark__status book-bookmark__wide" data-bookmark-status role="status" aria-live="polite"></p>
                <label>Full name<input name="name" required></label>
                <label>Email address<input name="email" type="email" required></label>
                <label>Phone / WhatsApp<input name="phone"></label>
                <label>Subject<input name="subject" required></label>
                <label class="book-bookmark__wide">How can we help?<textarea name="message" rows="5" required minlength="10"></textarea></label>
                <label class="consent book-bookmark__wide"><input name="consent" type="checkbox" required> I consent to Archon using these details to reply.</label>
                <button class="book-action book-bookmark__wide" type="submit">Send message</button>
            </form>
        </div>
    </dialog>

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

<script src="/assets/js/book-preview.js?v=20260822-voices-form1" defer></script>
