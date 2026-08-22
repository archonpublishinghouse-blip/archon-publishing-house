<?php
use App\Core\Security;

$activeAdmin = 'lead-create';
?>
<section class="admin-shell">
    <?php require dirname(__DIR__).'/components/admin-sidebar.php'; ?>
    <main class="admin-main">
        <p class="eyebrow">MANUAL CRM ENTRY</p>
        <h1>Create a lead</h1>
        <form method="post" action="/admin/leads/create" class="panel crm-manual-lead">
            <input type="hidden" name="_token" value="<?=Security::csrf()?>">
            <section>
                <div class="crm-heading">
                    <div>
                        <p class="eyebrow">LEAD TYPE</p>
                        <h2>Choose how this enquiry arrived</h2>
                    </div>
                </div>
                <label>Type
                    <select name="kind" data-lead-kind>
                        <option value="quote">Quote / eBook project lead</option>
                        <option value="contact">General contact lead</option>
                    </select>
                </label>
            </section>

            <section>
                <div class="crm-heading">
                    <div>
                        <p class="eyebrow">CLIENT</p>
                        <h2>Client details</h2>
                    </div>
                </div>
                <div class="admin-form">
                    <label>Full name<input name="name" required></label>
                    <label>Email address<input name="email" type="email" required></label>
                    <label>Phone / WhatsApp<input name="phone"></label>
                    <label>Assigned to
                        <select name="assigned_admin_id">
                            <option value="0">Unassigned</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?=$employee['id']?>"><?=Security::e($employee['name'])?><?=($employee['role'] ?? '') === 'employee' ? ' - Team member' : ' - CRM admin'?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </section>

            <section class="manual-lead-quote">
                <div class="crm-heading">
                    <div>
                        <p class="eyebrow">EBOOK PROJECT</p>
                        <h2>Quote lead details</h2>
                    </div>
                </div>
                <div class="admin-form">
                    <label>Service
                        <select name="service_id">
                            <option value="">Not sure yet</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?=$service['id']?>"><?=Security::e($service['title'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Status
                        <select name="quote_status">
                            <?php foreach ($quoteStatuses as $status): ?>
                                <option value="<?=Security::e($status)?>"><?=Security::e(ucwords(str_replace('_', ' ', $status)))?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Book title / working idea<input name="book_title"></label>
                    <label>Genre / subject<input name="genre"></label>
                    <label>Estimated word count<input name="word_count"></label>
                    <label>Project stage<input name="project_stage" placeholder="Idea, outline, draft, business concept..."></label>
                    <label>Preferred completion date<input name="completion_date" type="date"></label>
                    <label>Budget range<input name="budget_range"></label>
                </div>
                <label>Project description<textarea name="description" rows="6" placeholder="Summarise what the client wants help writing."></textarea></label>
            </section>

            <section class="manual-lead-contact">
                <div class="crm-heading">
                    <div>
                        <p class="eyebrow">GENERAL ENQUIRY</p>
                        <h2>Contact lead details</h2>
                    </div>
                </div>
                <div class="admin-form">
                    <label>Subject<input name="subject" placeholder="Manual lead"></label>
                    <label>Status
                        <select name="contact_status">
                            <?php foreach ($contactStatuses as $status): ?>
                                <option value="<?=Security::e($status)?>"><?=Security::e(ucwords(str_replace('_', ' ', $status)))?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <label>Message<textarea name="message" rows="6" placeholder="Summarise the client enquiry."></textarea></label>
            </section>

            <div class="crm-form-actions">
                <button class="button">Create lead</button>
                <a href="/admin/leads">Cancel</a>
            </div>
        </form>
    </main>
</section>
