<?php
namespace App\Core;

final class View {
    public static function render(string $view, array $data = [], int $status = 200): never {
        http_response_code($status);
        $settings=[];
        try { foreach (Database::pdo()->query('SELECT setting_key,setting_value FROM settings')->fetchAll() as $row) $settings[$row['setting_key']]=$row['setting_value']; } catch (\Throwable) {}
        $layout=$data['__layout'] ?? (str_starts_with($view, 'admin/') ? 'admin' : 'main');
        unset($data['__layout']);
        extract(array_merge(['settings'=>$settings],$data), EXTR_SKIP);
        $contentView = dirname(__DIR__) . '/Views/' . $view . '.php';
        if (!is_file($contentView)) throw new \RuntimeException("View not found: $view");
        $layoutView=dirname(__DIR__) . '/Views/layouts/' . $layout . '.php';
        if (!is_file($layoutView)) throw new \RuntimeException("Layout not found: $layout");
        require $layoutView; exit;
    }
}
