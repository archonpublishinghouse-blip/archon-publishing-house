<?php $serviceSlices=array_chunk($services??[],3);$authorSlices=array_chunk($authors??[],3);$reviewSlices=array_chunk($testimonials??[],3); ?>

<template data-book-page="frontispiece" data-book-url="/">
    <div class="chapter-marker">FRONTISPIECE</div>
    <h2>Welcome, <span data-book-display-name>Future Author</span>.</h2>
    <p>Every meaningful eBook begins with a clear idea and a writing partner who takes it seriously.</p>
    <p class="chapter-promise">Archon brings writing, editorial care and publishing craft together, so your knowledge can become a finished book worth sharing.</p>
    <p class="page-no">i</p>
</template>

<template data-book-page="contents" data-book-url="/contents">
    <div class="chapter-marker">TABLE OF CONTENTS</div>
    <h2>Inside your journey</h2>
    <nav class="book-toc" aria-label="Book table of contents">
        <ol>
            <li><a href="/" data-book-page="home">Home</a></li>
            <li><a href="/about" data-book-page="about">About Archon</a></li>
            <li><a href="/services" data-book-page="services">eBook Writing Services</a></li>
            <li><a href="/services" data-book-page="genres">Genres &amp; writing expertise</a></li>
            <li><a href="/services" data-book-page="process">Writing process</a></li>
            <li><a href="/authors" data-book-page="work">Our work</a></li>
            <li><a href="/authors" data-book-page="authors">Authors &amp; clients</a></li>
            <li><a href="/" data-book-page="reviews">Client reviews</a></li>
            <li><a href="/quote" data-book-page="quote">Start your eBook</a></li>
            <li><a href="/contact" data-book-page="contact">Contact us</a></li>
        </ol>
    </nav>
    <p class="page-no">ii</p>
</template>

<template data-book-page="home" data-book-url="/">
    <div class="chapter-marker">CHAPTER I</div>
    <h2>Turn your idea into an eBook.</h2>
    <p>Archon helps clients turn ideas, outlines, manuscripts and business concepts into professionally written eBooks.</p>
    <p>Bring us your expertise, notes or unfinished draft. We will help shape the structure, voice and reader journey with care.</p>
    <a class="book-link" href="/quote" data-book-page="quote">Start Your eBook</a>
    <p class="page-no">1</p>
</template>

<template data-book-page="about" data-book-url="/about">
    <div class="chapter-marker">CHAPTER II</div>
    <h2>About Archon.</h2>
    <p>Archon Publishing House is a professional eBook writing studio for clients with ideas, expertise, stories, frameworks or drafts that need to become finished manuscripts.</p>
    <p>We help define the reader, clarify the book promise, organize the structure and write or refine the manuscript with care.</p>
    <p>Our work is built for business owners, coaches, consultants, founders, speakers and first-time authors who want a book that feels credible and complete.</p>
    <p><strong>Email:</strong> hello@archonpublishinghouse.com<br><strong>Phone:</strong> +1 (555) 014-2026</p>
    <p class="page-no">2</p>
</template>

<template data-book-page="services" data-book-url="/services">
    <div class="chapter-marker">CHAPTER III</div>
    <h2>eBook Writing Services</h2>
    <?php foreach($serviceSlices[0]??[] as $service):?>
        <article class="chapter-item">
            <h3><?=Security::e($service['title'])?></h3>
            <p><?=Security::e($service['summary'])?></p>
            <a href="/services/<?=Security::e($service['slug'])?>">Learn more</a>
        </article>
    <?php endforeach;?>
    <p class="page-no">3</p>
</template>

<template data-book-page="services-more" data-book-url="/services">
    <div class="chapter-marker">CHAPTER III, CONTINUED</div>
    <h2>More ways we can help.</h2>
    <?php foreach($serviceSlices[1]??[] as $service):?>
        <article class="chapter-item">
            <h3><?=Security::e($service['title'])?></h3>
            <p><?=Security::e($service['summary'])?></p>
            <a href="/services/<?=Security::e($service['slug'])?>">Learn more</a>
        </article>
    <?php endforeach;?>
    <p class="page-no">4</p>
</template>

<template data-book-page="genres" data-book-url="/services">
    <div class="chapter-marker">CHAPTER IV</div>
    <h2>Genres &amp; writing expertise</h2>
    <?php foreach(array_slice($services??[],3,3) as $service):?>
        <article class="chapter-item">
            <h3><?=Security::e($service['title'])?></h3>
            <p><?=Security::e($service['description']??$service['summary'])?></p>
        </article>
    <?php endforeach;?>
    <p class="page-no">5</p>
</template>

