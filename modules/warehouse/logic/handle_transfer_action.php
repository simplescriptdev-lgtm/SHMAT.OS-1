<?php
// Обробник AJAX запитів для переміщення товарів
require_once __DIR__ . '/../../../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизовано']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Ініціалізуємо кошик переміщення в сесії, якщо його немає
if (!isset($_SESSION['transfer_cart'])) {
    $_SESSION['transfer_cart'] = [];
}

switch ($action) {
    case 'add_to_cart':
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (float) ($_POST['quantity'] ?? 0);
        $warehouseId = (int) ($_POST['warehouse_id'] ?? 0);
        
        if ($productId <= 0 || $quantity <= 0 || $warehouseId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Невірні параметри']);
            exit;
        }
        
        // Перевіряємо, чи товар вже є в кошику
        $found = false;
        foreach ($_SESSION['transfer_cart'] as $key => $item) {
            if ($item['product_id'] == $productId && $item['warehouse_id'] == $warehouseId) {
                // Оновлюємо кількість
                $_SESSION['transfer_cart'][$key]['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            // Отримуємо інформацію про товар
            $productStmt = $mysqli->prepare("SELECT name, article, brand FROM products WHERE id = ?");
            $productStmt->bind_param("i", $productId);
            $productStmt->execute();
            $productResult = $productStmt->get_result();
            $product = $productResult->fetch_assoc();
            $productStmt->close();
            
            if ($product) {
                $_SESSION['transfer_cart'][] = [
                    'product_id' => $productId,
                    'product_name' => $product['name'],
                    'product_article' => $product['article'],
                    'quantity' => $quantity,
                    'warehouse_id' => $warehouseId
                ];
            }
        }
        
        echo json_encode(['success' => true]);
        break;
        
    case 'get_cart':
        $items = $_SESSION['transfer_cart'] ?? [];
        echo json_encode(['success' => true, 'items' => $items]);
        break;
        
    case 'get_cart_count':
        $count = count($_SESSION['transfer_cart'] ?? []);
        echo json_encode(['success' => true, 'count' => $count]);
        break;
        
    case 'update_cart_item':
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (float) ($_POST['quantity'] ?? 0);
        
        if ($productId <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Невірні параметри']);
            exit;
        }
        
        foreach ($_SESSION['transfer_cart'] as $key => $item) {
            if ($item['product_id'] == $productId) {
                $_SESSION['transfer_cart'][$key]['quantity'] = $quantity;
                break;
            }
        }
        
        echo json_encode(['success' => true]);
        break;
        
    case 'remove_from_cart':
        $productId = (int) ($_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Невірні параметри']);
            exit;
        }
        
        foreach ($_SESSION['transfer_cart'] as $key => $item) {
            if ($item['product_id'] == $productId) {
                unset($_SESSION['transfer_cart'][$key]);
                $_SESSION['transfer_cart'] = array_values($_SESSION['transfer_cart']); // Переіндексуємо масив
                break;
            }
        }
        
        echo json_encode(['success' => true]);
        break;
        
    case 'create_transfer':
        $fromWarehouseId = (int) ($_POST['from_warehouse_id'] ?? 0);
        $toWarehouseId = (int) ($_POST['to_warehouse_id'] ?? 0);
        $userId = (int) $_SESSION['user_id'];
        
        if ($fromWarehouseId <= 0 || $toWarehouseId <= 0 || $fromWarehouseId == $toWarehouseId) {
            echo json_encode(['success' => false, 'message' => 'Невірні параметри']);
            exit;
        }
        
        if (empty($_SESSION['transfer_cart'])) {
            echo json_encode(['success' => false, 'message' => 'Кошик порожній']);
            exit;
        }
        
        // Створюємо переміщення в БД
        $mysqli->begin_transaction();
        
        try {
            // Створюємо запис переміщення
            $insertTransferStmt = $mysqli->prepare("
                INSERT INTO warehouse_transfers (from_warehouse_id, to_warehouse_id, created_by_user_id, status)
                VALUES (?, ?, ?, 'pending')
            ");
            $insertTransferStmt->bind_param("iii", $fromWarehouseId, $toWarehouseId, $userId);
            $insertTransferStmt->execute();
            $transferId = $mysqli->insert_id;
            $insertTransferStmt->close();
            
            // Додаємо товари до переміщення
            foreach ($_SESSION['transfer_cart'] as $item) {
                if ($item['warehouse_id'] == $fromWarehouseId) {
                    $insertItemStmt = $mysqli->prepare("
                        INSERT INTO warehouse_transfer_items (transfer_id, product_id, quantity, sector, `row_number`)
                        VALUES (?, ?, ?, NULL, NULL)
                    ");
                    $insertItemStmt->bind_param("iid", $transferId, $item['product_id'], $item['quantity']);
                    $insertItemStmt->execute();
                    $insertItemStmt->close();
                }
            }
            
            // Очищаємо кошик
            $_SESSION['transfer_cart'] = [];
            
            $mysqli->commit();
            echo json_encode(['success' => true, 'transfer_id' => $transferId]);
        } catch (Exception $e) {
            $mysqli->rollback();
            echo json_encode(['success' => false, 'message' => 'Помилка створення переміщення: ' . $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Невідома дія']);
        break;
}
?>
