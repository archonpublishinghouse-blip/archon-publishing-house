<?php
use App\Core\Security;

$activeAdmin = 'leads';
$isQuote = $kind === 'quote';
$title = $isQuote ? ($lead['book_title'] ?: 'Untitled eBook project') : ($lead['subject'] ?: 'Contact message');
$description = $isQuote ? $lead['description'] : $lead['message'];
?>
<section class="admin-shell">
    <?php require dirname(__DIR__).'/components/admin-sidebar.php'; ?>
    <main class="admin-main">
        <p class="eyebrow"><a href="/admin/leads">Lead CRM</a> / <?=Security::e($isQuote ? 'Quote request' : 'Contact message')?></p>
        <h1><?=Security::e($title)?></h1>
        <div class="crm-detail-grid">
            <section class="panel crm-panel">
                <div class="crm-heading">
                    <div>
                        <p class="eyebrow">CLIENT</p>
                        <h2><?=Security::e($lead['name'])?></h2>
                    </div>
                    <span class="status <?=Security::e($lead['status'])?>"><?=Security::e(str_replace('_', ' ', $lead['status']))?></span>
                </div>
                <dl class="crm-fields">
                    <dt>Email</dt><dd><a href="mailto:<?=Security::e($lead['email'])?>"><?=Security::e($lead['email'])?></a></dd>
                    <?php if (!empty($lead['phone'])): ?><dt>Phone / WhatsApp</dt><dd><?=Security::e($lead['phone'])?></dd><?php endif; ?>
                    <dt>Assigned to</dt><dd><?=Security::e($lead['assigned_name'] ?: 'Unassigned')?></dd>
                    <dt>Received</dt><dd><?=Security::e(date('M j, Y g:ia', strtotime($lead['created_at'])))?></dd>
                    <?php if ($isQuote): ?>
                        <?php if (!empty($lead['service_title'])): ?><dt>Service</dt><dd><?=Security::e($lead['service_title'])?></dd><?php endif; ?>
                        <?php foreach (['genre' => 'Genre', 'word_count' => 'Estimated word count', 'project_stage' => 'Project stage', 'completion_date' => 'Completion date', 'budget_range' => 'Budget range'] as $field => $label): ?>
                            <?php if (!empty($lead[$field])): ?><dt><?=Security::e($label)?></dt><dd><?=Security::e($lead[$field])?></dd><?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </dl>
                <h3>Client message</h3>
                <div class="crm-message"><?=nl2br(Security::e($description))?></div>
                <?php if ($attachments): ?>
                    <h3>Attachments</h3>
                    <ul class="crm-attachments">
                        <?php foreach ($attachments as $attachment): ?>
                            <li><a href="/admin/leads/quote-attachments/<?=$attachment['id']?>"><?=Security::e($attachment['original_name'])?></a> <small><?=number_format(((int)$attachment['file_size']) / 1024, 1)?> KB</small></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
            <aside class="crm-side">
                <?php if ($canManageAll): ?>
                    <section class="panel">
                        <h2>Assign lead</h2>
                        <form method="post" action="/admin/leads/<?=Security::e($kind)?>/<?=$lead['id']?>/assign">
                            <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                            <label>Owner
                                <select name="assigned_admin_id">
                                    <option value="0">Unassigned</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?=$employee['id']?>" <?=((int)($lead['assigned_admin_id'] ?? 0) === (int)$employee['id']) ? 'selected' : ''?>><?=Security::e($employee['name'])?><?=($employee['role'] ?? '') === 'employee' ? ' · Team member' : ' · CRM admin'?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <button class="button">Assign lead</button>
                        </form>
                    </section>
                <?php endif; ?>
                <section class="panel">
                    <h2>Update status</h2>
                    <form method="post" action="/admin/leads/<?=Security::e($kind)?>/<?=$lead['id']?>/status">
                        <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                        <label>Status
                            <select name="status">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?=Security::e($status)?>" <?=$lead['status'] === $status ? 'selected' : ''?>><?=Security::e(ucwords(str_replace('_', ' ', $status)))?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button class="button">Save status</button>
                    </form>
                </section>
                <section class="panel">
                    <h2>Add note</h2>
                    <form method="post" action="/admin/leads/<?=Security::e($kind)?>/<?=$lead['id']?>/notes">
                        <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                        <label>Internal note<textarea name="body" rows="5" required></textarea></label>
                        <button class="button">Save note</button>
                    </form>
                </section>
                <?php if ($canManageAll): ?>
                    <section class="panel danger-zone">
                        <h2>Remove lead</h2>
                        <p>This permanently removes the lead, its timeline records, and any quote attachments.</p>
                        <form method="post" action="/admin/leads/<?=Security::e($kind)?>/<?=$lead['id']?>/delete">
                            <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                            <label>Type DELETE to confirm<input name="confirm_delete" autocomplete="off" required></label>
                            <button class="button danger">Remove lead</button>
                        </form>
                    </section>
                <?php endif; ?>
            </aside>
        </div>
        <section class="panel crm-timeline">
            <div class="crm-heading">
                <div>
                    <p class="eyebrow">TIMELINE</p>
                    <h2>Lead history</h2>
                </div>
            </div>
            <?php foreach ($notes as $note): ?>
                <article>
                    <span class="crm-timeline__action"><?=Security::e(ucwords(str_replace('_', ' ', $note['action'] ?? 'note')))?></span>
                    <p><?=nl2br(Security::e($note['body']))?></p>
                    <small><?=Security::e($note['admin_name'] ?: 'System')?> · <?=Security::e(date('M j, Y g:ia', strtotime($note['created_at'])))?></small>
                </article>
            <?php endforeach; ?>
            <?php if (!$notes): ?><p>No lead activity yet.</p><?php endif; ?>
        </section>
    </main>
</section>
