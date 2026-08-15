<section class="page-hero compact">
    <p class="eyebrow">ARCHON PUBLISHING HOUSE</p>
    <h1>Table of Contents</h1>
    <p>Choose a chapter or continue through the interactive journey from the home page.</p>
</section>

<section class="section service-list">
    <?php foreach ($chapters as $chapter): ?>
        <article>
            <span><?=Security::e($chapter['number'] ?: '•')?></span>
            <div>
                <h2><?=Security::e($chapter['label'])?></h2>
                <p><?=Security::e($chapter['title'])?></p>
            </div>
            <?php if (!empty($chapter['available'])): ?>
                <a class="button outline" href="<?=Security::e($chapter['href'])?>">Open</a>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
