<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$dbPath = __DIR__ . '/../../database/customer.db';

try {
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $customerId = $_GET['customer_id'] ?? null;

    if (!$customerId) {
        http_response_code(400);
        echo json_encode(['error' => 'customer_id parameter is required']);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM customer_activity WHERE customer_id = :customer_id ORDER BY created_at DESC");
    $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($activities);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>