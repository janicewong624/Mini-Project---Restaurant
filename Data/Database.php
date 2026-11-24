<?php
class Database {

    public static function connect(): PDO {
        $config = require __DIR__ . '/../config.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }

    public static function generateID(PDO $pdo, string $table, string $column, string $prefix, int $digits): string {
        $start = strlen($prefix) + 1;
        $sql = "SELECT MAX(CAST(SUBSTRING($column, $start) AS UNSIGNED)) AS maxNum FROM $table";
        $row = $pdo->query($sql)->fetch();
        $next = ((int)($row['maxNum'] ?? 0)) + 1;
        return $prefix . str_pad((string)$next, $digits, '0', STR_PAD_LEFT);
    }
}