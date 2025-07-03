<?php
// server/api/CustomerController.php
declare(strict_types=1);

require_once __DIR__ . '/../core/GenericCrudController.php';

class CustomerController extends GenericCrudController {
    protected function initialize(): void {
        error_log("CustomerController: Initializing with table: customers");
        $this->table = 'customers';
        $this->primaryKey = 'customer_id';
        $this->allowedFields = [
            'customer_code', 'first_name', 'last_name',
            'company_name', 'customer_type', 'tax_id',
            'status', 'notes'
        ];
    }

    protected function getSearchCondition(): string {
        error_log("CustomerController: Setting search condition");
        return "customer_code LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR company_name LIKE ?";
    }

    protected function getSearchParams(string $term): array {
        error_log("CustomerController: Generating search params for term: $term");
        $searchParam = "%$term%";
        return array_fill(0, 4, $searchParam);
    }

    protected function getDefaultOrder(): string {
        error_log("CustomerController: Setting default order");
        return " ORDER BY last_name, first_name";
    }

    protected function validateRequiredFields(array $data): void {
        error_log("CustomerController: Validating required fields: " . json_encode($data));
        if (empty($data['customer_code'])) {
            error_log("CustomerController: Validation failed - customer_code is required");
            throw new RuntimeException('customer_code is required', 400);
        }
        
        if (isset($data['customer_type']) && !in_array($data['customer_type'], ['individual', 'business'])) {
            error_log("CustomerController: Validation failed - Invalid customer_type: " . ($data['customer_type'] ?? 'null'));
            throw new RuntimeException('Invalid customer_type', 400);
        }
    }
}

// Handle the request
error_log("CustomerController: Starting request handling");
(new CustomerController())->handleRequest();