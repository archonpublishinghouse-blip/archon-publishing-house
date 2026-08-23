<?php
use App\Core\Security;

$activeAdmin = $activeAdmin ?? '';
$currentRole = $_SESSION['admin']['role'] ?? 'employee';
$canManageAll = in_array($currentRole, ['admin', 'super_admin'], true);
$adminLinks = [
    ['href' => '/admin', 'label' => 'Dashboard', 'key' => 'dashboard'],
    ['href' => '/admin/leads', 'label' => 'Lead CRM', 'key' => 'leads'],
    ['href' => '/admin/profile', 'label' => 'Account Settings', 'key' => 'profile'],
];
if ($canManageAll) {
    $adminLinks = array_merge($adminLinks, [
        ['href' => '/admin/leads/create', 'label' => 'Create Lead', 'key' => 'lead-create'],
        ['href' => '/admin/book-contact', 'label' => 'Book Contact', 'key' => 'book-contact'],
        ['href' => '/admin/employees', 'label' => 'Team Accounts', 'key' => 'employees'],
        ['href' => '/admin/services', 'label' => 'Services', 'key' => 'services'],
        ['href' => '/admin/authors', 'label' => 'Authors', 'key' => 'authors'],
        ['href' => '/admin/posts', 'label' => 'Journal', 'key' => 'posts'],
        ['href' => '/admin/reviews', 'label' => 'Reviews', 'key' => 'reviews'],
    ]);
}
?>
<aside class="admin-sidebar">
    <a href="/admin" class="brand">ARCHON <small>CRM</small></a>
    <?php foreach ($adminLinks as $link): ?>
        <a href="<?=$link['href']?>" class="<?=$activeAdmin === $link['key'] ? 'active' : ''?>"><?=Security::e($link['label'])?></a>
    <?php endforeach; ?>
    <a href="/" class="admin-site-link">View website</a>
</aside>