<template data-book-page="process" data-book-url="/services">
    <div class="chapter-marker">CHAPTER V</div>
    <h2>Your eBook, made deliberately.</h2>
    <ol class="process-list">
        <li><b>01</b><span><strong>Discover</strong> Share your idea, audience, goals and source material.</span></li>
        <li><b>02</b><span><strong>Structure</strong> Build the promise, outline and chapter flow.</span></li>
        <li><b>03</b><span><strong>Write</strong> Develop a clear manuscript in your voice.</span></li>
        <li><b>04</b><span><strong>Refine</strong> Edit, polish and prepare the finished eBook.</span></li>
    </ol>
    <p class="page-no">6</p>
</template>

<template data-book-page="work" data-book-url="/authors">
    <div class="chapter-marker">CHAPTER VI</div>
    <h2>Our work, in capable hands.</h2>
    <p>Explore selected client-author profiles and examples of the type of expertise Archon helps shape into eBook projects. Detailed portfolio case studies are introduced only when approved for public display.</p>
    <?php foreach(array_slice($authors??[],0,2) as $author):?>
        <article class="chapter-item">
            <h3><?=Security::e($author['name'])?></h3>
            <p><?=Security::e($author['tagline']??$author['bio']??'Archon client author')?></p>
            <a href="/authors/<?=Security::e($author['slug'])?>">View profile</a>
        </article>
    <?php endforeach;?>
    <p class="page-no">7</p>
</template>

<template data-book-page="authors" data-book-url="/authors">
    <div class="chapter-marker">CHAPTER VII</div>
    <h2>Authors we have worked with</h2>
    <?php foreach($authorSlices[0]??[] as $author):?>
        <article class="chapter-item">
            <h3><?=Security::e($author['name'])?></h3>
            <p><?=Security::e($author['tagline']??$author['bio']??'Archon client author')?></p>
            <a href="/authors/<?=Security::e($author['slug'])?>">Meet this author</a>
        </article>
    <?php endforeach;?>
    <p class="page-no">8</p>
</template>

<template data-book-page="authors-more" data-book-url="/authors">
    <div class="chapter-marker">CHAPTER VII, CONTINUED</div>
    <h2>More Archon voices.</h2>
    <?php foreach($authorSlices[1]??[] as $author):?>
        <article class="chapter-item">
            <h3><?=Security::e($author['name'])?></h3>
            <p><?=Security::e($author['tagline']??$author['bio']??'Archon client author')?></p>
            <a href="/authors/<?=Security::e($author['slug'])?>">Meet this author</a>
        </article>
    <?php endforeach;?>
    <p class="page-no">9</p>
</template>

<template data-book-page="reviews" data-book-url="/">
    <div class="chapter-marker">CHAPTER VIII</div>
    <h2>Client reviews</h2>
    <?php foreach($reviewSlices[0]??[] as $review):?>
        <blockquote><p>&ldquo;<?=Security::e($review['quote'])?>&rdquo;</p><footer><?=Security::e($review['name'])?> <small><?=Security::e($review['role'])?></small></footer></blockquote>
    <?php endforeach;?>
    <p class="page-no">10</p>
</template>

<template data-book-page="reviews-more" data-book-url="/">
    <div class="chapter-marker">CHAPTER VIII, CONTINUED</div>
    <h2>More words from clients.</h2>
    <?php foreach($reviewSlices[1]??[] as $review):?>
        <blockquote><p>&ldquo;<?=Security::e($review['quote'])?>&rdquo;</p><footer><?=Security::e($review['name'])?> <small><?=Security::e($review['role'])?></small></footer></blockquote>
    <?php endforeach;?>
    <p class="page-no">11</p>
</template>

<template data-book-page="quote" data-book-url="/quote">
    <div class="chapter-marker">CHAPTER IX</div>
    <h2>Start your eBook.</h2>
    <p>Share your idea and our team will help you choose a clear next step.</p>
    <a class="book-link" href="/quote" data-bookmark-form="quote">Request a Quote</a>
    <p>Prefer to prepare before you write? Use our secure consultation form for project details and optional outlines, notes or manuscript extracts.</p>
    <p class="page-no">12</p>
</template>

<template data-book-page="contact" data-book-url="/contact">
    <div class="chapter-marker">CHAPTER X</div>
    <h2>Start a conversation.</h2>
    <p>Tell us about your eBook idea, outline, manuscript or business concept. We will reply with care.</p>
    <a class="book-link" href="/contact" data-bookmark-form="contact">Contact Us</a>
    <p><strong>Email:</strong> hello@archonpublishinghouse.com<br><strong>Phone:</strong> +1 (555) 014-2026</p>
    <p>For a detailed project enquiry, <a href="/quote" data-bookmark-form="quote">request a quote</a> using the existing secure form.</p>
    <p class="page-no">13</p>
</template>

<template data-book-page="final" data-book-url="/quote">
    <div class="chapter-marker">THE BACK COVER</div>
    <h2>Your story has a place here.</h2>
    <p>Let us help bring your eBook to life, one thoughtful page at a time.</p>
    <a class="book-link" href="/quote" data-bookmark-form="quote">Start Your eBook</a>
    <p class="page-no">14</p>
</template>
