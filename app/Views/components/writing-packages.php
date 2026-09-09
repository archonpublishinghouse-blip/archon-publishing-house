<?php
$packageConfig = \App\Services\PackageService::get();
$visiblePackages = \App\Services\PackageService::visible($packageConfig);
?>
<?php if ($visiblePackages): ?>
<section class="writing-packages" id="writing-packages" aria-labelledby="writing-packages-title">
    <header class="writing-packages__heading">
        <p class="writing-packages__eyebrow">EBOOK WRITING PACKAGES</p>
        <h2 id="writing-packages-title"><?=Security::e($packageConfig['heading'])?></h2>
        <p><?=Security::e($packageConfig['intro'])?></p>
    </header>
    <div class="writing-packages__grid">
        <?php foreach ($visiblePackages as $id => $package): ?>
            <article class="writing-package <?=$packageConfig['featured'] === $id ? 'writing-package--featured' : ''?>" aria-labelledby="package-title-<?=$id?>">
                <?php if ($package['badge'] !== ''): ?><p class="writing-package__badge"><?=Security::e($package['badge'])?></p><?php endif; ?>
                <p class="writing-package__tagline"><?=Security::e($package['tagline'])?></p>
                <h3 id="package-title-<?=$id?>"><?=Security::e($package['name'])?></h3>
                <p class="writing-package__description"><?=Security::e($package['description'])?></p>
                <p class="writing-package__price"><?=Security::e($package['price'])?></p>
                <p class="writing-package__audience"><?=Security::e($package['audience'])?></p>
                <ul><?php foreach (explode("\n", $package['features']) as $feature): ?><li><?=Security::e($feature)?></li><?php endforeach; ?></ul>
                <p class="writing-package__timeline"><?=Security::e($package['timeline'])?></p>
                <a class="writing-package__button" href="/quote?package=<?=$id?>"><?=Security::e($package['button'])?><span aria-hidden="true">&rarr;</span></a>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if ($packageConfig['note'] !== ''): ?><p class="writing-packages__note"><?=Security::e($packageConfig['note'])?></p><?php endif; ?>
</section>
<?php endif; ?>
