<?php
namespace App\Core;
use PDO;
use PDOException;

final class Database {
    private static ?PDO $pdo = null;
    private static ?PDOException $connectionError = null;
    public static function pdo(): PDO {
        if (self::$pdo) return self::$pdo;
        if (self::$connectionError) throw self::$connectionError;
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', Env::get('DB_HOST','127.0.0.1'), Env::get('DB_PORT','3306'), Env::get('DB_DATABASE','archon_publishing'));
        try { self::$pdo = new PDO($dsn, Env::get('DB_USERNAME','root'), Env::get('DB_PASSWORD',''), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false, PDO::ATTR_TIMEOUT=>2]); } catch (PDOException $exception) { self::$connectionError=$exception; throw $exception; }
        return self::$pdo;
    }
    public static function available(): bool { try { self::pdo(); return true; } catch (PDOException) { return false; } }
}
