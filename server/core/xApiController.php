<?php
declare(strict_types=1);

abstract class ApiController {
    protected PDO $pdo;
    protected string $requestMethod;
    protected array $inputData;
    
    public function __construct() {
        $this->pdo = Database::getInstance();
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->inputData = $this->parseInput();
        $this->setHeaders();
    }
    
    protected function parseInput(): array {
        if ($this->requestMethod === 'GET') {
            return $_GET;
        }
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    protected function setHeaders(): void {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type');
    }
    
    protected function sendResponse(mixed $data, int $statusCode = 200): never {
        http_response_code($statusCode);
        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit;
    }
    
    protected function validateRequiredFields(array $required): void {
        $missing = array_diff($required, array_keys($this->inputData));
        if ($missing) {
            $this->sendError('Missing fields: ' . implode(', ', $missing), 400);
        }
    }
    
    protected function sendError(string $message, int $statusCode = 400): never {
        $this->sendResponse(['error' => $message], $statusCode);
    }
    
    abstract public function handleRequest(): void;
}