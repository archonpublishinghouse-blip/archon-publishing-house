<?php
namespace App\Core;

final class Security {
    public static function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    public static function csrf(): string { return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32)); }
    public static function verifyCsrf(): bool {
        $sessionToken = $_SESSION['_csrf'] ?? null;
        $submittedToken = $_POST['_token'] ?? null;

        return is_string($sessionToken)
            && $sessionToken !== ''
            && is_string($submittedToken)
            && $submittedToken !== ''
            && hash_equals($sessionToken, $submittedToken);
    }
    public static function flash(string $key, ?string $value = null): ?string {
        if ($value !== null) { $_SESSION['_flash'][$key] = $value; return null; }
        $value = $_SESSION['_flash'][$key] ?? null; unset($_SESSION['_flash'][$key]); return $value;
    }
    public static function rateLimit(string $action, int $limit = 5, int $seconds = 900): bool {
        $key = '_rate_' . $action . '_' . hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? 'cli'));
        $hits = $_SESSION[$key] ?? []; $now = time();
        $hits = array_values(array_filter($hits, fn($v) => $v > $now - $seconds));
        if (count($hits) >= $limit) { $_SESSION[$key] = $hits; return false; }
        $hits[] = $now; $_SESSION[$key] = $hits; return true;
    }
    public static function redirect(string $path): never { header('Location: ' . $path, true, 302); exit; }
}
