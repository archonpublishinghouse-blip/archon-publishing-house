<?php $isContentsChapter=($bookChapter??'')==='contents'; ?>
<section class="archon-book" data-book-experience data-book-chapter="<?=Security::e($bookChapter??'home')?>">
    <div class="book-atmosphere" aria-hidden="true"></div>
    <section class="collector-cover" data-book-cover aria-labelledby="book-cover-title">
        <div class="collector-cover__spine" aria-hidden="true"></div><div class="collector-cover__pages" aria-hidden="true"></div>
        <div class="collector-cover__face"><p class="collector-cover__publisher">ARCHON PUBLISHING HOUSE</p><span class="collector-cover__rule" aria-hidden="true"></span><p class="collector-cover__eyebrow">YOUR PUBLISHING JOURNEY</p><h1 id="book-cover-title">Your Publishing<br>Journey</h1><p class="collector-cover__byline" data-book-cover-byline>Written for a Future Author</p><p class="collector-cover__imprint">Published by Archon Publishing House</p><button class="collector-cover__open" type="button" data-book-open>Open My Book</button></div>
    </section>
    <section class="open-book" data-book-open-panel aria-label="Your Archon publishing journey">
        <div class="book-ribbon" aria-hidden="true">ARCHON</div>
        <div class="book-toolbar"><a href="/contents" class="book-toolbar__link">Contents</a><span class="book-toolbar__chapter" data-book-current-label><?= $isContentsChapter?'Table of Contents':'Opening Pages' ?></span><div><button type="button" data-book-change-name>Change name</button><button type="button" data-book-remove-name>Remove name</button></div></div>
        <div class="book-opening-spread" data-book-opening-spread>
            <article class="book-leaf book-leaf--left"><p class="book-kicker">A PERSONAL FRONTISPIECE</p><div class="book-ornament" aria-hidden="true">*</div><h2>Welcome, <span data-book-display-name>Future Author</span>.</h2><p>Every meaningful eBook begins with a clear idea and a publishing partner who takes it seriously.</p><p class="book-promise">Archon brings writing, editorial care and publishing craft together -- so your story can become a book worth sharing.</p><span class="book-page-number">i</span></article>
            <article class="book-leaf book-leaf--right"><p class="book-kicker">TABLE OF CONTENTS</p><h2>Inside your journey</h2><?php require __DIR__.'/book-toc.php'; ?><span class="book-page-number">ii</span></article>
        </div>
        <div class="book-chapter-spread<?= $isContentsChapter?' is-current':'' ?>" data-book-home-spread tabindex="-1">
            <article class="book-leaf book-leaf--chapter"><p class="book-kicker"><?= $isContentsChapter?'CHAPTER GUIDE':'CHAPTER I' ?></p><h2><?= $isContentsChapter?'The chapters ahead':'A book begins with possibility.' ?></h2><p><?= $isContentsChapter?'Choose a chapter to explore Archon\'s writing expertise, process and ways to begin.':'Explore the opening chapter, then turn to the contents whenever you are ready to find the right next step.' ?></p><div class="book-ink-divider" aria-hidden="true">*</div><span class="book-page-number">1</span></article>
            <article class="book-leaf book-leaf--content"><div class="book-content" data-book-content><?php require $contentView; ?></div><span class="book-page-number">2</span></article>
        </div>
        <div class="book-controls" aria-label="Book controls"><button type="button" data-book-previous aria-label="Show previous page">Previous page</button><span data-book-progress>Opening pages</span><button type="button" data-book-next aria-label="Show Home chapter">Next page</button><button type="button" data-book-close>Close book</button></div>
    </section>
    <noscript><div class="book-noscript"><p><strong>This book is fully readable without animation.</strong> Use the Table of Contents to explore every chapter.</p></div></noscript>
</section>
