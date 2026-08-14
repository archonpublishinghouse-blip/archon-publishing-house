<?php
declare(strict_types=1);

// When using PHP's local development server, let existing assets be served
// directly instead of routing CSS, JavaScript, images, and uploads through MVC.
if (PHP_SAPI === 'cli-server') {
    $asset = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($asset) && parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) !== '/sitemap.xml') return false;
}

require dirname(__DIR__) . '/app/bootstrap.php';

try {
    (new App\Core\Application())->run();
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(500);
    $debug = App\Core\Env::get('APP_DEBUG', 'false') === 'true';
    require dirname(__DIR__) . '/app/Views/errors/500.php';
}
