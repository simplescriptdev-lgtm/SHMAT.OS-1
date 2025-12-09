<?php
// Endpoint для отримання товару за ID (для AJAX)

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/index.php';

header('Content-Type: application/json');

if ($dbError !== null) {
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних.']);
    exit;
}

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($productId > 0) {
    $product = getProduct($mysqli, $productId);
    if ($product) {
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Товар не знайдено.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Невірний ID товару.']);
}

