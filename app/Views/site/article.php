<article class="article section narrow">
    <p class="eyebrow">ARCHON JOURNAL · <?=date('F j, Y',strtotime($post['published_at']))?></p>
    <h1><?=Security::e($post['title'])?></h1>
    <p class="lead"><?=Security::e($post['excerpt'])?></p>
    <div><?=nl2br(Security::e($post['body']))?></div>
    <p><a class="button outline" href="/quote">Start your eBook</a></p>
</article>
