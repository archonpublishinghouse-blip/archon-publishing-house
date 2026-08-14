<?php
use App\Core\Security;

$siteTitle = $title ?? 'Your eBook Journey | Archon Publishing House';
$baseUrl = rtrim(\App\Core\Env::get('APP_URL', 'http://localhost'), '/');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/book-preview', PHP_URL_PATH) ?: '/book-preview';
$canonical = $baseUrl . $requestPath;
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width,initial-scale=1">
 <meta name="description" content="Preview your personalized Archon eBook writing journey.">
 <link rel="canonical" href="<?=Security::e($canonical)?>">
 <title><?=Security::e($siteTitle)?></title>
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="preview-body">
 <a class="skip" href="#preview-book">Skip to book</a>
 <div id="preview-book"><?php require $contentView; ?></div>
</body>
</html>
