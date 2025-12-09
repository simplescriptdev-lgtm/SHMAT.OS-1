<?php
// Отримання інформації про "Прихідний склад" (001)

require_once __DIR__ . '/../../../config/bootstrap.php';

header('Content-Type: application/json');

if ($dbError !== null) {
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних.']);
    exit;
}

$stmt = $mysqli->prepare('SELECT id, name, identification_number, has_scheme FROM warehouses WHERE identification_number = ? LIMIT 1');
if ($stmt) {
    $warehouseNumber = '001';
    $stmt->bind_param('s', $warehouseNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $warehouse = $result->fetch_assoc();
    $stmt->close();
    
    if ($warehouse) {
        echo json_encode(['success' => true, 'warehouse' => $warehouse]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Прихідний склад не знайдено.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Помилка підготовки запиту.']);
}



