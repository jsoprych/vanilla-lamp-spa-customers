<?php
require_once __DIR__ . '/../core/GenericCrudController.php';

class CustomerController extends GenericCrudController {
    protected function initialize(): void {
        $this->table = 'customers';
        $this->primaryKey = 'customer_id';
        $this->allowedFields = [
            'customer_id', 'customer_code', 'first_name', 'last_name',
            'company_name', 'customer_type', 'tax_id', 'status',
            'notes', 'created_at', 'updated_at'
        ];
        $this->booleanFields = [];
        $this->defaultOrder = ['company_name ASC', 'last_name ASC', 'first_name ASC'];
    }

    protected function getSearchConditions(array $input): array {
        $conditions = [];
        
        if (!empty($input['search'])) {
            $conditions[] = "(first_name LIKE :search OR last_name LIKE :search OR 
                             company_name LIKE :search OR customer_code LIKE :search)";
        }
        
        if (!empty($input['status'])) {
            $conditions[] = "status = :status";
        }
        
        if (!empty($input['customer_type'])) {
            $conditions[] = "customer_type = :customer_type";
        }
        
        return $conditions;
    }

    protected function getSearchParams(array $input): array {
        $params = [];
        
        if (!empty($input['search'])) {
            $params[':search'] = '%' . $input['search'] . '%';
        }
        
        if (!empty($input['status'])) {
            $params[':status'] = $input['status'];
        }
        
        if (!empty($input['customer_type'])) {
            $params[':customer_type'] = $input['customer_type'];
        }
        
        return $params;
    }

    protected function validateRequiredFields(array $data): void {
        $required = ['customer_type', 'status'];
        
        if ($data['customer_type'] === 'individual') {
            $required = array_merge($required, ['first_name', 'last_name']);
        } elseif ($data['customer_type'] === 'business') {
            $required[] = 'company_name';
        }
        
        $missing = array_diff($required, array_keys($data));
        if ($missing) {
            throw new RuntimeException('Missing required fields: ' . implode(', ', $missing), 400);
        }
    }

    protected function processRecord(array $record): array {
        $record['display_name'] = !empty($record['company_name']) 
            ? $record['company_name'] 
            : trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''));
            
        return parent::processRecord($record);
    }
}

$controller = new CustomerController();
$controller->handleRequest();