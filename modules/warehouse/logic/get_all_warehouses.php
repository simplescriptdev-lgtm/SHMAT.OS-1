<?php
// Отримання списку всіх складів
require_once __DIR__ . '/../../../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$warehouses = [];

$stmt = $mysqli->prepare("SELECT id, name, identification_number FROM warehouses ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $warehouses[] = $row;
}

$stmt->close();

echo json_encode(['success' => true, 'warehouses' => $warehouses]);
?>
