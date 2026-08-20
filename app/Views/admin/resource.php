<?php
use App\Core\Security;

$activeAdmin = $name;
?>
<section class="admin-shell">
    <?php require dirname(__DIR__).'/components/admin-sidebar.php'; ?>
    <main class="admin-main">
        <p class="eyebrow">CONTENT MANAGEMENT</p>
        <h1><?=Security::e($cfg['title'])?></h1>
        <?php if (($cfg['creatable'] ?? true)): ?>
            <details class="panel">
                <summary>Add <?=Security::e(rtrim($cfg['title'], 's'))?></summary>
                <form method="post" action="/admin/<?=Security::e($name)?>/create" class="admin-form">
                    <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                    <?php foreach ($cfg['fields'] as $field): ?>
                        <label><?=Security::e(ucwords(str_replace('_', ' ', $field)))?>
                            <?php if (in_array($field, ['description', 'bio', 'body', 'message', 'benefits'], true)): ?>
                                <textarea name="<?=Security::e($field)?>" rows="4"></textarea>
                            <?php else: ?>
                                <input name="<?=Security::e($field)?>" <?=str_contains($field, 'price') ? 'type="number" step=".01"' : ''?>>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                    <button class="button">Create</button>
                </form>
            </details>
        <?php endif; ?>
        <div class="records">
            <?php foreach ($rows as $row): ?>
                <details class="panel record">
                    <summary><span>#<?=$row['id']?></span> <?=Security::e($row['title'] ?? $row['name'] ?? $row['email'] ?? 'Record')?></summary>
                    <form method="post" action="/admin/<?=Security::e($name)?>/edit" class="admin-form">
                        <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                        <input type="hidden" name="id" value="<?=$row['id']?>">
                        <?php foreach ($cfg['fields'] as $field): ?>
                            <label><?=Security::e(ucwords(str_replace('_', ' ', $field)))?>
                                <?php if (in_array($field, ['description', 'bio', 'body', 'message', 'benefits'], true)): ?>
                                    <textarea name="<?=Security::e($field)?>" rows="4"><?=Security::e($row[$field] ?? '')?></textarea>
                                <?php else: ?>
                                    <input name="<?=Security::e($field)?>" value="<?=Security::e((string)($row[$field] ?? ''))?>" <?=str_contains($field, 'price') ? 'type="number" step=".01"' : ''?>>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                        <button class="button">Save changes</button>
                    </form>
                    <form method="post" action="/admin/<?=Security::e($name)?>/delete" class="danger-action" onsubmit="return confirm('Remove this record?')">
                        <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                        <input type="hidden" name="id" value="<?=$row['id']?>">
                        <button class="text-button">Delete record</button>
                    </form>
                </details>
            <?php endforeach; ?>
        </div>
    </main>
</section>
