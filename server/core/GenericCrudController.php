<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/AuditLogger.php';

abstract class GenericCrudController {
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $allowedFields = [];
    protected array $booleanFields = [];
    protected array $config = [];
    protected array $defaultOrder = [];
    protected array $input = [];

    public function __construct() {
        try {
            $this->initialize();
            $this->config = $this->loadConfig();
            // Force debug mode for troubleshooting
            $this->config['debug_mode'] = true;
            $this->initDatabase();
            $this->setHeaders();
            $this->configureErrorHandling();
            
            if ($this->config['debug_mode']) {
                $this->logInitializationDetails();
            }
        } catch (Exception $e) {
            $this->handleCriticalError($e);
        }
    }

    abstract protected function initialize(): void;

    public function handleRequest(): void {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $this->input = $this->getInput($method);
            
            if ($this->config['debug_mode']) {
                error_log("[DEBUG] {$method} request for {$this->table}");
                error_log("[DEBUG] Input data: " . json_encode($this->input));
            }
            
            $result = match($method) {
                'GET'    => $this->handleGet($this->input),
                'POST'   => $this->handlePost($this->input),
                'PUT'    => $this->handlePut($this->input),
                'DELETE' => $this->handleDelete($this->input),
                'OPTIONS' => $this->handleOptions(),
                default  => throw new RuntimeException('Method not allowed', 405)
            };
            
            $this->sendResponse(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    protected function handleGet(array $input): array {
        $columns = !empty($this->allowedFields) 
            ? implode(', ', $this->allowedFields)
            : '*';
        
        $query = "SELECT {$columns} FROM {$this->table}";
        $params = [];
        $conditions = [];
        
        if (!empty($input[$this->primaryKey])) {
            $conditions[] = "{$this->primaryKey} = :primary_key";
            $params[':primary_key'] = $input[$this->primaryKey];
        }
        
        $customConditions = $this->getSearchConditions($input);
        $customParams = $this->getSearchParams($input);
        
        if (!empty($customConditions)) {
            $conditions = array_merge($conditions, $customConditions);
            $params = array_merge($params, $customParams);
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $query .= $this->getDefaultOrder();
        
        if ($this->config['debug_mode']) {
            error_log("[DEBUG] Final query: {$query}");
            error_log("[DEBUG] Query params: " . json_encode($params));
        }
        
        try {
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map([$this, 'processRecord'], $results);
        } catch (PDOException $e) {
            $this->handleDatabaseError($e, $query, $params);
            throw new RuntimeException('Database query failed', 500);
        }
    }

    protected function getSearchConditions(array $input): array {
        return [];
    }

    protected function getSearchParams(array $input): array {
        return [];
    }

    protected function handlePost(array $input): array {
        $data = $this->filterInput($input);
        $this->validateRequiredFields($data);
        
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $query = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($query);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        $newId = (int)$this->db->lastInsertId();
        
        AuditLogger::log($this->table, $newId, 'CREATE', null, $data);
        return ['id' => $newId];
    }

    protected function handlePut(array $input): array {
        if (empty($input[$this->primaryKey])) {
            throw new RuntimeException("Missing {$this->primaryKey}", 400);
        }
        
        $data = $this->filterInput($input);
        $set = [];
        
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $set) . 
                 " WHERE {$this->primaryKey} = :primary_key";
        $stmt = $this->db->prepare($query);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':primary_key', $input[$this->primaryKey], PDO::PARAM_INT);
        
        $stmt->execute();
        $success = $stmt->rowCount() > 0;
        
        if ($success) {
            AuditLogger::log($this->table, $input[$this->primaryKey], 'UPDATE', null, $data);
        }
        
        return ['success' => $success];
    }

    protected function handleDelete(array $input): array {
        if (empty($input[$this->primaryKey])) {
            throw new RuntimeException("Missing {$this->primaryKey}", 400);
        }
        
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :primary_key";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':primary_key', $input[$this->primaryKey], PDO::PARAM_INT);
        $stmt->execute();
        $success = $stmt->rowCount() > 0;
        
        if ($success) {
            AuditLogger::log($this->table, $input[$this->primaryKey], 'DELETE', null, null);
        }
        
        return ['success' => $success];
    }

    protected function loadConfig(): array {
        $configPath = __DIR__ . '/config.php';
        if (!file_exists($configPath)) {
            error_log("[WARNING] Config file missing at: {$configPath}");
            return $this->getDefaultConfig();
        }
        
        $config = require $configPath;
        if (!is_array($config)) {
            error_log("[CRITICAL] Invalid config format");
            return $this->getDefaultConfig();
        }
        
        return array_merge($this->getDefaultConfig(), $config);
    }

    protected function getDefaultConfig(): array {
        return [
            'debug_mode' => true, // Forced to true for troubleshooting
            'audit_logging' => true,
            'verbose_read_logging' => false,
            'log_read_to_database' => true,
            'sensitive_fields' => ['password', 'token'],
            'log_path' => __DIR__ . '/../logs/', // Ensure this path exists and is writable
            'error_log_file' => __DIR__ . '/../logs/php_errors.log'
        ];
    }

    protected function initDatabase(): void {
        if (empty($this->table)) {
            throw new RuntimeException('Table name not initialized', 500);
        }
        
        try {
            $this->db = Database::getInstance();
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            if ($this->config['debug_mode']) {
                $dsn = Database::getDsn();
                error_log("[DEBUG] Database connected to: " . parse_url($dsn, PHP_URL_PATH));
            }
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 500);
        }
    }

