<?php
// Центральний обробник AJAX запитів для блоків дозволів та доступів
require_once __DIR__ . '/index.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизовано']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    // Блоки дозволів
    case 'create_permission_block':
        $name = trim($_POST['name'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Назва блоку обов\'язкова']);
            exit;
        }
        $result = createPermissionBlock($mysqli, $name, $notes ?: null);
        echo json_encode($result);
        break;
        
    case 'update_permission_block':
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        if ($id <= 0 || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Невірні дані']);
            exit;
        }
        $result = updatePermissionBlock($mysqli, $id, $name, $notes ?: null);
        echo json_encode($result);
        break;
        
    case 'delete_permission_block':
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Невірний ID']);
            exit;
        }
        $result = deletePermissionBlock($mysqli, $id);
        echo json_encode($result);
        break;
        
    case 'get_permission_block':
        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Невірний ID']);
            exit;
        }
        $block = getPermissionBlock($mysqli, $id);
        if ($block) {
            echo json_encode(['success' => true, 'block' => $block]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Блок не знайдено']);
        }
        break;
        
    // Доступи
    case 'create_permission':
        $blockId = (int) ($_POST['block_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        if ($blockId <= 0 || empty($name) || empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Заповніть всі обов\'язкові поля']);
            exit;
        }
        $result = createPermission($mysqli, $blockId, $name, $code, $notes ?: null);
        echo json_encode($result);
        break;
        
    case 'update_permission':
        $id = (int) ($_POST['id'] ?? 0);
        $blockId = (int) ($_POST['block_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        if ($id <= 0 || $blockId <= 0 || empty($name) || empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Невірні дані']);
            exit;
        }
        $result = updatePermission($mysqli, $id, $blockId, $name, $code, $notes ?: null);
        echo json_encode($result);
        break;
        
    case 'delete_permission':
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Невірний ID']);
            exit;
        }
        $result = deletePermission($mysqli, $id);
        echo json_encode($result);
        break;
        
    case 'get_permission':
        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Невірний ID']);
            exit;
        }
        $permission = getPermission($mysqli, $id);
        if ($permission) {
            echo json_encode(['success' => true, 'permission' => $permission]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Доступ не знайдено']);
        }
        break;
        
    case 'save_user_permissions':
        $userId = (int) ($_POST['user_id'] ?? 0);
        $permissionIdsJson = $_POST['permission_ids'] ?? '[]';
        $permissionIds = json_decode($permissionIdsJson, true);
        
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Невірний ID користувача']);
            exit;
        }
        
        if (!is_array($permissionIds)) {
            $permissionIds = [];
        }
        
        $result = saveUserPermissions($mysqli, $userId, $permissionIds);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Невідома дія']);
        break;
}
?>

