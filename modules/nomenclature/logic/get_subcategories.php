<?php
// Функція для отримання підкатегорій за ID категорії

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/index.php';

header('Content-Type: application/json');

if ($dbError !== null) {
    echo json_encode([]);
    exit;
}

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

if ($categoryId > 0) {
    $subcategories = getSubcategories($mysqli, $categoryId);
    echo json_encode($subcategories);
} else {
    echo json_encode([]);
}
