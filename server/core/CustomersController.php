<?php
declare(strict_types=1);

class CustomersController extends GenericCrudController {
    protected function initialize(): void {
        $this->table = 'customers';
        $this->primaryKey = 'customer_id';
        $this->allowedFields = [
            'first_name', 'last_name', 'email',
            'phone', 'status'
        ];
    }
}

(new CustomersController())->handleRequest();