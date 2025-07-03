<?php
// server/api/AddressController.php
declare(strict_types=1);

require_once __DIR__ . '/../core/GenericCrudController.php';

class AddressController extends GenericCrudController {
    protected function initialize(): void {
        $this->table = 'customer_addresses';
        $this->primaryKey = 'address_id';
        $this->allowedFields = [
            'customer_id', 'address_type', 'line1', 'line2',
            'city', 'state_province', 'postal_code', 'country',
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
            ORDER BY is_primary DESC, address_type
        ");
        $stmt->execute([$input['customer_id']]);
        
        return array_map([$this, 'processRecord'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    protected function validateRequiredFields(array $data): void {
        $required = ['customer_id', 'address_type', 'line1', 'city'];
        $missing = array_diff($required, array_keys($data));
        
        if (!empty($missing)) {
            throw new RuntimeException('Missing required fields: ' . implode(', ', $missing), 400);
        }
        
        if (!in_array($data['address_type'], ['billing', 'shipping', 'home', 'work', 'other'])) {
            throw new RuntimeException('Invalid address_type', 400);
        }
    }
}

// Handle the request
(new AddressController())->handleRequest();