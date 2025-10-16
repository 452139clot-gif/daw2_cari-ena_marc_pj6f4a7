<?php
// orders.php
// Devuelve todas las órdenes en formato JSON

header('Content-Type: application/json; charset=utf-8');

$dbPath = __DIR__ . '../onlineOrders.db'; // Ajusta según tu ubicación

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($orders, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar con la base de datos: ' . $e->getMessage()]);
}
