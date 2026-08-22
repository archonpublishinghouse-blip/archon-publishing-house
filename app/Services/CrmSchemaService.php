<?php
namespace App\Services;

use PDO;

final class CrmSchemaService {
    private static bool $ready = false;

    public static function ensure(PDO $pdo): void {
        if (self::$ready) return;

        self::addColumn($pdo, 'admins', 'role', "ALTER TABLE admins ADD COLUMN role VARCHAR(30) NOT NULL DEFAULT 'admin' AFTER password_hash");
        self::addColumn($pdo, 'admins', 'phone', "ALTER TABLE admins ADD COLUMN phone VARCHAR(80) NULL AFTER email");
        self::addColumn($pdo, 'admins', 'job_title', "ALTER TABLE admins ADD COLUMN job_title VARCHAR(120) NULL AFTER phone");

        foreach (['quote_requests' => 'quote_form', 'contact_messages' => 'contact_form'] as $table => $source) {
            self::addColumn($pdo, $table, 'assigned_admin_id', "ALTER TABLE $table ADD COLUMN assigned_admin_id BIGINT UNSIGNED NULL AFTER status");
            self::addColumn($pdo, $table, 'assigned_at', "ALTER TABLE $table ADD COLUMN assigned_at DATETIME NULL AFTER assigned_admin_id");
            self::addColumn($pdo, $table, 'lead_source', "ALTER TABLE $table ADD COLUMN lead_source VARCHAR(80) NOT NULL DEFAULT '$source' AFTER assigned_at");
            self::addColumn($pdo, $table, 'priority', "ALTER TABLE $table ADD COLUMN priority VARCHAR(20) NOT NULL DEFAULT 'normal' AFTER lead_source");
            self::addIndex($pdo, $table, 'idx_'.$table.'_assigned_admin_id', "ALTER TABLE $table ADD INDEX idx_{$table}_assigned_admin_id (assigned_admin_id)");
            self::addIndex($pdo, $table, 'idx_'.$table.'_status_assigned', "ALTER TABLE $table ADD INDEX idx_{$table}_status_assigned (status, assigned_admin_id)");
        }

        $pdo->exec("UPDATE admins SET role='admin' WHERE role IS NULL OR role=''");
        self::$ready = true;
    }

    private static function addColumn(PDO $pdo, string $table, string $column, string $sql): void {
        if (self::columnExists($pdo, $table, $column)) return;
        $pdo->exec($sql);
    }

    private static function addIndex(PDO $pdo, string $table, string $index, string $sql): void {
        if (self::indexExists($pdo, $table, $index)) return;
        $pdo->exec($sql);
    }

    private static function columnExists(PDO $pdo, string $table, string $column): bool {
        $statement = $pdo->prepare('SELECT COUNT(*) count FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
        $statement->execute([$table, $column]);
        return (int)($statement->fetch()['count'] ?? 0) > 0;
    }

    private static function indexExists(PDO $pdo, string $table, string $index): bool {
        $statement = $pdo->prepare('SELECT COUNT(*) count FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND INDEX_NAME=?');
        $statement->execute([$table, $index]);
        return (int)($statement->fetch()['count'] ?? 0) > 0;
    }
}
