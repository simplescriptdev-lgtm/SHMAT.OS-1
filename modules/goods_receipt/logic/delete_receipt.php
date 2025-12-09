<?php
// Функція для видалення приходу товару (з поверненням товару на склад)

function deleteGoodsReceipt($mysqli, $receiptId) {
    // Отримуємо інформацію про прихід перед видаленням
    $receipt = getGoodsReceipt($mysqli, $receiptId);
    if (!$receipt) {
        return ['success' => false, 'message' => 'Прихід не знайдено.'];
    }

    // Знаходимо "Прихідний склад"
    $warehouseStmt = $mysqli->prepare('SELECT id FROM warehouses WHERE identification_number = ? LIMIT 1');
    $warehouseId = null;
    if ($warehouseStmt) {
        $warehouseNumber = '001';
        $warehouseStmt->bind_param('s', $warehouseNumber);
        $warehouseStmt->execute();
        $warehouseResult = $warehouseStmt->get_result();
        if ($warehouseResult->num_rows > 0) {
            $warehouseRow = $warehouseResult->fetch_assoc();
            $warehouseId = $warehouseRow['id'];
        }
        $warehouseStmt->close();
    }

    $mysqli->begin_transaction();

    try {
        // Повертаємо товар зі складу (зменшуємо кількість)
        if ($warehouseId && !empty($receipt['items'])) {
            foreach ($receipt['items'] as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                // Зменшуємо кількість на складі
                $stockStmt = $mysqli->prepare('SELECT id, quantity FROM warehouse_stock WHERE warehouse_id = ? AND product_id = ? LIMIT 1');
                if ($stockStmt) {
                    $stockStmt->bind_param('ii', $warehouseId, $productId);
                    $stockStmt->execute();
                    $stockResult = $stockStmt->get_result();
                    
                    if ($stockResult->num_rows > 0) {
                        $stockRow = $stockResult->fetch_assoc();
                        $newQuantity = max(0, $stockRow['quantity'] - $quantity);
                        
                        if ($newQuantity > 0) {
                            $stockUpdateStmt = $mysqli->prepare('UPDATE warehouse_stock SET quantity = ? WHERE id = ?');
                            $stockUpdateStmt->bind_param('di', $newQuantity, $stockRow['id']);
                            $stockUpdateStmt->execute();
                            $stockUpdateStmt->close();
                        } else {
                            // Видаляємо запис, якщо кількість стала 0
                            $stockDeleteStmt = $mysqli->prepare('DELETE FROM warehouse_stock WHERE id = ?');
                            $stockDeleteStmt->bind_param('i', $stockRow['id']);
                            $stockDeleteStmt->execute();
                            $stockDeleteStmt->close();
                        }
                    }
                    $stockStmt->close();
                }
            }
        }

        // Видаляємо прихід (каскадно видаляться позиції)
        $deleteStmt = $mysqli->prepare('DELETE FROM goods_receipts WHERE id = ?');
        if (!$deleteStmt) {
            throw new Exception('Помилка підготовки запиту: ' . $mysqli->error);
        }

        $deleteStmt->bind_param('i', $receiptId);
        if (!$deleteStmt->execute()) {
            throw new Exception('Помилка при видаленні приходу: ' . $mysqli->error);
        }
        $deleteStmt->close();

        $mysqli->commit();
        return ['success' => true, 'message' => 'Прихід товару успішно видалено.'];
    } catch (Exception $e) {
        $mysqli->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}



