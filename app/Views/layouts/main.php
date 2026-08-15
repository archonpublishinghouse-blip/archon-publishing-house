<?php
use App\Core\Security;

$siteTitle = $title ?? 'Archon Publishing House';
$flashSuccess = Security::flash('success');
$flashError = Security::flash('error');
$baseUrl = rtrim(\App\Core\Env::get('APP_URL', 'http://localhost'), '/');
$canonical = $baseUrl . (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$description = $settings['seo.description'] ?? 'Archon Publishing House helps authors write, edit, design and publish professional eBooks.';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="<?=Security::e($description)?>">
    <link rel="canonical" href="<?=Security::e($canonical)?>">
    <meta property="og:title" content="<?=Security::e($siteTitle)?>">
    <meta property="og:description" content="<?=Security::e($description)?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?=Security::e($baseUrl)?>/assets/images/brand/archon-logo-parchment.webp">
    <title><?=Security::e($siteTitle)?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <script type="application/ld+json">{"@context":"https://schema.org","@type":"Organization","name":"Archon Publishing House","url":"<?=Security::e($baseUrl)?>","logo":"<?=Security::e($baseUrl)?>/assets/images/brand/archon-logo-parchment.webp"}</script>
</head>
<body>
    <a class="skip" href="#content">Skip to content</a>
    <div class="announcement">
        <?=Security::e($settings['announcement.text'] ?? 'Complimentary consultation for new publishing projects')?>
        <a href="/quote"><?=Security::e($settings['announcement.link_label'] ?? 'Request yours')?></a>
    </div>
    <header class="site-header">
        <a href="/" class="brand" aria-label="Archon Publishing House home">
            <img class="brand-logo" src="/assets/images/brand/archon-logo-parchment.webp" alt="">
            <span><b>ARCHON</b><small>PUBLISHING HOUSE</small></span>
        </a>
        <button class="menu-toggle" aria-expanded="false" aria-controls="mainnav">Menu</button>
        <nav id="mainnav">
            <a href="/">Home</a>
            <a href="/services">Services</a>
            <a href="/authors">Authors</a>
            <a href="/about">About</a>
            <a href="/blog">Journal</a>
            <a href="/contact">Contact</a>
        </nav>
        <div class="nav-actions"><a class="button small" href="/quote">Request a quote</a></div>
    </header>
    <?php if ($flashSuccess || $flashError): ?>
        <div class="toast <?=$flashError ? 'error' : ''?>" role="status"><?=Security::e($flashError ?: $flashSuccess)?></div>
    <?php endif; ?>
    <main id="content"><?php require $contentView; ?></main>
    <section class="newsletter">
        <div>
            <p class="eyebrow">THE ARCHON LETTER</p>
            <h2>A measured note on the art of publishing.</h2>
        </div>
        <form action="/newsletter" method="post">
            <input type="hidden" name="_token" value="<?=Security::csrf()?>">
            <label class="sr-only" for="newsletter-email">Email address</label>
            <input id="newsletter-email" type="email" name="email" placeholder="Your email address" required>
            <label class="consent"><input type="checkbox" name="consent" required> I agree to receive editorial updates.</label>
            <button class="button cream" type="submit">Subscribe</button>
        </form>
    </section>
    <footer>
        <div class="footer-brand">
            <img class="footer-logo" src="/assets/images/brand/archon-logo-parchment.webp" alt="Archon Publishing House">
            <p>From ideas to published stories.</p>
        </div>
        <div>
            <h3>Explore</h3>
            <a href="/services">Publishing services</a>
            <a href="/authors">Our authors</a>
            <a href="/about">About Archon</a>
            <a href="/blog">Journal</a>
        </div>
        <div>
            <h3>Support</h3>
            <a href="/contact">Contact</a>
            <a href="/privacy">Privacy</a>
            <a href="/terms">Terms</a>
        </div>
        <div>
            <h3>Start a project</h3>
            <p>Tell us about your eBook idea, outline or manuscript.</p>
            <a href="/quote" class="button small">Request a quote</a>
        </div>
        <p class="copyright">&copy; <?=date('Y')?> Archon Publishing House. Demo content only.</p>
    </footer>
    <script src="/assets/js/main.js" defer></script>
</body>
</html>
