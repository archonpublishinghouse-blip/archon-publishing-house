<?php
use App\Core\Security;

$activeAdmin = 'profile';
$isManager = in_array($record['role'] ?? '', ['admin', 'super_admin'], true);
?>
<section class="admin-shell">
    <?php require dirname(__DIR__).'/components/admin-sidebar.php'; ?>
    <main class="admin-main">
        <p class="eyebrow">CRM USER SETTINGS</p>
        <h1>Account settings</h1>
        <div class="crm-detail-grid">
            <?php if ($isManager): ?>
                <section class="panel">
                    <div class="crm-heading">
                        <div>
                            <p class="eyebrow">ADMIN PROFILE</p>
                            <h2>Name and contact details</h2>
                        </div>
                    </div>
                    <form method="post" action="/admin/profile" class="admin-form">
                        <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                        <label>Full name<input name="name" value="<?=Security::e($record['name'])?>" required></label>
                        <label>Email address<input name="email" type="email" value="<?=Security::e($record['email'])?>" required></label>
                        <label>Phone<input name="phone" value="<?=Security::e($record['phone'] ?? '')?>"></label>
                        <label>Job title<input name="job_title" value="<?=Security::e($record['job_title'] ?? '')?>"></label>
                        <button class="button">Save profile</button>
                    </form>
                </section>
            <?php else: ?>
                <section class="panel">
                    <div class="crm-heading">
                        <div>
                            <p class="eyebrow">TEAM MEMBER PROFILE</p>
                            <h2><?=Security::e($record['name'])?></h2>
                        </div>
                    </div>
                    <dl class="crm-fields">
                        <dt>Email</dt><dd><?=Security::e($record['email'])?></dd>
                        <dt>Role</dt><dd>Assigned leads only</dd>
                    </dl>
                    <p>Team member names, emails, roles, and access levels are managed by a CRM admin. You can update your password here.</p>
                </section>
            <?php endif; ?>

            <aside class="panel">
                <div class="crm-heading">
                    <div>
                        <p class="eyebrow">SECURITY</p>
                        <h2>Change password</h2>
                    </div>
                </div>
                <form method="post" action="/admin/profile" class="crm-password-form">
                    <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                    <label>Current password<input name="current_password" type="password" autocomplete="current-password" required></label>
                    <label>New password<input name="new_password" type="password" minlength="10" autocomplete="new-password" required></label>
                    <label>Confirm new password<input name="confirm_password" type="password" minlength="10" autocomplete="new-password" required></label>
                    <button class="button outline">Update password</button>
                    <p class="small-note">Use at least 10 characters with both letters and numbers.</p>
                </form>
            </aside>
        </div>
    </main>
</section>
