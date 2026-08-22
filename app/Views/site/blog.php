<section class="page-hero">
    <p class="eyebrow">THE ARCHON JOURNAL</p>
    <h1>Notes for people who want to <em>write a better eBook.</em></h1>
    <p>Practical guidance on planning, structuring, writing and refining eBooks for business, expertise, education and personal storytelling.</p>
</section>

<section class="section article-grid">
    <?php foreach($posts as $post):?>
        <a href="/blog/<?=Security::e($post['slug'])?>">
            <span>JOURNAL · <?=date('M Y',strtotime($post['published_at']))?></span>
            <h2><?=Security::e($post['title'])?></h2>
            <p><?=Security::e($post['excerpt'])?></p>
            <b>Read article →</b>
        </a>
    <?php endforeach;?>

    <?php if(!$posts):?>
        <div class="empty">
            <h2>Journal notes are being prepared.</h2>
            <p>In the meantime, you can tell us about the eBook you want to create.</p>
            <a class="button" href="/quote">Start your eBook</a>
        </div>
    <?php endif;?>
</section>
