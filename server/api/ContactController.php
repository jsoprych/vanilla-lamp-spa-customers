<?php
// server/api/ContactController.php
declare(strict_types=1);

require_once __DIR__ . '/../core/GenericCrudController.php';

class ContactController extends GenericCrudController {
    protected function initialize(): void {
        $this->table = 'customer_contacts';
        $this->primaryKey = 'contact_id';
        $this->allowedFields = [
            'contact_id', 'customer_id', 'contact_type', 'contact_value',
            'is_primary', 'notes', 'created_at'
        ];
        $this->booleanFields = ['is_primary'];
    }

    protected function handleGet(array $input): array {
        if (empty($input['customer_id'])) {
            throw new RuntimeException('customer_id parameter is required', 400);
        }

        $conditions = ["customer_id = :customer_id"];
        $params = [':customer_id' => $input['customer_id']];

        $query = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $conditions);
        $query .= " ORDER BY is_primary DESC, contact_type"; // Removed created_at if not a column

        if ($this->config['debug_mode']) {
            error_log("[DEBUG] Contact query: {$query}");
            error_log("[DEBUG] Contact params: " . json_encode($params));
        }

        try {
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleDatabaseError($e, $query, $params);
            throw new RuntimeException('Database query failed', 500);
        }
    }

    protected function validateRequiredFields(array $data): void {
        $required = ['customer_id', 'contact_type', 'contact_value'];
        $missing = array_diff($required, array_keys($data));
        
        if (!empty($missing)) {
            throw new RuntimeException('Missing required fields: ' . implode(', ', $missing), 400);
        }
        
        if (!in_array($data['contact_type'], ['email', 'phone', 'mobile', 'fax', 'other'])) {
            throw new RuntimeException('Invalid contact_type', 400);
        }
    }
}

(new ContactController())->handleRequest();