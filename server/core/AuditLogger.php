<?php
declare(strict_types=1);

final class AuditLogger {
    private static ?PDOStatement $stmt = null;

    public static function log(
        string $table,
        int $recordId, 
        string $action,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        if (self::$stmt === null) {
            $db = Database::getInstance();
            self::$stmt = $db->prepare("
                INSERT INTO audit_logs 
                (table_name, record_id, action, old_values, new_values, user_ip)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
        }

        self::$stmt->execute([
            $table,
            $recordId,
            $action,
            $oldData ? json_encode($oldData, JSON_THROW_ON_ERROR) : null,
            $newData ? json_encode($newData, JSON_THROW_ON_ERROR) : null,
            $_SERVER['REMOTE_ADDR'] ?? 'CLI'
        ]);
    }
}