    protected function setHeaders(): void {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 86400');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    protected function getInput(string $method): array {
        if ($method === 'GET') {
            return $_GET;
        }
        
        $jsonInput = json_decode(file_get_contents('php://input'), true);
        return is_array($jsonInput) ? $jsonInput : [];
    }

    protected function processRecord(array $record): array {
        foreach ($this->booleanFields as $field) {
            if (isset($record[$field])) {
                $record[$field] = (bool)$record[$field];
            }
        }
        
        return $record;
    }

    protected function filterInput(array $input): array {
        return array_intersect_key($input, array_flip($this->allowedFields));
    }

    protected function validateRequiredFields(array $data): void {
    }

    protected function getDefaultOrder(): string {
        if (empty($this->defaultOrder)) {
            return " ORDER BY {$this->primaryKey} ASC";
        }
        
        $sanitizedOrders = array_map(function($order) {
            if (!preg_match('/^[a-zA-Z0-9_]+(\s+(ASC|DESC))?$/i', $order)) {
                return $this->primaryKey . ' ASC';
            }
            return $order;
        }, $this->defaultOrder);
        
        return " ORDER BY " . implode(', ', $sanitizedOrders);
    }

    protected function handleOptions(): array {
        return ['status' => 'ok'];
    }

    protected function sendResponse(array $response): void {
        echo json_encode($response, JSON_THROW_ON_ERROR);
    }

    protected function handleError(Exception $e): void {
        $code = $e->getCode() ?: 500;
        http_response_code($code);
        
        $response = ['success' => false, 'error' => $e->getMessage()];
        if ($this->config['debug_mode']) {
            $response['trace'] = $e->getTraceAsString();
            $response['file'] = $e->getFile();
            $response['line'] = $e->getLine();
        }
        
        $this->sendResponse($response);
        error_log("[ERROR] {$code}: {$e->getMessage()}");
    }

    private function configureErrorHandling(): void {
        ini_set('log_errors', '1');
        ini_set('error_log', $this->config['error_log_file']);
        ini_set('display_errors', $this->config['debug_mode'] ? '1' : '0');
    }

    private function logInitializationDetails(): void {
        error_log("[DEBUG] Controller initialized for table: {$this->table}");
        error_log("[DEBUG] Log path: {$this->config['log_path']}");
        error_log("[DEBUG] Working directory: " . getcwd());
        
        if (!is_dir($this->config['log_path']) || !is_writable($this->config['log_path'])) {
            error_log("[WARN] Log directory not writable: {$this->config['log_path']}");
        }
    }

    private function handleCriticalError(Exception $e): void {
        error_log("[CRITICAL] Construction failed: " . $e->getMessage());
        error_log("[CRITICAL] Stack trace: " . $e->getTraceAsString());
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Internal server error',
            'message' => $this->config['debug_mode'] ? $e->getMessage() : 'Please contact support'
        ]);
        exit;
    }

    protected function handleDatabaseError(PDOException $e, string $query, array $params): void {
        error_log("[DB ERROR] Query failed: " . $e->getMessage());
        error_log("[DB ERROR] Query: {$query}");
        error_log("[DB ERROR] Params: " . json_encode($params));
        error_log("[DB ERROR] Trace: " . $e->getTraceAsString());
    }
}