<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow"><?=Security::e($settings['home.hero.eyebrow']??'EBOOK WRITING, REFINED')?></p>
        <h1><?=Security::e($settings['home.hero.title']??'Turn your eBook idea into a professionally written manuscript.')?></h1>
        <p><?=Security::e($settings['home.hero.body']??'Archon helps clients shape ideas, outlines, expertise and drafts into polished eBooks with clear structure and editorial care.')?></p>
        <div class="actions">
            <a class="button" href="/services"><?=Security::e($settings['home.hero.services_label']??'Explore writing services')?></a>
            <a class="button outline" href="/quote"><?=Security::e($settings['home.hero.quote_label']??'Request a consultation')?></a>
        </div>
    </div>
    <div class="hero-art" aria-label="An abstract composition of books and editorial marks">
        <div class="book-spine one">YOUR<br>STORY</div>
        <div class="book-spine two">FROM<br>IDEA TO<br>EBOOK</div>
        <div class="compass">*</div>
    </div>
</section>

<section class="services-feature section parchment">
    <div class="section-heading">
        <div>
            <p class="eyebrow">FROM IDEA TO MANUSCRIPT</p>
            <h2>Open our writing book.</h2>
        </div>
        <a href="/services">Explore every service</a>
    </div>
    <div class="service-book" data-book>
        <div class="book-cover" data-book-open tabindex="0" role="button" aria-label="Open our eBook writing services book">
            <span>*</span>
            <h3>Our eBook Writing<br>Services</h3>
            <small>Open the book to explore</small>
        </div>
        <div class="book-open" aria-live="polite">
            <button class="close-book" data-book-close aria-label="Close services book">Close</button>
            <div class="pages"><article data-page="left"></article><article data-page="right"></article></div>
            <div class="book-controls">
                <button data-book-prev aria-label="Previous services page">Previous</button>
                <span data-book-progress></span>
                <button data-book-next aria-label="Next services page">Next</button>
            </div>
        </div>
        <div class="service-data" hidden><?=Security::e(json_encode($services,JSON_HEX_APOS|JSON_HEX_QUOT))?></div>
    </div>
    <noscript>
        <div class="service-fallback">
            <?php foreach($services as $service):?>
                <article><h3><?=Security::e($service['title'])?></h3><p><?=Security::e($service['summary'])?></p><a href="/services/<?=Security::e($service['slug'])?>">Learn more</a></article>
            <?php endforeach;?>
        </div>
    </noscript>
</section>

<section class="section reasons">
    <div>
        <p class="eyebrow">WHY ARCHON</p>
        <h2>Writing is a craft, not a shortcut.</h2>
    </div>
    <div class="reason-grid">
        <article><b>01</b><h3>Editorial care</h3><p>Every idea is shaped with structure, tone and reader clarity in mind.</p></article>
        <article><b>02</b><h3>Clear partnership</h3><p>Thoughtful guidance and transparent milestones from first conversation to finished manuscript.</p></article>
        <article><b>03</b><h3>Built to endure</h3><p>eBooks shaped to support your expertise, message and long-term credibility.</p></article>
    </div>
</section>

<section class="section dark process">
    <p class="eyebrow">THE ARCHON PROCESS</p>
    <h2>A deliberate path from idea to eBook.</h2>
    <ol><li><b>01</b> Discover</li><li><b>02</b> Structure</li><li><b>03</b> Write</li><li><b>04</b> Refine</li></ol>
</section>

<section class="section testimonial-section">
    <div class="section-heading">
        <div><p class="eyebrow">CLIENT EXPERIENCES</p><h2>Trusted with important ideas.</h2></div>
    </div>
    <div class="testimonial-grid">
        <?php foreach($testimonials as $testimonial):?>
            <blockquote><p>“<?=Security::e($testimonial['quote'])?>”</p><footer><b><?=Security::e($testimonial['name'])?></b><span><?=Security::e($testimonial['role'])?></span></footer></blockquote>
        <?php endforeach;?>
    </div>
</section>

<section class="section">
    <div class="section-heading">
        <div><p class="eyebrow">CLIENT AUTHORS</p><h2>Meet people behind the projects.</h2></div>
        <a href="/authors">View profiles</a>
    </div>
    <div class="author-grid">
        <?php foreach($authors as $author):?>
            <a class="author-card" href="/authors/<?=Security::e($author['slug'])?>"><div class="portrait"><?=Security::e(mb_substr($author['name'],0,1))?></div><h3><?=Security::e($author['name'])?></h3><p><?=Security::e($author['tagline']??'Archon client author')?></p></a>
        <?php endforeach;?>
    </div>
</section>

<section class="section parchment">
    <p class="eyebrow">FROM THE JOURNAL</p>
    <h2>Notes for future eBook authors.</h2>
    <div class="article-grid">
        <?php foreach($posts as $post):?>
            <a href="/blog/<?=Security::e($post['slug'])?>"><span>JOURNAL</span><h3><?=Security::e($post['title'])?></h3><p><?=Security::e($post['excerpt'])?></p><b>Read article</b></a>
        <?php endforeach;?>
    </div>
</section>

<section class="cta section">
    <p class="eyebrow">YOUR STORY HAS A PLACE HERE</p>
    <h2>Ready to bring your eBook to life?</h2>
    <a class="button" href="/quote">Request a consultation</a>
</section>
