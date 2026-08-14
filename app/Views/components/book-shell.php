<?php $pages=['frontispiece','contents','home','services','genres','process','work','authors','reviews','quote','contact','final']; ?>
<section class="archon-book" data-book-experience data-book-chapter="<?=Security::e($bookChapter??'home')?>">
 <div class="book-atmosphere" aria-hidden="true"></div>
 <div class="physical-book" data-physical-book>
  <div class="page-block" aria-hidden="true"></div>
  <div class="book-block" data-book-block aria-label="Archon eBook journey">
   <div class="book-spread"><article class="paper-page paper-page--left" data-book-left tabindex="-1"></article><div class="book-spine-open" aria-hidden="true"></div><article class="paper-page paper-page--right" data-book-right></article><div class="turning-leaf" data-turn-leaf aria-hidden="true"><div class="turning-leaf__front" data-turn-front></div><div class="turning-leaf__back" data-turn-back></div></div></div>
  </div>
  <div class="hardcover" data-hardcover>
   <div class="hardcover__front"><div class="cover-emboss"><p>ARCHON PUBLISHING HOUSE</p><i></i><span>YOUR EBOOK JOURNEY</span><h1>Your eBook<br>Journey</h1><strong data-book-cover-byline>Created for a Future Author</strong><small>Brought to Life by Archon Publishing House</small><button type="button" data-book-open>Open My Book</button></div></div>
   <div class="hardcover__inside"><p>ARCHON PUBLISHING HOUSE</p><div>*</div><span>A book begins with a name.</span></div>
  </div>
  <div class="book-edge book-edge--right" aria-hidden="true"></div><div class="book-edge book-edge--bottom" aria-hidden="true"></div>
 </div>
 <div class="book-toolbar" aria-label="Book controls"><button type="button" data-book-previous aria-label="Previous pages">Previous</button><button type="button" data-book-next aria-label="Next pages">Next</button><button type="button" data-book-contents>Contents</button><span data-book-progress>Closed cover</span><button type="button" data-book-change-name>Change name</button><button type="button" data-book-remove-name>Remove name</button><button type="button" data-book-close>Close book</button></div>
 <?php require __DIR__.'/book-pages.php'; ?>
 <noscript><div class="book-noscript"><h1>Archon eBook Writing Services</h1><p>Our website remains available without animation. <a href="/contents">View the Table of Contents</a> or browse our <a href="/services">writing services</a>.</p><?php require $contentView; ?></div></noscript>
</section>
