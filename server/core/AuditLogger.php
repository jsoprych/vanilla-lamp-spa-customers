<?php
declare(strict_types=1);

final class AuditLogger {
    public static function log(string $table, int $recordId, string $action, ?array $oldData, ?array $newData): void {
        $config = require __DIR__ . '/config.php';
        $sanitizedOld = self::sanitize($oldData, $config['sensitive_fields']);
        $sanitizedNew = self::sanitize($newData, $config['sensitive_fields']);
        if ($config['audit_logging'] && in_array($action, ['CREATE', 'UPDATE', 'DELETE']) ||
            ($config['log_read_to_database'] && $action === 'READ')) {
            self::writeToDatabase(
                $table,
                $recordId,
                $action,
                $sanitizedOld,
                $sanitizedNew
            );
        }
        if ($action === 'READ' && $config['verbose_read_logging']) {
            $logDir = $config['log_path'] ?? __DIR__ . '/../logs/';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            file_put_contents(
                $logDir . 'reads.log',
                json_encode([
                    'timestamp' => date('c'),
                    'table' => $table,
                    'action' => 'READ',
                    'record_id' => $recordId,
                    'input' => $sanitizedNew
                ]) . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    private static function writeToDatabase(string $table, int $recordId, string $action, ?array $oldData, ?array $newData): void {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO audit_logs 
                (table_name, record_id, action, old_values, new_values, user_ip, created_at)
                VALUES (?, ?, ?, ?, ?, ?, datetime('now'))
            ");
            $stmt->execute([
                $table,
                $recordId,
                $action,
                $oldData ? json_encode($oldData) : null,
                $newData ? json_encode($newData) : null,
                $_SERVER['REMOTE_ADDR'] ?? 'cli'
            ]);
        } catch (PDOException $e) {
            error_log("[ERROR] Audit logging failed: " . $e->getMessage());
            // No re-throw to avoid breaking the request
        }
    }

    private static function sanitize(?array $data, array $sensitiveFields): ?array {
        if ($data === null) return null;
        foreach ($sensitiveFields as $field) {
            unset($data[$field]);
        }
        return $data;
    }
}