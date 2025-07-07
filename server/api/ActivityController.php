<?php
// server/api/ActivityController.php
declare(strict_types=1);

require_once __DIR__ . '/../core/GenericCrudController.php';

class ActivityController extends GenericCrudController {
    protected function initialize(): void {
        $this->table = 'customer_activity';
        $this->primaryKey = 'activity_id';
        $this->allowedFields = [
            'customer_id', 'activity_type', 'activity_details',
            'ip_address', 'user_agent'
        ];
    }

    protected function handleGet(array $input): array {
        if (empty($input['customer_id'])) {
            throw new RuntimeException('customer_id parameter is required', 400);
        }

        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE customer_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$input['customer_id']]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

(new ActivityController())->handleRequest();