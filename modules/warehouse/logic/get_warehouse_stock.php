<?php
// Функція для отримання товарів на складі

function getWarehouseStock($mysqli, $warehouseId) {
    $stmt = $mysqli->prepare('
        SELECT 
            ws.id,
            ws.quantity,
            ws.sector,
            ws.`row_number`,
            p.id as product_id,
            p.name as product_name,
            p.article as product_article,
            p.brand as product_brand,
            pc.name as category_name,
            psc.name as subcategory_name
        FROM warehouse_stock ws
        INNER JOIN products p ON ws.product_id = p.id
        LEFT JOIN product_categories pc ON p.category_id = pc.id
        LEFT JOIN product_subcategories psc ON p.subcategory_id = psc.id
        WHERE ws.warehouse_id = ?
        ORDER BY p.name
    ');
    
    $stock = [];
    if ($stmt) {
        $stmt->bind_param('i', $warehouseId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $stock[] = $row;
        }
        $stmt->close();
    }
    return $stock;
}

