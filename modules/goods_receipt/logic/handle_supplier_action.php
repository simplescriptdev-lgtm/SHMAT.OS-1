<?php
// Обробник дій з постачальниками (створення, редагування, видалення, отримання)

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
    $information = isset($_POST['information']) ? trim($_POST['information']) : null;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

    $result = createSupplier($mysqli, $name, $information, $notes);
    echo json_encode($result);
} elseif ($action === 'get') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id > 0) {
        $supplier = getSupplier($mysqli, $id);
        if ($supplier) {
            echo json_encode(['success' => true, 'supplier' => $supplier]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Постачальника не знайдено.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID постачальника.']);
    }
} elseif ($action === 'update') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $information = isset($_POST['information']) ? trim($_POST['information']) : null;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

    if ($id > 0) {
        $result = updateSupplier($mysqli, $id, $name, $information, $notes);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID постачальника.']);
    }
} elseif ($action === 'delete') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id > 0) {
        $result = deleteSupplier($mysqli, $id);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID постачальника.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Невідома дія.']);
}



