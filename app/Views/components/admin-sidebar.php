<?php
use App\Core\Security;

$activeAdmin = $activeAdmin ?? '';
$adminLinks = [
    ['href' => '/admin', 'label' => 'Dashboard', 'key' => 'dashboard'],
    ['href' => '/admin/leads', 'label' => 'Lead CRM', 'key' => 'leads'],
    ['href' => '/admin/services', 'label' => 'Services', 'key' => 'services'],
    ['href' => '/admin/authors', 'label' => 'Authors', 'key' => 'authors'],
    ['href' => '/admin/posts', 'label' => 'Journal', 'key' => 'posts'],
    ['href' => '/admin/reviews', 'label' => 'Reviews', 'key' => 'reviews'],
    ['href' => '/admin/settings', 'label' => 'Settings', 'key' => 'settings'],
];
?>
<aside class="admin-sidebar">
    <a href="/admin" class="brand">ARCHON <small>ADMIN</small></a>
    <?php foreach ($adminLinks as $link): ?>
        <a href="<?=$link['href']?>" class="<?=$activeAdmin === $link['key'] ? 'active' : ''?>"><?=Security::e($link['label'])?></a>
    <?php endforeach; ?>
    <a href="/" class="admin-site-link">View website</a>
</aside>
