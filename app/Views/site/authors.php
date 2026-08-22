<section class="page-hero">
    <p class="eyebrow">AUTHORS & CLIENTS</p>
    <h1>Ideas shaped into <em>credible eBooks.</em></h1>
    <p>Meet a few of the founders, coaches, consultants and first-time authors whose expertise has been shaped with Archon’s writing and editorial support.</p>
</section>

<section class="section">
    <form class="author-search" method="get">
        <label class="sr-only" for="author-search">Search authors and clients</label>
        <input id="author-search" name="q" value="<?=Security::e($q)?>" placeholder="Search by name or expertise">
        <button class="button outline">Search</button>
    </form>

    <div class="author-grid">
        <?php foreach($authors as $author):?>
            <a class="author-card" href="/authors/<?=Security::e($author['slug'])?>">
                <div class="portrait"><?=Security::e(mb_substr($author['name'],0,1))?></div>
                <h2><?=Security::e($author['name'])?></h2>
                <p><?=Security::e($author['tagline']??'Archon client author')?></p>
            </a>
        <?php endforeach;?>
    </div>

    <?php if(!$authors):?>
        <div class="empty">
            <h2>No matching profile found</h2>
            <p>Try another name, expertise area or browse the full client-author list.</p>
            <a class="button" href="/authors">View all profiles</a>
        </div>
    <?php endif;?>
</section>
