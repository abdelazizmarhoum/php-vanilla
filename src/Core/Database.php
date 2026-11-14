<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function init(): void
    {
        if (self::$pdo !== null) {
            return;
        }

        $host = (string) config('db.host');
        $port = (string) config('db.port');
        $name = (string) config('db.name');
        $user = (string) config('db.user');
        $pass = (string) config('db.pass');

        $dsn = "pgsql:host={$host};port={$port};dbname={$name}";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Database unavailable', 0, $e);
        }
    }

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            self::init();
        }
        return self::$pdo;
    }

    public static function q(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}