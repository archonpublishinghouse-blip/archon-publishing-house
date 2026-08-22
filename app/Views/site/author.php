<section class="author-profile section">
    <div class="portrait large"><?=Security::e(mb_substr($author['name'], 0, 1))?></div>
    <div>
        <p class="eyebrow">ARCHON CLIENT AUTHOR</p>
        <h1><?=Security::e($author['name'])?></h1>
        <p><?=nl2br(Security::e($author['bio']))?></p>
        <?php if ($author['website']): ?>
            <a href="<?=Security::e($author['website'])?>" rel="noopener">Author website &nearr;</a>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <h2>Selected eBook work</h2>
    <?php if ($books): ?>
        <div class="book-grid">
            <?php foreach ($books as $book): ?>
                <article class="book-card">
                    <div class="cover cover-<?=((int)$book['id'] % 5) + 1?>">
                        <span><?=Security::e($book['title'])?></span>
                        <i>ARCHON</i>
                    </div>
                    <div>
                        <h3><?=Security::e($book['title'])?></h3>
                        <?php if (!empty($book['short_description'])): ?>
                            <p><?=Security::e($book['short_description'])?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Approved portfolio details will be added here when they are available. If you would like to create something similar, Archon can help shape your idea into a complete eBook.</p>
        <a class="button outline" href="/quote">Share your idea</a>
    <?php endif; ?>
</section>
