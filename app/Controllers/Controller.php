<?php
namespace App\Controllers;
use App\Core\{Database,Security,View};

abstract class Controller {
    protected function db(): \PDO { return Database::pdo(); }
    protected function render(string $view, array $data = [], int $status = 200): never { View::render($view, $data, $status); }
    protected function requirePost(): void { if (!Security::verifyCsrf()) { http_response_code(419); exit('Your form expired. Please go back and try again.'); } }
    protected function customer(): ?array { return $_SESSION['customer'] ?? null; }
    protected function requireCustomer(): array { if (!$this->customer()) Security::redirect('/login'); return $this->customer(); }
    protected function admin(): ?array { return $_SESSION['admin'] ?? null; }
    protected function requireAdmin(): array { if (!$this->admin()) Security::redirect('/admin/login'); return $this->admin(); }
    protected function one(string $sql, array $params = []): ?array { $s=$this->db()->prepare($sql);$s->execute($params);return $s->fetch()?:null; }
    protected function all(string $sql, array $params=[]): array { $s=$this->db()->prepare($sql);$s->execute($params);return $s->fetchAll(); }
    protected function settings(array $defaults): array {
        $settings = $defaults;
        if (!$defaults) return $settings;
        $keys = array_keys($defaults);
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        try {
            foreach ($this->all("SELECT setting_key,setting_value FROM settings WHERE setting_key IN ($placeholders)", $keys) as $row) {
                $key = (string)($row['setting_key'] ?? '');
                if (array_key_exists($key, $settings) && trim((string)($row['setting_value'] ?? '')) !== '') {
                    $settings[$key] = (string)$row['setting_value'];
                }
            }
        } catch (\Throwable) {
            return $settings;
        }
        return $settings;
    }
    protected function saveSetting(string $key, string $value): void {
        $this->db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')->execute([$key, $value]);
    }
}
