<?php
// Отримання переміщень, що очікують підтвердження для конкретного складу
function getPendingTransfers($mysqli, $warehouseId) {
    $transfers = [];
    
    // Отримуємо переміщення, де цей склад є складом призначення (to_warehouse_id)
    $stmt = $mysqli->prepare("
        SELECT 
            wt.id,
            wt.from_warehouse_id,
            wt.to_warehouse_id,
            wt.created_by_user_id,
            wt.status,
            wt.created_at,
            wf.name as from_warehouse_name,
            wf.identification_number as from_warehouse_number,
            wto.name as to_warehouse_name,
            wto.identification_number as to_warehouse_number,
            u.full_name as created_by_name
        FROM warehouse_transfers wt
        LEFT JOIN warehouses wf ON wt.from_warehouse_id = wf.id
        LEFT JOIN warehouses wto ON wt.to_warehouse_id = wto.id
        LEFT JOIN access_users u ON wt.created_by_user_id = u.id
        WHERE wt.to_warehouse_id = ? AND wt.status = 'pending'
        ORDER BY wt.created_at DESC
    ");
    
    $stmt->bind_param("i", $warehouseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Отримуємо товари для кожного переміщення
        $itemsStmt = $mysqli->prepare("
            SELECT 
                wti.id,
                wti.product_id,
                wti.quantity,
                p.name as product_name,
                p.article as product_article
            FROM warehouse_transfer_items wti
            LEFT JOIN products p ON wti.product_id = p.id
            WHERE wti.transfer_id = ?
        ");
        $itemsStmt->bind_param("i", $row['id']);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        $items = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
        }
        $itemsStmt->close();
        
        $row['items'] = $items;
        $transfers[] = $row;
    }
    
    $stmt->close();
    
    return $transfers;
}
?>

