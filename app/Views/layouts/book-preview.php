<?php
use App\Core\Security;

$siteTitle = $title ?? 'Your eBook Journey | Archon Publishing House';
$description = $metaDescription ?? 'Archon Publishing House helps turn ideas, outlines and manuscripts into professionally written eBooks.';
$robots = $metaRobots ?? 'index,follow';
$baseUrl = rtrim(\App\Core\Env::get('APP_URL', 'http://localhost'), '/');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$canonicalRoute = $canonicalPath ?? $requestPath;
$canonicalRoute = '/' . ltrim($canonicalRoute, '/');
$canonical = $baseUrl . $canonicalRoute;
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width,initial-scale=1">
 <meta name="description" content="<?=Security::e($description)?>">
 <meta name="robots" content="<?=Security::e($robots)?>">
 <link rel="canonical" href="<?=Security::e($canonical)?>">
 <meta property="og:type" content="website">
 <meta property="og:title" content="<?=Security::e($siteTitle)?>">
 <meta property="og:description" content="<?=Security::e($description)?>">
 <meta property="og:url" content="<?=Security::e($canonical)?>">
 <title><?=Security::e($siteTitle)?></title>
 <link rel="icon" type="image/png" href="/assets/images/brand/archon-logo-transparent.png">
 <link rel="shortcut icon" type="image/png" href="/assets/images/brand/archon-logo-transparent.png">
 <link rel="apple-touch-icon" href="/assets/images/brand/archon-logo-transparent.png">
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="/assets/css/main.css?v=20260823-mobile1">
</head>
<body class="preview-body">
 <a class="skip" href="#preview-book">Skip to book</a>
 <div id="preview-book"><?php require $contentView; ?></div>
 <script type="module" src="/assets/js/archon-3d-scene.js?v=20260821-3d1"></script>
</body>
</html>
