<?php
// Створення переміщення товарів між складами
function createTransfer($mysqli, $fromWarehouseId, $toWarehouseId, $userId) {
    // Ініціалізуємо кошик переміщення в сесії, якщо його немає
    if (!isset($_SESSION['transfer_cart'])) {
        $_SESSION['transfer_cart'] = [];
    }
    
    if ($fromWarehouseId <= 0 || $toWarehouseId <= 0 || $fromWarehouseId == $toWarehouseId) {
        return ['success' => false, 'message' => 'Невірні параметри'];
    }
    
    if (empty($_SESSION['transfer_cart'])) {
        return ['success' => false, 'message' => 'Кошик порожній'];
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
                    INSERT INTO warehouse_transfer_items (transfer_id, product_id, quantity, from_sector, from_row_number, to_sector, to_row_number)
                    VALUES (?, ?, ?, NULL, NULL, NULL, NULL)
                ");
                if ($insertItemStmt) {
                    $insertItemStmt->bind_param("iid", $transferId, $item['product_id'], $item['quantity']);
                    $insertItemStmt->execute();
                    $insertItemStmt->close();
                } else {
                    throw new Exception('Помилка підготовки запиту: ' . $mysqli->error);
                }
            }
        }
        
        // Очищаємо кошик
        $_SESSION['transfer_cart'] = [];
        
        $mysqli->commit();
        return ['success' => true, 'transfer_id' => $transferId];
    } catch (Exception $e) {
        $mysqli->rollback();
        return ['success' => false, 'message' => 'Помилка створення переміщення: ' . $e->getMessage()];
    }
}
?>

