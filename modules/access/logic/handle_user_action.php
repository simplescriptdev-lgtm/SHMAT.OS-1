<?php
// Обробник дій з користувачами (створення, редагування, видалення, отримання)

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
    $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

    $result = createAccessUser($mysqli, $fullName, $login, $password, $notes);
    echo json_encode($result);
} elseif ($action === 'get') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id > 0) {
        $user = getAccessUser($mysqli, $id);
        if ($user) {
            // Не повертаємо пароль у відповіді
            unset($user['password_hash']);
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Користувача не знайдено.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID користувача.']);
    }
} elseif ($action === 'update') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $password = isset($_POST['password']) && !empty(trim($_POST['password'])) ? trim($_POST['password']) : null;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

    if ($id > 0) {
        $result = updateAccessUser($mysqli, $id, $fullName, $login, $password, $notes);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID користувача.']);
    }
} elseif ($action === 'delete') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id > 0) {
        $result = deleteAccessUser($mysqli, $id);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID користувача.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Невідома дія.']);
}



