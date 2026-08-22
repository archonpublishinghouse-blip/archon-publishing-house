<?php
use App\Core\Security;

$activeAdmin = 'employees';
?>
<section class="admin-shell">
    <?php require dirname(__DIR__).'/components/admin-sidebar.php'; ?>
    <main class="admin-main">
        <p class="eyebrow">CRM CREDENTIALS</p>
        <h1>Team accounts</h1>
        <div class="crm-detail-grid">
            <section class="panel">
                <div class="crm-heading">
                    <div>
                        <p class="eyebrow">CREATE ACCOUNT</p>
                        <h2>Create login credentials</h2>
                    </div>
                </div>
                <form method="post" action="/admin/employees/create" class="admin-form">
                    <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                    <label>Name<input name="name" required></label>
                    <label>Email<input name="email" type="email" required></label>
                    <label>Phone<input name="phone"></label>
                    <label>Job title<input name="job_title" placeholder="Lead coordinator, Writer, Sales..."></label>
                    <label>Role
                        <select name="role">
                            <option value="employee">Team member — assigned leads only</option>
                            <option value="admin">CRM admin — all leads and team settings</option>
                        </select>
                    </label>
                    <label>Temporary password<input name="password" type="password" minlength="10" required></label>
                    <button class="button">Create account</button>
                </form>
            </section>
            <aside class="panel">
                <h2>Credential rules</h2>
                <p>Everyone signs in from the same CRM login page.</p>
                <p><strong>CRM admin credentials</strong> can see every lead, assign leads, export CSVs, and manage team accounts.</p>
                <p><strong>Team member credentials</strong> can only see assigned leads. They can update status and add notes on those leads.</p>
            </aside>
        </div>

        <section class="panel crm-panel">
            <div class="crm-heading">
                <div>
                    <p class="eyebrow">CURRENT TEAM</p>
                    <h2>CRM accounts</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table class="crm-table">
                    <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Last login</th><th>Update</th></tr></thead>
                    <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td>
                                <strong><?=Security::e($employee['name'])?></strong>
                                <small><?=Security::e($employee['email'])?></small>
                                <?php if (!empty($employee['job_title'])): ?><small><?=Security::e($employee['job_title'])?></small><?php endif; ?>
                            </td>
                            <td><span class="status <?=Security::e($employee['role'])?>"><?=Security::e(ucwords(str_replace('_', ' ', $employee['role'])))?></span></td>
                            <td><?=((int)$employee['is_active'] === 1) ? 'Active' : 'Inactive'?></td>
                            <td><?=!empty($employee['last_login_at']) ? Security::e(date('M j, Y g:ia', strtotime($employee['last_login_at']))) : 'Never'?></td>
                            <td>
                                <details>
                                    <summary>Edit</summary>
                                    <form method="post" action="/admin/employees/<?=$employee['id']?>/update" class="crm-employee-form">
                                        <input type="hidden" name="_token" value="<?=Security::csrf()?>">
                                        <label>Name<input name="name" value="<?=Security::e($employee['name'])?>" required></label>
                                        <label>Phone<input name="phone" value="<?=Security::e($employee['phone'] ?? '')?>"></label>
                                        <label>Job title<input name="job_title" value="<?=Security::e($employee['job_title'] ?? '')?>"></label>
                                        <label>Role
                                            <select name="role">
                                                <option value="employee" <?=$employee['role'] === 'employee' ? 'selected' : ''?>>Team member</option>
                                                <option value="admin" <?=$employee['role'] !== 'employee' ? 'selected' : ''?>>CRM admin</option>
                                            </select>
                                        </label>
                                        <label>New password <small>Leave blank to keep existing.</small><input name="password" type="password" minlength="10"></label>
                                        <label class="consent"><input name="is_active" type="checkbox" value="1" <?=((int)$employee['is_active'] === 1) ? 'checked' : ''?>> Active account</label>
                                        <button class="button small">Save user</button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</section>
