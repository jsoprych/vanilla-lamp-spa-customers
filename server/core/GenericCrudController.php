<?php
// server/core/GenericCrudController.php
declare(strict_types=1);

require_once __DIR__ . '/Database.php'; // Explicitly include Database.php

abstract class GenericCrudController {
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $allowedFields = [];
    protected array $booleanFields = [];

    public function __construct() {
        error_log("GenericCrudController: Initializing constructor");
        try {
            $this->db = Database::getInstance();
            error_log("GenericCrudController: Database connection established successfully");
        } catch (Exception $e) {
            error_log("GenericCrudController: Database connection failed - " . $e->getMessage());
            throw $e;
        }
        $this->initialize();
        $this->setHeaders();
        error_log("GenericCrudController: Constructor completed for table: {$this->table}");
    }

    abstract protected function initialize(): void;

    protected function setHeaders(): void {
        error_log("GenericCrudController: Setting headers");
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type');
    }

    public function handleRequest(): void {
        error_log("GenericCrudController: Handling request, method: {$_SERVER['REQUEST_METHOD']}");
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $input = $this->getInput($method);
            error_log("GenericCrudController: Input received - " . json_encode($input));

            $result = match($method) {
                'GET'    => $this->handleGet($input),
                'POST'   => $this->handlePost($input),
                'PUT'    => $this->handlePut($input),
                'DELETE' => $this->handleDelete($input),
                default   => throw new RuntimeException('Method not allowed', 405)
            };

            error_log("GenericCrudController: Request processed successfully, result: " . json_encode($result));
            echo json_encode($result, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            error_log("GenericCrudController: Exception caught - Code: {$e->getCode()}, Message: {$e->getMessage()}, Trace: " . $e->getTraceAsString());
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR);
        }
    }

    protected function getInput(string $method): array {
        error_log("GenericCrudController: Getting input for method: $method");
        if ($method === 'GET') {
            error_log("GenericCrudController: GET input - " . json_encode($_GET));
            return $_GET;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        error_log("GenericCrudController: POST/PUT input - " . json_encode($input));
        return $input;
    }

    protected function handleGet(array $input): array {
        error_log("GenericCrudController: Handling GET request, input: " . json_encode($input));
        if (isset($input[$this->primaryKey])) {
            error_log("GenericCrudController: Fetching single record by {$this->primaryKey}: " . $input[$this->primaryKey]);
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
            $stmt->execute([$input[$this->primaryKey]]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                error_log("GenericCrudController: Record not found for {$this->primaryKey}: " . $input[$this->primaryKey]);
                throw new RuntimeException('Record not found', 404);
            }
            
            error_log("GenericCrudController: Single record fetched: " . json_encode($result));
            return $this->processRecord($result);
        }

        error_log("GenericCrudController: Fetching all records, search term: " . ($input['search'] ?? 'none'));
        $query = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($input['search'])) {
            $query .= " WHERE " . $this->getSearchCondition();
            $params = $this->getSearchParams($input['search']);
            error_log("GenericCrudController: Search query: $query, params: " . json_encode($params));
        }
        
        $query .= $this->getDefaultOrder();
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        $results = array_map([$this, 'processRecord'], $stmt->fetchAll(PDO::FETCH_ASSOC));
        error_log("GenericCrudController: Fetched records count: " . count($results));
        return $results;
    }

    protected function getSearchCondition(): string {
        error_log("GenericCrudController: Using default search condition (1=0)");
        return "1=0"; // Default no search - override in child classes
    }

    protected function getSearchParams(string $term): array {
        error_log("GenericCrudController: Using default search params (empty)");
        return [];
    }

    protected function getDefaultOrder(): string {
        error_log("GenericCrudController: Using default order (empty)");
        return "";
    }

    protected function processRecord(array $record): array {
        error_log("GenericCrudController: Processing record: " . json_encode($record));
        foreach ($this->booleanFields as $field) {
            if (array_key_exists($field, $record)) {
                $record[$field] = (bool)$record[$field];
            }
        }
        return $record;
    }

    protected function handlePost(array $input): array {
        error_log("GenericCrudController: Handling POST request, input: " . json_encode($input));
        $data = $this->filterInput($input);
        $this->validateRequiredFields($data);
        
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        error_log("GenericCrudController: POST query: $query, data: " . json_encode(array_values($data)));
        $stmt = $this->db->prepare($query);
        $stmt->execute(array_values($data));
        
        $newId = (int)$this->db->lastInsertId();
        AuditLogger::log($this->table, $newId, 'CREATE', null, $data);
        
        error_log("GenericCrudController: POST successful, new ID: $newId");
        return ['id' => $newId, 'success' => true];
    }

    protected function handlePut(array $input): array {
        error_log("GenericCrudController: Handling PUT request, input: " . json_encode($input));
        if (!isset($input[$this->primaryKey])) {
            error_log("GenericCrudController: Missing {$this->primaryKey} in PUT request");
            throw new RuntimeException("Missing {$this->primaryKey}", 400);
        }

        $data = $this->filterInput($input);
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        
        $query = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = ?";
        error_log("GenericCrudController: PUT query: $query, data: " . json_encode([...array_values($data), $input[$this->primaryKey]]));
        $stmt = $this->db->prepare($query);
        $stmt->execute([...array_values($data), $input[$this->primaryKey]]);
        
        AuditLogger::log($this->table, $input[$this->primaryKey], 'UPDATE', null, $data);
        
        error_log("GenericCrudController: PUT affected rows: " . $stmt->rowCount());
        return ['success' => $stmt->rowCount() > 0];
    }

    protected function handleDelete(array $input): array {
        error_log("GenericCrudController: Handling DELETE request, input: " . json_encode($input));
        if (!isset($input[$this->primaryKey])) {
            error_log("GenericCrudController: Missing {$this->primaryKey} in DELETE request");
            throw new RuntimeException("Missing {$this->primaryKey}", 400);
        }

        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        error_log("GenericCrudController: DELETE query: $query, ID: " . $input[$this->primaryKey]);
        $stmt = $this->db->prepare($query);
        $stmt->execute([$input[$this->primaryKey]]);
        
        AuditLogger::log($this->table, $input[$this->primaryKey], 'DELETE', null, null);
        
        error_log("GenericCrudController: DELETE affected rows: " . $stmt->rowCount());
        return ['success' => $stmt->rowCount() > 0];
    }

    protected function filterInput(array $input): array {
        error_log("GenericCrudController: Filtering input: " . json_encode($input));
        return array_intersect_key($input, array_flip($this->allowedFields));
    }

    protected function validateRequiredFields(array $data): void {
        error_log("GenericCrudController: Validating required fields: " . json_encode($data));
        // To be implemented by child classes if needed
    }
}