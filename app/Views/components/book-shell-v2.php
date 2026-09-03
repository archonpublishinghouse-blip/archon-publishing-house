<link rel="stylesheet" href="/assets/css/book-preview.css?v=20260903-slider1">

<?php require __DIR__ . '/book-welcome.php'; ?>

<main class="preview" data-preview data-state="0" aria-label="Archon Publishing House interactive book">
    <section class="book-splash" data-book-splash aria-label="Archon Publishing House introduction">
        <div class="book-splash__mark" aria-hidden="true"></div>
        <div class="book-splash__copy">
            <p>ARCHON</p>
            <h1>Archon Publishing House</h1>
            <span>From your idea to a professionally written eBook</span>
        </div>
        <button type="button" class="book-splash__skip" data-splash-skip>Skip Intro</button>
    </section>

    <header class="preview-masthead" aria-label="Primary website navigation">
        <a class="preview-masthead__brand" href="#top" aria-label="Archon Publishing House home">
            <img src="/assets/images/brand/archon-logo-transparent.png" alt="">
            <span>
                <strong>Archon</strong>
                <small>Publishing House</small>
            </span>
        </a>
        <nav class="preview-masthead__nav" aria-label="Main sections">
            <a href="#book-experience">The Book</a>
            <a href="#published-books">Published Work</a>
            <a href="#writing-services">Services</a>
            <a href="#writing-process">Process</a>
        </nav>
        <a class="preview-masthead__cta" href="#start-your-ebook">Request a Quote</a>
    </header>

    <section class="book-intro" id="top" data-book-intro aria-labelledby="book-intro-title">
        <div class="book-intro__grain" aria-hidden="true"></div>
        <div class="book-intro__content">
            <div class="book-intro__copy">
                <p class="book-kicker">ARCHON PUBLISHING HOUSE</p>
                <h1 id="book-intro-title">Your idea deserves a book with presence.</h1>
                <p>From a raw idea, outline or business concept, Archon helps shape a polished eBook with refined structure, credible writing and a finished author-ready voice.</p>
                <div class="book-intro__actions">
                    <button type="button" class="book-intro__button" data-book-intro-start>Begin Your eBook Journey</button>
                    <a class="book-intro__link" href="#book-experience">See the book experience</a>
                </div>
                <dl class="book-intro__proof" aria-label="Archon service highlights">
                    <div>
                        <dt>01</dt>
                        <dd>Idea-to-outline clarity</dd>
                    </div>
                    <div>
                        <dt>02</dt>
                        <dd>Professional eBook writing</dd>
                    </div>
                    <div>
                        <dt>03</dt>
                        <dd>Client-ready manuscript flow</dd>
                    </div>
                </dl>
                <div class="book-intro__signature" aria-label="Archon promise">
                    <span>Luxury writing experience</span>
                    <strong>Designed for founders, experts and future authors who want the book to feel as considered as the idea.</strong>
                </div>
            </div>

            <div class="book-intro__showcase" aria-hidden="true">
                <span class="book-intro__halo"></span>
                <div class="book-intro__book">
                    <span class="book-intro__shadow"></span>
                    <span class="book-intro__page book-intro__page--back"></span>
                    <span class="book-intro__page book-intro__page--middle"></span>
                    <div class="book-intro__cover">
                        <img src="/assets/images/brand/archon-logo-transparent.png" alt="">
                        <span>Professional eBook Writing</span>
                        <strong>Your Story, Refined</strong>
                    </div>
                    <span class="book-intro__bookmark"></span>
                </div>
                <div class="book-intro__note book-intro__note--one">outline</div>
                <div class="book-intro__note book-intro__note--two">draft</div>
                <div class="book-intro__note book-intro__note--three">final manuscript</div>
            </div>
        </div>
        <span class="book-intro__scroll">Scroll to open your personalized book</span>
    </section>

    <section class="book-experience-section" id="book-experience" data-book-experience aria-label="Interactive book experience">
        <div class="book-experience-section__label" aria-hidden="true">
            <span>Interactive Collector's Edition</span>
        </div>
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
    </section>

    <section class="book-below" aria-label="Archon Publishing House service overview">
        <div class="book-below__intro">
            <div>
                <p class="book-kicker">AFTER THE BOOK OPENS</p>
                <h2>Everything your idea needs to become a finished eBook.</h2>
                <p>The interactive book gives visitors the experience. These sections give them the confidence: what Archon does, how the process works, and how to begin a serious writing enquiry.</p>
            </div>
            <div class="book-below-visual" aria-hidden="true">
                <span class="book-below-visual__sheet book-below-visual__sheet--one"></span>
                <span class="book-below-visual__sheet book-below-visual__sheet--two"></span>
                <span class="book-below-visual__cover">
                    <img src="/assets/images/brand/archon-symbol.svg" alt="">
                </span>
                <span class="book-below-visual__pen"></span>
            </div>
        </div>

        <div class="book-below__grid">
            <article class="book-below-card book-below-card--wide">
                <span class="book-below-card__visual book-below-card__visual--idea" aria-hidden="true"></span>
                <span class="book-below-card__number">01</span>
                <h3>We shape raw ideas into readable books.</h3>
                <p>Visitors may arrive with only a subject, a rough outline, a manuscript, or a business concept. Archon’s role is to help turn that starting point into a structured, professionally written eBook.</p>
            </article>
            <article class="book-below-card">
                <span class="book-below-card__visual book-below-card__visual--writing" aria-hidden="true"></span>
                <span class="book-below-card__number">02</span>
                <h3>Writing-first positioning</h3>
                <p>The public website now focuses on eBook-writing services, trust, process and quote requests instead of marketplace shopping.</p>
            </article>
            <article class="book-below-card">
                <span class="book-below-card__visual book-below-card__visual--book" aria-hidden="true"></span>
                <span class="book-below-card__number">03</span>
                <h3>Book-like interaction</h3>
                <p>The readable book remains the signature experience, while the scrollable page supports visitors who want a faster overview.</p>
            </article>
        </div>

        <?php
            $publishedBooks = [
                ['title' => 'Words That Outlive Us', 'image' => '/assets/images/published-books/words-that-outlive-us.png'],
                ['title' => 'From Vision to Volume', 'image' => '/assets/images/published-books/from-vision-to-volume.png'],
                ['title' => 'The Unwritten Legacy', 'image' => '/assets/images/published-books/the-unwritten-legacy.png'],
                ['title' => 'Echoes of Ambition', 'image' => '/assets/images/published-books/echoes-of-ambition.png'],
                ['title' => 'The Courage to Begin', 'image' => '/assets/images/published-books/the-courage-to-begin.png'],
            ];
        ?>
        <section class="book-below-portfolio" id="published-books" aria-labelledby="book-below-portfolio-title">
            <div class="book-below-portfolio__copy">
                <p class="book-kicker">PUBLISHED BOOKS</p>
                <h2 id="book-below-portfolio-title">A look at books brought to life with Archon.</h2>
                <p>These published-book visuals help visitors immediately understand the quality, tone and premium presentation behind Archon Publishing House.</p>
            </div>
            <div class="published-slider" data-published-slider>
                <div class="published-slider__viewport" data-published-viewport tabindex="0" aria-label="Published books slider" aria-roledescription="carousel">
                    <div class="book-below-portfolio__shelf published-slider__track" data-published-track aria-live="polite">
                        <?php foreach ($publishedBooks as $index => $bookCover): ?>
                            <article class="published-book published-book--<?=($index % 5) + 1?>" data-published-slide>
                                <figure class="published-book__cover">
                                    <img src="<?=Security::e($bookCover['image'])?>" alt="<?=Security::e($bookCover['title'])?> book cover" loading="lazy" decoding="async">
                                </figure>
                                <div class="published-book__meta">
                                    <p>Published Book</p>
                                    <h3><?=Security::e($bookCover['title'])?></h3>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="published-slider__controls" aria-label="Published book slider controls">
                    <button type="button" class="published-slider__button" data-published-prev aria-label="Previous published books">
                        <span aria-hidden="true">&larr;</span>
                    </button>
                    <div class="published-slider__dots" data-published-dots aria-label="Published books pages"></div>
                    <button type="button" class="published-slider__button" data-published-next aria-label="Next published books">
                        <span aria-hidden="true">&rarr;</span>
                    </button>
                </div>
            </div>
        </section>

        <section class="book-below-services" id="writing-services" aria-labelledby="book-below-services-title">
            <div>
                <p class="book-kicker">WRITING SERVICES</p>
                <h2 id="book-below-services-title">Choose the level of help your eBook needs.</h2>
            </div>
            <div class="book-below-services__list">
                <?php foreach (array_slice(($services ?? []), 0, 3) as $index => $service): ?>
                    <article>
                        <i class="book-below-services__icon" aria-hidden="true"></i>
                        <span><?=str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT)?></span>
                        <h3><?=Security::e($service['title'])?></h3>
                        <p><?=Security::e($service['excerpt'] ?? $service['description'] ?? 'Professional support for your eBook project.')?></p>
                    </article>
                <?php endforeach; ?>
                <?php if (empty($services ?? [])): ?>
                    <article>
                        <i class="book-below-services__icon" aria-hidden="true"></i>
                        <span>01</span>
                        <h3>eBook writing support</h3>
                        <p>Professional help for clients who want to turn an idea, outline or draft into a completed eBook.</p>
                    </article>
                    <article>
                        <i class="book-below-services__icon" aria-hidden="true"></i>
                        <span>02</span>
                        <h3>Manuscript development</h3>
                        <p>Structured writing guidance for projects that need clearer flow, stronger chapters and a polished reader experience.</p>
                    </article>
                    <article>
                        <i class="book-below-services__icon" aria-hidden="true"></i>
                        <span>03</span>
                        <h3>Publishing preparation</h3>
                        <p>Support for shaping a client-ready manuscript before the next stage of publication.</p>
                    </article>
                <?php endif; ?>
            </div>
        </section>

        <section class="book-below-studio" aria-labelledby="book-below-studio-title">
            <div class="book-below-studio__scene" aria-hidden="true">
                <span class="book-below-studio__lamp"></span>
                <span class="book-below-studio__paper"></span>
                <span class="book-below-studio__paper book-below-studio__paper--second"></span>
                <span class="book-below-studio__ink"></span>
                <span class="book-below-studio__quill"></span>
            </div>
            <div class="book-below-studio__copy">
                <p class="book-kicker">VISUAL WRITING ROOM</p>
                <h2 id="book-below-studio-title">A calm, guided writing experience.</h2>
                <p>The design now supports the brand story visually: notes become structure, structure becomes pages, and pages become a finished eBook.</p>
            </div>
        </section>

        <section class="book-below-process" id="writing-process" aria-labelledby="book-below-process-title">
            <p class="book-kicker">THE ARCHON PROCESS</p>
            <h2 id="book-below-process-title">A simple path from conversation to manuscript.</h2>
            <div class="book-below-process__steps">
                <article>
                    <i class="book-below-process__mark" aria-hidden="true"></i>
                    <span>Discover</span>
                    <p>Share your idea, target reader, purpose and any notes you already have.</p>
                </article>
                <article>
                    <i class="book-below-process__mark" aria-hidden="true"></i>
                    <span>Structure</span>
                    <p>Clarify the book’s promise, chapter flow and writing direction.</p>
                </article>
                <article>
                    <i class="book-below-process__mark" aria-hidden="true"></i>
                    <span>Write</span>
                    <p>Develop the manuscript with a professional, consistent author voice.</p>
                </article>
                <article>
                    <i class="book-below-process__mark" aria-hidden="true"></i>
                    <span>Refine</span>
                    <p>Review, polish and prepare the eBook for its next publishing step.</p>
                </article>
            </div>
        </section>

        <section class="book-below-quote" aria-label="Archon writing promise">
            <p>“The right book does more than explain an idea. It gives the idea a form people can trust, remember and share.”</p>
            <span>Archon Publishing House</span>
        </section>

        <section class="book-below-cta" id="start-your-ebook" aria-label="Start your eBook call to action">
            <img class="book-below-cta__mark" src="/assets/images/brand/archon-symbol.svg" alt="" aria-hidden="true">
            <div>
                <p class="book-kicker">READY WHEN YOU ARE</p>
                <h2>Start with the idea. We will help shape the book.</h2>
                <p>Send the basics of your project and Archon can guide you toward the right writing plan.</p>
            </div>
            <div class="book-below-cta__actions">
                <a href="/quote">Request a Quote</a>
                <a href="/contact">Contact Archon</a>
            </div>
        </section>
    </section>

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

<script src="/assets/js/book-preview.js?v=20260903-slider1" defer></script>
