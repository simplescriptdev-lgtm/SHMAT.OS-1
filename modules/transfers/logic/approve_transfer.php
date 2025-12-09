<?php
// Підтвердження переміщення товарів між складами
function approveTransfer($mysqli, $transferId, $userId) {
    if ($transferId <= 0) {
        return ['success' => false, 'message' => 'Невірний ID переміщення'];
    }
    
    // Починаємо транзакцію
    $mysqli->begin_transaction();
    
    try {
        // Отримуємо інформацію про переміщення
        $transferStmt = $mysqli->prepare("
            SELECT from_warehouse_id, to_warehouse_id, status
            FROM warehouse_transfers
            WHERE id = ? AND status = 'pending'
        ");
        $transferStmt->bind_param("i", $transferId);
        $transferStmt->execute();
        $transferResult = $transferStmt->get_result();
        $transfer = $transferResult->fetch_assoc();
        $transferStmt->close();
        
        if (!$transfer) {
            throw new Exception('Переміщення не знайдено або вже оброблено');
        }
        
        $fromWarehouseId = $transfer['from_warehouse_id'];
        $toWarehouseId = $transfer['to_warehouse_id'];
        
        // Отримуємо товари переміщення
        $itemsStmt = $mysqli->prepare("
            SELECT product_id, quantity, from_sector, from_row_number, to_sector, to_row_number
            FROM warehouse_transfer_items
            WHERE transfer_id = ?
        ");
        $itemsStmt->bind_param("i", $transferId);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        $items = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
        }
        $itemsStmt->close();
        
        if (empty($items)) {
            throw new Exception('Немає товарів для переміщення');
        }
        
        // Переміщуємо товари між складами
        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $fromSector = $item['from_sector'];
            $fromRow = $item['from_row_number'];
            $toSector = $item['to_sector'];
            $toRow = $item['to_row_number'];
            
            // Спочатку знаходимо запис на складі відправлення
            // Якщо є сектор і ряд, шукаємо за ними, інакше шукаємо без них
            if (!empty($fromSector) && !empty($fromRow)) {
                $findFromStmt = $mysqli->prepare("
                    SELECT id, quantity FROM warehouse_stock
                    WHERE warehouse_id = ? AND product_id = ? AND sector = ? AND `row_number` = ?
                    LIMIT 1
                ");
                $findFromStmt->bind_param("iiss", $fromWarehouseId, $productId, $fromSector, $fromRow);
            } else {
                $findFromStmt = $mysqli->prepare("
                    SELECT id, quantity FROM warehouse_stock
                    WHERE warehouse_id = ? AND product_id = ? 
                    AND (sector IS NULL OR sector = '') 
                    AND (`row_number` IS NULL OR `row_number` = '')
                    LIMIT 1
                ");
                $findFromStmt->bind_param("ii", $fromWarehouseId, $productId);
            }
            
            $findFromStmt->execute();
            $fromStockResult = $findFromStmt->get_result();
            $fromStock = $fromStockResult->fetch_assoc();
            $findFromStmt->close();
            
            if (!$fromStock) {
                throw new Exception('Товар не знайдено на складі відправлення (ID товару: ' . $productId . ')');
            }
            
            if ($fromStock['quantity'] < $quantity) {
                throw new Exception('Недостатня кількість товару на складі відправлення. Доступно: ' . $fromStock['quantity'] . ', потрібно: ' . $quantity);
            }
            
            // Зменшуємо кількість на складі відправлення
            $newFromQuantity = $fromStock['quantity'] - $quantity;
            if ($newFromQuantity > 0) {
                $updateFromStmt = $mysqli->prepare("
                    UPDATE warehouse_stock
                    SET quantity = ?
                    WHERE id = ?
                ");
                $updateFromStmt->bind_param("di", $newFromQuantity, $fromStock['id']);
                $updateFromStmt->execute();
                $updateFromStmt->close();
            } else {
                // Видаляємо запис, якщо кількість стала 0
                $deleteFromStmt = $mysqli->prepare("DELETE FROM warehouse_stock WHERE id = ?");
                $deleteFromStmt->bind_param("i", $fromStock['id']);
                $deleteFromStmt->execute();
                $deleteFromStmt->close();
            }
            
            // Додаємо товар на склад призначення
            // Спочатку перевіряємо, чи вже є такий товар на складі
            if (!empty($toSector) && !empty($toRow)) {
                $checkToStmt = $mysqli->prepare("
                    SELECT id, quantity FROM warehouse_stock
                    WHERE warehouse_id = ? AND product_id = ? AND sector = ? AND `row_number` = ?
                    LIMIT 1
                ");
                $checkToStmt->bind_param("iiss", $toWarehouseId, $productId, $toSector, $toRow);
            } else {
                $checkToStmt = $mysqli->prepare("
                    SELECT id, quantity FROM warehouse_stock
                    WHERE warehouse_id = ? AND product_id = ? 
                    AND (sector IS NULL OR sector = '') 
                    AND (`row_number` IS NULL OR `row_number` = '')
                    LIMIT 1
                ");
                $checkToStmt->bind_param("ii", $toWarehouseId, $productId);
            }
            
            $checkToStmt->execute();
            $checkToResult = $checkToStmt->get_result();
            $existingStock = $checkToResult->fetch_assoc();
            $checkToStmt->close();
            
            if ($existingStock) {
                // Оновлюємо існуючий запис
                $newToQuantity = $existingStock['quantity'] + $quantity;
                $updateStmt = $mysqli->prepare("
                    UPDATE warehouse_stock
                    SET quantity = ?
                    WHERE id = ?
                ");
                $updateStmt->bind_param("di", $newToQuantity, $existingStock['id']);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                // Створюємо новий запис
                $insertStmt = $mysqli->prepare("
                    INSERT INTO warehouse_stock (warehouse_id, product_id, quantity, sector, `row_number`)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insertStmt->bind_param("iidss", $toWarehouseId, $productId, $quantity, $toSector, $toRow);
                $insertStmt->execute();
                $insertStmt->close();
            }
        }
        
        // Оновлюємо статус переміщення
        $updateTransferStmt = $mysqli->prepare("
            UPDATE warehouse_transfers
            SET status = 'approved', approved_by_user_id = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $updateTransferStmt->bind_param("ii", $userId, $transferId);
        $updateTransferStmt->execute();
        $updateTransferStmt->close();
        
        $mysqli->commit();
        return ['success' => true, 'message' => 'Переміщення успішно підтверджено'];
    } catch (Exception $e) {
        $mysqli->rollback();
        return ['success' => false, 'message' => 'Помилка підтвердження переміщення: ' . $e->getMessage()];
    }
}
?>
