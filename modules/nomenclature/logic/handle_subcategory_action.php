<?php
// Обробник дій з підкатегоріями (створення, редагування, видалення)

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/index.php';

header('Content-Type: application/json');

if ($dbError !== null) {
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних.']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'create') {
    $categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $result = createSubcategory($mysqli, $categoryId, $name);
    echo json_encode($result);
} elseif ($action === 'update') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    if ($id > 0) {
        $result = updateSubcategory($mysqli, $id, $name);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID підкатегорії.']);
    }
} elseif ($action === 'delete') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id > 0) {
        $result = deleteSubcategory($mysqli, $id);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID підкатегорії.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Невідома дія.']);
}



