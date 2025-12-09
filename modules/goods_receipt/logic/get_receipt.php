<?php
// Функція для отримання приходу товару за ID з позиціями

function getGoodsReceipt($mysqli, $receiptId) {
    $stmt = $mysqli->prepare('
        SELECT 
            gr.id,
            gr.receipt_number,
            gr.supplier_id,
            gr.total_amount,
            gr.items_count,
            gr.created_at,
            s.name as supplier_name
        FROM goods_receipts gr
        INNER JOIN suppliers s ON gr.supplier_id = s.id
        WHERE gr.id = ?
    ');
    
    if (!$stmt) {
        error_log('Помилка підготовки запиту для отримання приходу: ' . $mysqli->error);
        return null;
    }
    
    $stmt->bind_param('i', $receiptId);
    if (!$stmt->execute()) {
        error_log('Помилка виконання запиту для отримання приходу: ' . $mysqli->error);
        $stmt->close();
        return null;
    }
    
    $result = $stmt->get_result();
    $receipt = $result->fetch_assoc();
    $stmt->close();
    
    if (!$receipt) {
        return null;
    }
    
    // Отримуємо позиції приходу
    $itemsStmt = $mysqli->prepare('
        SELECT 
            gri.id,
            gri.product_id,
            gri.quantity,
            gri.unit_price,
            gri.total_price,
            p.name as product_name,
            p.article as product_article,
            p.brand as product_brand
        FROM goods_receipt_items gri
        INNER JOIN products p ON gri.product_id = p.id
        WHERE gri.receipt_id = ?
        ORDER BY gri.id
    ');
    
    if (!$itemsStmt) {
        error_log('Помилка підготовки запиту для отримання позицій приходу: ' . $mysqli->error);
        $receipt['items'] = [];
        return $receipt;
    }
    
    $itemsStmt->bind_param('i', $receiptId);
    if (!$itemsStmt->execute()) {
        error_log('Помилка виконання запиту для отримання позицій приходу: ' . $mysqli->error);
        $itemsStmt->close();
        $receipt['items'] = [];
        return $receipt;
    }
    
    $itemsResult = $itemsStmt->get_result();
    
    $items = [];
    while ($itemRow = $itemsResult->fetch_assoc()) {
        $items[] = $itemRow;
    }
    $itemsStmt->close();
    
    error_log('Отримано товарів для приходу ' . $receiptId . ': ' . count($items));
    if (count($items) > 0) {
        error_log('Перший товар: ' . print_r($items[0], true));
    }
    
    $receipt['items'] = $items;
    
    return $receipt;
}

