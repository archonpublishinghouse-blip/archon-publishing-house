<link rel="stylesheet" href="/assets/css/writing-guide.css?v=20260909-content1">
<link rel="stylesheet" href="/assets/css/writing-packages.css?v=20260909-packages1">

<section class="page-hero">
    <p class="eyebrow">EBOOK WRITING SERVICES</p>
    <h1>Professional writing support for <em>your next eBook.</em></h1>
    <p>Bring us an idea, outline, manuscript, business concept, or specialist knowledge. We help shape it into a clear, polished eBook with a reader-focused structure.</p>
    <a class="button" href="/quote">Discuss your project</a>
</section>

<section class="section service-list" aria-label="Our eBook services">
    <?php foreach ($services as $i => $service): ?>
        <article>
            <span><?=str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT)?></span>
            <div>
                <h2><?=Security::e($service['title'])?></h2>
                <p><?=Security::e($service['summary'])?></p>
            </div>
            <a class="button outline" href="/services/<?=Security::e(rawurlencode($service['slug']))?>">Learn more<span class="writing-guide-sr-only"> about <?=Security::e($service['title'])?></span></a>
        </article>
    <?php endforeach; ?>
    <?php if (!$services): ?>
        <div class="empty">
            <h2>Services are being prepared.</h2>
            <p>Our eBook writing services will appear here shortly.</p>
            <a class="button" href="/quote">Request a consultation</a>
        </div>
    <?php endif; ?>
</section>

<div class="writing-guide-container">
    <?php require dirname(__DIR__) . '/components/writing-packages.php'; ?>
    <?php require dirname(__DIR__) . '/components/writing-guide.php'; ?>
</div>
