<?php
// Обробник дій зі складами (створення, редагування, видалення, отримання)

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/index.php';

header('Content-Type: application/json');

if ($dbError !== null) {
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних.']);
    exit;
}

// Перевіряємо action з POST або GET
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'create') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $identificationNumber = isset($_POST['identification_number']) ? trim($_POST['identification_number']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $hasScheme = isset($_POST['has_scheme']) && $_POST['has_scheme'] === '1';
    $userIds = isset($_POST['user_ids']) && is_array($_POST['user_ids']) ? $_POST['user_ids'] : [];

    $result = createWarehouse($mysqli, $name, $identificationNumber, $description, $hasScheme, $userIds);
    echo json_encode($result);
} elseif ($action === 'get') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id > 0) {
        $warehouse = getWarehouse($mysqli, $id);
        if ($warehouse) {
            echo json_encode(['success' => true, 'warehouse' => $warehouse]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Склад не знайдено.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID складу.']);
    }
} elseif ($action === 'get_users') {
    $excludeUserIds = isset($_GET['exclude_user_ids']) ? json_decode($_GET['exclude_user_ids'], true) : [];
    if (!is_array($excludeUserIds)) {
        $excludeUserIds = [];
    }
    $users = getWarehouseUsers($mysqli, $excludeUserIds);
    echo json_encode(['success' => true, 'users' => $users]);
} elseif ($action === 'update') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $identificationNumber = isset($_POST['identification_number']) ? trim($_POST['identification_number']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $hasScheme = isset($_POST['has_scheme']) && $_POST['has_scheme'] === '1';
    $userIds = isset($_POST['user_ids']) && is_array($_POST['user_ids']) ? $_POST['user_ids'] : [];

    if ($id > 0) {
        $result = updateWarehouse($mysqli, $id, $name, $identificationNumber, $description, $hasScheme, $userIds);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID складу.']);
    }
} elseif ($action === 'delete') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id > 0) {
        $result = deleteWarehouse($mysqli, $id);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID складу.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Невідома дія.']);
}



