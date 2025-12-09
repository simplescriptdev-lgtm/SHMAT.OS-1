<?php
// Обробник дій з приходами товару

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/index.php';

header('Content-Type: application/json');

if ($dbError !== null) {
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних.']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'create') {
    $supplierId = isset($_POST['supplier_id']) ? (int) $_POST['supplier_id'] : 0;
    $itemsJson = isset($_POST['items']) ? $_POST['items'] : '[]';
    $items = json_decode($itemsJson, true);
    
    // Діагностика
    error_log('Received items JSON: ' . $itemsJson);
    error_log('Decoded items: ' . print_r($items, true));
    
    if (!is_array($items)) {
        error_log('Items is not an array, setting to empty array');
        $items = [];
    }
    
    error_log('Items count: ' . count($items));

    $result = createGoodsReceipt($mysqli, $supplierId, $items);
    echo json_encode($result);
} elseif ($action === 'get') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id > 0) {
        $receipt = getGoodsReceipt($mysqli, $id);
        if ($receipt) {
            echo json_encode(['success' => true, 'receipt' => $receipt]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Прихід не знайдено.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID приходу.']);
    }
} elseif ($action === 'delete') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id > 0) {
        $result = deleteGoodsReceipt($mysqli, $id);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID приходу.']);
    }
} elseif ($action === 'search_products') {
    $searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';
    $excludeIds = isset($_GET['exclude_ids']) ? json_decode($_GET['exclude_ids'], true) : [];
    if (!is_array($excludeIds)) {
        $excludeIds = [];
    }
    $products = searchProducts($mysqli, $searchTerm, $excludeIds);
    echo json_encode(['success' => true, 'products' => $products]);
} elseif ($action === 'get_all_products') {
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $excludeIds = isset($_GET['exclude_ids']) ? json_decode($_GET['exclude_ids'], true) : [];
    if (!is_array($excludeIds)) {
        $excludeIds = [];
    }
    $products = getAllProducts($mysqli, $limit, $offset, $excludeIds);
    echo json_encode(['success' => true, 'products' => $products]);
} else {
    echo json_encode(['success' => false, 'message' => 'Невідома дія.']);
}

