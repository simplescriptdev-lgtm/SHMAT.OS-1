<?php
// Функція для створення приходу товару

function createGoodsReceipt($mysqli, $supplierId, $items) {
    if (empty($supplierId) || $supplierId <= 0) {
        return ['success' => false, 'message' => 'Необхідно вибрати постачальника.'];
    }

    if (empty($items) || !is_array($items) || count($items) === 0) {
        return ['success' => false, 'message' => 'Необхідно додати хоча б один товар до приходу.'];
    }

    // Генеруємо номер накладної
    $receiptNumber = 'REC-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Перевіряємо унікальність номера
    $checkStmt = $mysqli->prepare('SELECT id FROM goods_receipts WHERE receipt_number = ? LIMIT 1');
    if ($checkStmt) {
        $checkStmt->bind_param('s', $receiptNumber);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows > 0) {
            // Якщо номер вже існує, генеруємо новий
            $receiptNumber = 'REC-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }
        $checkStmt->close();
    }

    // Знаходимо "Прихідний склад" за ідентифікаційним номером "001"
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

    if (!$warehouseId) {
        return ['success' => false, 'message' => 'Не знайдено "Прихідний склад" (001). Створіть склад з ідентифікаційним номером 001.'];
    }

    // Перевіряємо, чи склад має схему
    $warehouseSchemeStmt = $mysqli->prepare('SELECT has_scheme FROM warehouses WHERE id = ? LIMIT 1');
    $hasScheme = false;
    if ($warehouseSchemeStmt) {
        $warehouseSchemeStmt->bind_param('i', $warehouseId);
        $warehouseSchemeStmt->execute();
        $schemeResult = $warehouseSchemeStmt->get_result();
        if ($schemeResult->num_rows > 0) {
            $schemeRow = $schemeResult->fetch_assoc();
            $hasScheme = (bool) $schemeRow['has_scheme'];
        }
        $warehouseSchemeStmt->close();
    }

    $mysqli->begin_transaction();

    try {
        // Обчислюємо загальну суму та кількість позицій
        $totalAmount = 0;
        $itemsCount = count($items);

        foreach ($items as $item) {
            $quantity = isset($item['quantity']) ? (float) $item['quantity'] : 0;
            $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : 0;
            $totalAmount += $quantity * $unitPrice;
        }

        // Створюємо прихід
        $stmt = $mysqli->prepare('INSERT INTO goods_receipts (supplier_id, receipt_number, total_amount, items_count) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            throw new Exception('Помилка підготовки запиту: ' . $mysqli->error);
        }

        $stmt->bind_param('isdi', $supplierId, $receiptNumber, $totalAmount, $itemsCount);
        if (!$stmt->execute()) {
            throw new Exception('Помилка при створенні приходу: ' . $mysqli->error);
        }

        $receiptId = $mysqli->insert_id;
        $stmt->close();

        // Додаємо позиції приходу та оновлюємо залишки на складі
        $itemStmt = $mysqli->prepare('INSERT INTO goods_receipt_items (receipt_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)');
        if (!$itemStmt) {
            throw new Exception('Помилка підготовки запиту для позицій: ' . $mysqli->error);
        }

        foreach ($items as $item) {
            // Підтримуємо обидва варіанти: product_id та id (для сумісності)
            $productId = isset($item['product_id']) ? (int) $item['product_id'] : (isset($item['id']) ? (int) $item['id'] : 0);
            $quantity = isset($item['quantity']) ? (float) $item['quantity'] : 0;
            $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : 0;
            $totalPrice = $quantity * $unitPrice;
            $sector = isset($item['sector']) ? trim($item['sector']) : null;
            $row = isset($item['row']) ? trim($item['row']) : null;

            if ($productId <= 0 || $quantity <= 0) {
                error_log('Пропущено товар: product_id=' . $productId . ', quantity=' . $quantity);
                continue;
            }
            
            error_log('Додаємо товар до приходу: product_id=' . $productId . ', quantity=' . $quantity . ', unit_price=' . $unitPrice);

            // Додаємо позицію приходу
            $itemStmt->bind_param('iiddd', $receiptId, $productId, $quantity, $unitPrice, $totalPrice);
            if (!$itemStmt->execute()) {
                throw new Exception('Помилка при додаванні позиції: ' . $mysqli->error);
            }

            // Оновлюємо залишки на складі
            if ($hasScheme && !empty($sector) && !empty($row)) {
                // Якщо склад має схему і вказані сектор та ряд, перевіряємо чи існує запис з такими сектором та рядом
                $stockCheckStmt = $mysqli->prepare('SELECT id, quantity FROM warehouse_stock WHERE warehouse_id = ? AND product_id = ? AND sector = ? AND `row_number` = ? LIMIT 1');
                if ($stockCheckStmt) {
                    $stockCheckStmt->bind_param('iiss', $warehouseId, $productId, $sector, $row);
                    $stockCheckStmt->execute();
                    $stockResult = $stockCheckStmt->get_result();
                    
                    if ($stockResult->num_rows > 0) {
                        // Оновлюємо існуючий запис
                        $stockRow = $stockResult->fetch_assoc();
                        $newQuantity = $stockRow['quantity'] + $quantity;
                        $stockUpdateStmt = $mysqli->prepare('UPDATE warehouse_stock SET quantity = ? WHERE id = ?');
                        $stockUpdateStmt->bind_param('di', $newQuantity, $stockRow['id']);
                        $stockUpdateStmt->execute();
                        $stockUpdateStmt->close();
                    } else {
                        // Створюємо новий запис
                        $stockInsertStmt = $mysqli->prepare('INSERT INTO warehouse_stock (warehouse_id, product_id, quantity, sector, `row_number`) VALUES (?, ?, ?, ?, ?)');
                        if (!$stockInsertStmt) {
                            throw new Exception('Помилка підготовки запиту для додавання товару на склад: ' . $mysqli->error);
                        }
                        $stockInsertStmt->bind_param('iidss', $warehouseId, $productId, $quantity, $sector, $row);
                        if (!$stockInsertStmt->execute()) {
                            throw new Exception('Помилка при додаванні товару на склад: ' . $mysqli->error);
                        }
                        $stockInsertStmt->close();
                    }
                    $stockCheckStmt->close();
                }
            } else {
                // Якщо склад не має схеми або сектор/ряд не вказані, оновлюємо загальну кількість (без сектора та ряду)
                // Спочатку шукаємо запис без сектора та ряду
                $stockCheckStmt = $mysqli->prepare('SELECT id, quantity FROM warehouse_stock WHERE warehouse_id = ? AND product_id = ? AND (sector IS NULL OR sector = "") AND (`row_number` IS NULL OR `row_number` = "") LIMIT 1');
                if ($stockCheckStmt) {
                    $stockCheckStmt->bind_param('ii', $warehouseId, $productId);
                    $stockCheckStmt->execute();
                    $stockResult = $stockCheckStmt->get_result();
                    
                    if ($stockResult->num_rows > 0) {
                        // Оновлюємо існуючий запис
                        $stockRow = $stockResult->fetch_assoc();
                        $newQuantity = $stockRow['quantity'] + $quantity;
                        $stockUpdateStmt = $mysqli->prepare('UPDATE warehouse_stock SET quantity = ? WHERE id = ?');
                        $stockUpdateStmt->bind_param('di', $newQuantity, $stockRow['id']);
                        $stockUpdateStmt->execute();
                        $stockUpdateStmt->close();
                    } else {
                        // Створюємо новий запис без сектора та ряду
                        $stockInsertStmt = $mysqli->prepare('INSERT INTO warehouse_stock (warehouse_id, product_id, quantity, sector, `row_number`) VALUES (?, ?, ?, NULL, NULL)');
                        $stockInsertStmt->bind_param('iid', $warehouseId, $productId, $quantity);
                        if (!$stockInsertStmt->execute()) {
                            throw new Exception('Помилка при додаванні товару на склад: ' . $mysqli->error);
                        }
                        $stockInsertStmt->close();
                    }
                    $stockCheckStmt->close();
                } else {
                    throw new Exception('Помилка підготовки запиту для складу: ' . $mysqli->error);
                }
            }
        }

        $itemStmt->close();
        $mysqli->commit();
        return ['success' => true, 'message' => 'Прихід товару успішно створено.', 'id' => $receiptId, 'receipt_number' => $receiptNumber];
    } catch (Exception $e) {
        $mysqli->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

