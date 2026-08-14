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
    protected function requireAdmin(): array { if (!$this->admin()) Security::redirect('/login'); return $this->admin(); }
    protected function one(string $sql, array $params = []): ?array { $s=$this->db()->prepare($sql);$s->execute($params);return $s->fetch()?:null; }
    protected function all(string $sql, array $params=[]): array { $s=$this->db()->prepare($sql);$s->execute($params);return $s->fetchAll(); }
}
