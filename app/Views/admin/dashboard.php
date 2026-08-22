<?php
use App\Core\Security;

$activeAdmin = 'dashboard';
?>
<section class="admin-shell">
    <?php require dirname(__DIR__).'/components/admin-sidebar.php'; ?>
    <main class="admin-main">
        <p class="eyebrow">CLIENT ENQUIRY CONTROL ROOM</p>
        <h1><?=$canManageAll ? 'Lead dashboard' : 'My lead dashboard'?></h1>
        <div class="stat-grid">
            <article><span>New quote leads</span><b><?=number_format($stats['new_quotes'])?></b></article>
            <article><span>Open quote pipeline</span><b><?=number_format($stats['open_quotes'])?></b></article>
            <article><span>Unread messages</span><b><?=number_format($stats['new_messages'])?></b></article>
            <article><span>Converted projects</span><b><?=number_format($stats['converted'])?></b></article>
        </div>
        <section class="panel crm-panel">
            <div class="crm-heading">
                <div>
                    <p class="eyebrow">RECENT LEADS</p>
                    <h2><?=$canManageAll ? 'Latest client enquiries' : 'Latest assigned enquiries'?></h2>
                </div>
                <a class="button outline" href="/admin/leads">Open Lead CRM</a>
            </div>
            <div class="table-wrap">
                <table class="crm-table">
                    <thead><tr><th>Lead</th><th>Type</th><th>Owner</th><th>Status</th><th>Received</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recentLeads as $lead): ?>
                        <tr>
                            <td><strong><?=Security::e($lead['name'])?></strong><small><?=Security::e($lead['email'])?></small></td>
                            <td><?=Security::e($lead['source'] === 'quote' ? 'Quote request' : 'Contact message')?></td>
                            <td><?=Security::e($lead['assigned_name'] ?: 'Unassigned')?></td>
                            <td><span class="status <?=Security::e($lead['status'])?>"><?=Security::e(str_replace('_', ' ', $lead['status']))?></span></td>
                            <td><?=Security::e(date('M j, Y', strtotime($lead['created_at'])))?></td>
                            <td><a href="/admin/leads/<?=Security::e($lead['source'])?>/<?=$lead['id']?>">Review</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$recentLeads): ?>
                        <tr><td colspan="6">No leads have arrived yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</section>
