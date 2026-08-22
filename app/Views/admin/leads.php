<?php
use App\Core\Security;

$activeAdmin = 'leads';
$sourceLabels = ['all' => 'All leads', 'quotes' => 'Quote requests', 'contacts' => 'Contact messages'];
?>
<section class="admin-shell">
    <?php require dirname(__DIR__).'/components/admin-sidebar.php'; ?>
    <main class="admin-main">
        <p class="eyebrow">PRIVATE CRM</p>
        <h1><?=$canManageAll ? 'Lead inbox' : 'My assigned leads'?></h1>
        <div class="stat-grid crm-stat-grid">
            <article><span>Total quotes</span><b><?=number_format($metrics['quotes_total'])?></b></article>
            <article><span>New quotes</span><b><?=number_format($metrics['quotes_new'])?></b></article>
            <article><span>Total messages</span><b><?=number_format($metrics['contacts_total'])?></b></article>
            <article><span>New messages</span><b><?=number_format($metrics['contacts_new'])?></b></article>
        </div>
        <form method="get" action="/admin/leads" class="filters crm-filters">
            <label>Source
                <select name="source">
                    <?php foreach ($sourceLabels as $value => $label): ?>
                        <option value="<?=$value?>" <?=$source === $value ? 'selected' : ''?>><?=Security::e($label)?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Status
                <select name="status">
                    <option value="">Any status</option>
                    <?php foreach ($statusOptions as $option): ?>
                        <option value="<?=Security::e($option)?>" <?=$status === $option ? 'selected' : ''?>><?=Security::e(ucwords(str_replace('_', ' ', $option)))?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Search
                <input name="q" value="<?=Security::e($q)?>" placeholder="Name, email, title, subject">
            </label>
            <?php if ($canManageAll): ?>
                <label>Assigned to
                    <select name="assigned">
                        <option value="">Anyone</option>
                        <option value="unassigned" <?=$assigned === 'unassigned' ? 'selected' : ''?>>Unassigned</option>
                        <option value="me" <?=$assigned === 'me' ? 'selected' : ''?>>Me</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?=$employee['id']?>" <?=$assigned === (string)$employee['id'] ? 'selected' : ''?>><?=Security::e($employee['name'])?><?=($employee['role'] ?? '') === 'employee' ? ' · Team member' : ' · CRM admin'?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>
            <button class="button">Filter leads</button>
            <a href="/admin/leads">Reset</a>
        </form>
        <?php if ($canManageAll): ?>
            <div class="crm-export-row">
                <a href="/admin/export/quotes">Export quotes CSV</a>
                <a href="/admin/export/contacts">Export messages CSV</a>
            </div>
        <?php endif; ?>
        <section class="panel crm-panel">
            <div class="table-wrap">
                <table class="crm-table">
                    <thead><tr><th>Lead</th><th>Need</th><th>Contact</th><th>Status</th><th>Assigned</th><th>Received</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td>
                                <strong><?=Security::e($lead['name'])?></strong>
                                <small><?=Security::e($lead['source'] === 'quote' ? 'Quote request' : 'Contact message')?></small>
                            </td>
                            <td>
                                <strong><?=Security::e($lead['title'] ?: 'General enquiry')?></strong>
                                <small><?=Security::e($lead['service_title'] ?: $lead['genre'] ?: '')?></small>
                            </td>
                            <td>
                                <a href="mailto:<?=Security::e($lead['email'])?>"><?=Security::e($lead['email'])?></a>
                                <?php if (!empty($lead['phone'])): ?><small><?=Security::e($lead['phone'])?></small><?php endif; ?>
                            </td>
                            <td><span class="status <?=Security::e($lead['status'])?>"><?=Security::e(str_replace('_', ' ', $lead['status']))?></span></td>
                            <td><?=Security::e($lead['assigned_name'] ?: 'Unassigned')?></td>
                            <td><?=Security::e(date('M j, Y g:ia', strtotime($lead['created_at'])))?></td>
                            <td><a class="button small" href="/admin/leads/<?=Security::e($lead['source'])?>/<?=$lead['id']?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$leads): ?>
                        <tr><td colspan="7">No matching leads found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</section>
