<?php
// Центральний обробник AJAX запитів для переміщення товарів
require_once __DIR__ . '/index.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизовано']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add_to_cart':
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (float) ($_POST['quantity'] ?? 0);
        $warehouseId = (int) ($_POST['warehouse_id'] ?? 0);
        
        $result = addToTransferCart($mysqli, $productId, $quantity, $warehouseId);
        echo json_encode($result);
        break;
        
    case 'get_cart':
        $result = getTransferCart();
        echo json_encode($result);
        break;
        
    case 'get_cart_count':
        $result = getTransferCartCount();
        echo json_encode($result);
        break;
        
    case 'update_cart_item':
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (float) ($_POST['quantity'] ?? 0);
        
        $result = updateTransferCartItem($productId, $quantity);
        echo json_encode($result);
        break;
        
    case 'remove_from_cart':
        $productId = (int) ($_POST['product_id'] ?? 0);
        
        $result = removeFromTransferCart($productId);
        echo json_encode($result);
        break;
        
    case 'create_transfer':
        $fromWarehouseId = (int) ($_POST['from_warehouse_id'] ?? 0);
        $toWarehouseId = (int) ($_POST['to_warehouse_id'] ?? 0);
        $userId = (int) $_SESSION['user_id'];
        
        $result = createTransfer($mysqli, $fromWarehouseId, $toWarehouseId, $userId);
        echo json_encode($result);
        break;
        
    case 'get_all_warehouses':
        $result = getAllWarehouses($mysqli);
        echo json_encode($result);
        break;
        
    case 'get_pending_transfers':
        $warehouseId = (int) ($_POST['warehouse_id'] ?? $_GET['warehouse_id'] ?? 0);
        if ($warehouseId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Невірний ID складу']);
            exit;
        }
        $transfers = getPendingTransfers($mysqli, $warehouseId);
        echo json_encode(['success' => true, 'transfers' => $transfers]);
        break;
        
    case 'approve_transfer':
        $transferId = (int) ($_POST['transfer_id'] ?? 0);
        $userId = (int) $_SESSION['user_id'];
        $result = approveTransfer($mysqli, $transferId, $userId);
        echo json_encode($result);
        break;
        
    case 'reject_transfer':
        $transferId = (int) ($_POST['transfer_id'] ?? 0);
        $userId = (int) $_SESSION['user_id'];
        $result = rejectTransfer($mysqli, $transferId, $userId);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Невідома дія']);
        break;
}
?>

