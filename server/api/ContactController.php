<?php
// server/api/ContactController.php
declare(strict_types=1);

require_once __DIR__ . '/../core/GenericCrudController.php';

class ContactController extends GenericCrudController {
    protected function initialize(): void {
        $this->table = 'customer_contacts';
        $this->primaryKey = 'contact_id';
        $this->allowedFields = [
            'customer_id', 'contact_type', 'contact_value',
            'is_primary', 'notes'
        ];
        $this->booleanFields = ['is_primary'];
    }

    protected function handleGet(array $input): array {
        if (empty($input['customer_id'])) {
            throw new RuntimeException('customer_id parameter is required', 400);
        }

        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE customer_id = ? 
            ORDER BY is_primary DESC, contact_type
        ");
        $stmt->execute([$input['customer_id']]);
        
        return array_map([$this, 'processRecord'], $stmt->fetchAll(PDO::FETCH_ASSOC));
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

// Handle the request
(new ContactController())->handleRequest();