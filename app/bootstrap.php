<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) require $file;
    }
});
class_alias(App\Core\Security::class, 'Security');

App\Core\Env::load(dirname(__DIR__) . '/.env');
date_default_timezone_set(App\Core\Env::get('APP_TIMEZONE', 'UTC'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(App\Core\Env::get('SESSION_NAME', 'archon_session'));
    session_set_cookie_params([
        'httponly' => true, 'secure' => App\Core\Env::get('SESSION_SECURE', 'false') === 'true',
        'samesite' => 'Lax', 'path' => '/',
    ]);
    session_start();
}
if (App\Core\Env::get('MARKETPLACE_ENABLED', 'false') === 'true') {
    App\Services\RememberService::restore();
}
