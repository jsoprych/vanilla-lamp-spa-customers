<?php
declare(strict_types=1);

final class Database {
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            // Use __DIR__ relative to the runtime directory (dist/)
            $dbPath = __DIR__ . '/../database/customer.db';
            if (!file_exists($dbPath)) {
                throw new RuntimeException("Database file not found at: $dbPath");
            }
            self::$instance = new PDO("sqlite:$dbPath", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        }
        return self::$instance;
    }
}