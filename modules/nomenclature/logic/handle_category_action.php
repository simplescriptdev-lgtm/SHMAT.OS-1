<?php
// Обробник дій з категоріями (створення, редагування, видалення)

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/index.php';

header('Content-Type: application/json');

if ($dbError !== null) {
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних.']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'create') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $result = createCategory($mysqli, $name);
    echo json_encode($result);
} elseif ($action === 'update') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    if ($id > 0) {
        $result = updateCategory($mysqli, $id, $name);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID категорії.']);
    }
} elseif ($action === 'delete') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id > 0) {
        $result = deleteCategory($mysqli, $id);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID категорії.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Невідома дія.']);
}



