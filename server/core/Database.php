<?php
class Database {
    private static ?PDO $instance = null;
    private static string $dsn = '';

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dbPath = __DIR__ . '/../database/customer.db';
            self::$dsn = "sqlite:" . $dbPath; // Store the DSN
            self::$instance = new PDO(self::$dsn);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$instance;
    }

    public static function getDsn(): string {
        return self::$dsn; // Provide the DSN for logging
    }
}