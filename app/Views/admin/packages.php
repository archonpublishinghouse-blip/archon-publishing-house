<?php
use App\Core\Security;
$activeAdmin = 'packages';
?>
<link rel="stylesheet" href="/assets/css/writing-packages.css?v=20260909-packages1">
<section class="admin-shell">
    <?php require dirname(__DIR__) . '/components/admin-sidebar.php'; ?>
    <main class="admin-main">
        <p class="eyebrow">WEBSITE CONTENT</p>
        <h1>Writing packages</h1>
        <p>Edit the three packages below. Changes appear on the homepage and Services page after saving. Prices are display text; package enquiries go to the existing lead CRM.</p>
        <?php if ($errors): ?>
            <div class="panel package-editor__errors" role="alert">
                <h2>Please check these fields</h2>
                <ul><?php foreach ($errors as $error): ?><li><?=Security::e($error)?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
        <form method="post" action="/admin/packages" class="package-editor">
            <input type="hidden" name="_token" value="<?=Security::csrf()?>">
            <fieldset class="panel">
                <legend>Package section</legend>
                <label>Section heading<input name="config[heading]" value="<?=Security::e($packageConfig['heading'])?>" maxlength="180" required></label>
                <label>Introduction<textarea name="config[intro]" rows="3" maxlength="900" required><?=Security::e($packageConfig['intro'])?></textarea></label>
                <label>Note below packages<textarea name="config[note]" rows="2" maxlength="500"><?=Security::e($packageConfig['note'])?></textarea></label>
                <label>Highlighted package<select name="config[featured]">
                    <option value="">None</option>
                    <?php foreach ($packageConfig['packages'] as $id => $item): ?>
                        <option value="<?=$id?>" <?=$packageConfig['featured'] === $id ? 'selected' : ''?>><?=Security::e($item['name'] ?: ucfirst($id))?></option>
                    <?php endforeach; ?>
                </select></label>
            </fieldset>
            <?php foreach ($packageConfig['packages'] as $id => $item): ?>
                <fieldset class="panel">
                    <legend><?=Security::e(ucfirst($id))?> package</legend>
                    <div class="package-editor__grid">
                        <label>Package name<input name="config[packages][<?=$id?>][name]" value="<?=Security::e($item['name'])?>" maxlength="60" required></label>
                        <label>Tagline<input name="config[packages][<?=$id?>][tagline]" value="<?=Security::e($item['tagline'])?>" maxlength="120" required></label>
                        <label>Price text<input name="config[packages][<?=$id?>][price]" value="<?=Security::e($item['price'])?>" placeholder="Request a quote or From $1,500" maxlength="100" required></label>
                        <label>Timeline<input name="config[packages][<?=$id?>][timeline]" value="<?=Security::e($item['timeline'])?>" maxlength="160" required></label>
                        <label>Best suited for<input name="config[packages][<?=$id?>][audience]" value="<?=Security::e($item['audience'])?>" maxlength="240" required></label>
                        <label>Badge (optional)<input name="config[packages][<?=$id?>][badge]" value="<?=Security::e($item['badge'])?>" maxlength="80"></label>
                    </div>
                    <label>Description<textarea name="config[packages][<?=$id?>][description]" rows="3" maxlength="900" required><?=Security::e($item['description'])?></textarea></label>
                    <label>Included features (one per line, up to 15)<textarea name="config[packages][<?=$id?>][features]" rows="7" maxlength="2400" required><?=Security::e($item['features'])?></textarea></label>
                    <div class="package-editor__grid">
                        <label>Button label<input name="config[packages][<?=$id?>][button]" value="<?=Security::e($item['button'])?>" maxlength="60" required></label>
                        <label>Display order<input type="number" name="config[packages][<?=$id?>][order]" value="<?=$item['order']?>" min="0" max="99" required></label>
                    </div>
                    <label class="consent"><input type="checkbox" name="config[packages][<?=$id?>][enabled]" value="1" <?=$item['enabled'] ? 'checked' : ''?>> Show this package on the website</label>
                </fieldset>
            <?php endforeach; ?>
            <div class="package-editor__actions">
                <button class="button" type="submit">Save all packages</button>
                <a class="button outline" href="/#writing-packages">View packages on website</a>
            </div>
        </form>
    </main>
</section>
