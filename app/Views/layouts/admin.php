<?php
use App\Core\Security;

$siteTitle = $title ?? 'Archon Administration';
$flashSuccess = Security::flash('success');
$flashError = Security::flash('error');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?=Security::e($siteTitle)?></title>
    <link rel="icon" type="image/png" href="/assets/images/brand/archon-logo-transparent.png">
    <link rel="shortcut icon" type="image/png" href="/assets/images/brand/archon-logo-transparent.png">
    <link rel="apple-touch-icon" href="/assets/images/brand/archon-logo-transparent.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css?v=20260821-crm1">
    <style>
        .admin-session-logout{position:fixed;z-index:30;top:1rem;right:1rem;margin:0}
    </style>
</head>
<body class="admin-body">
    <?php if ($flashSuccess || $flashError): ?>
        <div class="toast <?=$flashError ? 'error' : ''?>" role="status"><?=Security::e($flashError ?: $flashSuccess)?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['admin'])): ?>
        <form method="post" action="/admin/logout" class="admin-session-logout">
            <input type="hidden" name="_token" value="<?=Security::csrf()?>">
            <button class="button small" type="submit">Sign out</button>
        </form>
    <?php endif; ?>
    <?php require $contentView; ?>
    <script src="/assets/js/main.js" defer></script>
</body>
</html>
