<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$dbPath = __DIR__ . '/../../database/customer.db';

try {
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $searchTerm = $_GET['search'] ?? '';
    $customerId = $_GET['id'] ?? null;

    if ($customerId) {
        // Get single customer details
        $stmt = $db->prepare("SELECT * FROM customers WHERE customer_id = :id");
        $stmt->bindParam(':id', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer) {
            echo json_encode($customer);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Customer not found']);
        }
    } else {
        // Get list of customers
        if ($searchTerm) {
            $stmt = $db->prepare("
                SELECT * FROM customers 
                WHERE customer_code LIKE :search 
                OR first_name LIKE :search 
                OR last_name LIKE :search 
                OR company_name LIKE :search
                ORDER BY last_name, first_name
            ");
            $searchParam = "%$searchTerm%";
            $stmt->bindParam(':search', $searchParam);
        } else {
            $stmt = $db->prepare("SELECT * FROM customers ORDER BY last_name, first_name");
        }
        
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($customers);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>